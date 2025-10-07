<?php
require_once 'Base_Model.php';

class Posts extends Model {
    public $postId;
    public $postTimeStamp;
    public $postText;
    public $userId;
    public $username;

    protected $map = [
        'post_id' => 'postId',
        'post_timestamp' => 'postTimeStamp',
        'post_text' => 'postText',
        'user_id' => 'userId',
        'username' => 'username'
    ];

    public function createPost() {
        $sql = "INSERT INTO posts (post_text, user_id) VALUES (:post_text, :user_id)";
        $params = [
            'post_text' => $this->postText,
            'user_id' => $this->userId
        ];
        $statement = $this->run($sql, $params);

        if ($statement->rowCount() > 0) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function getAllPosts(int $currentUserId, int $limit = 5, int $offset = 0): array {
        $sql = "SELECT
            p.post_id AS postId,
            p.post_text AS postText,
            p.post_timestamp AS createdAt,
            u.username,
            u.first_name AS firstName,
            u.last_name AS lastName,
            u.profile_image_url AS profileImageUrl,
            (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
            (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id AND user_id = :current_user_id) AS userHasLiked
        FROM
            posts p
        JOIN
            users u ON p.user_id = u.user_id
        ORDER BY
            p.post_timestamp DESC
        LIMIT :limit OFFSET :offset";

        $params = [
            'current_user_id' => $currentUserId,
            'limit' => $limit,
            'offset' => $offset
        ];

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':current_user_id', $currentUserId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($posts)) {
            return [];
        }

        return $this->attachCommentsAndSanitize($posts);
    }

    public function getPostsByUserId(int $profileUserId, int $currentUserId, int $sinceId = 0): array {
        $sql = "SELECT
                p.post_id AS postId,
                p.post_text AS postText,
                p.post_timestamp AS createdAt,
                u.username,
                u.first_name AS firstName,
                u.last_name AS lastName,
                u.profile_image_url AS profileImageUrl,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id AND user_id = :current_user_id) AS userHasLiked
            FROM
                posts p
            JOIN
                users u ON p.user_id = u.user_id
            WHERE p.user_id = :profile_user_id";

        $params = [
            'profile_user_id' => $profileUserId,
            'current_user_id' => $currentUserId
        ];

        if ($sinceId > 0) {
            $sql .= " AND p.post_id > :since_id";
            $params['since_id'] = $sinceId;
        }

        $sql .= " ORDER BY p.post_timestamp DESC";

        $statement = $this->run($sql, $params);
        $posts = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($posts)) {
            return [];
        }

        return $this->attachCommentsAndSanitize($posts);
    }

    public function getPostById(int $postId, int $currentUserId): ?array {
        $sql = "SELECT
                p.post_id AS postId,
                p.post_text AS postText,
                p.post_timestamp AS createdAt,
                u.username,
                u.first_name AS firstName,
                u.last_name AS lastName,
                u.profile_image_url AS profileImageUrl,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id AND user_id = :current_user_id) AS userHasLiked
            FROM
                posts p
            JOIN
                users u ON p.user_id = u.user_id
            WHERE p.post_id = :post_id";

        $stmt = $this->run($sql, ['current_user_id' => $currentUserId, 'post_id' => $postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            return null;
        }

        $postsArray = $this->attachCommentsAndSanitize([$post]);
        return $postsArray[0];
    }

    public function getPostUpdates(array $postIds, int $currentUserId): array {
        if (empty($postIds)) {
            return [];
        }

        $updates = [];
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $likeSql = "SELECT
                        p.post_id,
                        COUNT(l.user_id) AS likeCount,
                        MAX(CASE WHEN l.user_id = ? THEN 1 ELSE 0 END) AS userHasLiked
                    FROM posts p
                    LEFT JOIN post_likes l ON p.post_id = l.post_id
                    WHERE p.post_id IN ($placeholders)
                    GROUP BY p.post_id";

        $likeParams = array_merge([$currentUserId], $postIds);
        $likeStmt = $this->run($likeSql, $likeParams);
        $likeResults = $likeStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($likeResults as $row) {
            $updates[$row['post_id']] = [
                'likeCount' => $row['likeCount'],
                'userHasLiked' => (bool)$row['userHasLiked']
            ];
        }

        $commentSql = "SELECT
                        c.post_id AS postId,
                        c.comment_text AS commentText,
                        c.comment_timestamp AS commentTimestamp,
                        u.username,
                        u.profile_image_url AS profileImageUrl
                       FROM comments c
                       JOIN users u ON c.user_id = u.user_id
                       WHERE c.post_id IN ($placeholders)
                       ORDER BY c.comment_timestamp ASC";
        $commentStmt = $this->run($commentSql, $postIds);
        $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

        $commentsByPostId = [];
        foreach ($comments as $comment) {
            // Sanitize comment text here
            $comment['commentText'] = htmlspecialchars($comment['commentText']);
            $commentsByPostId[$comment['postId']][] = $comment;
        }

        foreach ($postIds as $postId) {
            if (!isset($updates[$postId])) {
                $updates[$postId] = [];
            }
            $updates[$postId]['comments'] = $commentsByPostId[$postId] ?? [];
        }

        return $updates;
    }

    public function deletePost($postId, $userId): bool {
        $sql = "DELETE FROM posts WHERE post_id = :post_id AND user_id = :user_id";
        $stmt = $this->run($sql, ['post_id' => $postId, 'user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function toggleLike(int $postId, int $userId) {
        $checkSql = "SELECT COUNT(*) FROM post_likes WHERE user_id = :user_id AND post_id = :post_id";
        $stmt = $this->run($checkSql, ['user_id' => $userId, 'post_id' => $postId]);
        $isLiked = $stmt->fetchColumn() > 0;

        if ($isLiked) {
            $this->run("DELETE FROM post_likes WHERE user_id = :user_id AND post_id = :post_id", ['user_id' => $userId, 'post_id' => $postId]);
        } else {
            $this->run("INSERT INTO post_likes (user_id, post_id) VALUES (:user_id, :post_id)", ['user_id' => $userId, 'post_id' => $postId]);
        }
    }

    private function attachCommentsAndSanitize(array $posts): array {
        $postIds = array_column($posts, 'postId');
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $commentSql = "SELECT
                        c.post_id AS postId,
                        c.comment_text AS commentText,
                        c.comment_timestamp AS commentTimestamp,
                        u.username,
                        u.profile_image_url AS profileImageUrl
                       FROM comments c
                       JOIN users u ON c.user_id = u.user_id
                       WHERE c.post_id IN ($placeholders)
                       ORDER BY c.comment_timestamp ASC";
        $commentStmt = $this->run($commentSql, $postIds);
        $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

        $commentsByPostId = [];
        foreach ($comments as $comment) {
            $comment['commentText'] = htmlspecialchars($comment['commentText']);
            $commentsByPostId[$comment['postId']][] = $comment;
        }

        foreach ($posts as &$post) {
            $post['postText'] = htmlspecialchars($post['postText']);
            $post['comments'] = $commentsByPostId[$post['postId']] ?? [];
        }

        return $posts;
    }
}