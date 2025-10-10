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

    public function getAllPosts(int $userId, int $limit, int $offset): array
    {
        $sql = "SELECT
                    p.post_id AS postId,
                    p.post_text AS postText,
                    p.post_timestamp AS postDate,
                    u.user_id AS userId,
                    u.username,
                    u.first_name AS firstName,
                    u.last_name AS lastName,
                    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
                    EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.post_id AND user_id = :userId) AS userHasLiked
                FROM
                    posts p
                JOIN
                    users u ON p.user_id = u.user_id
                WHERE p.is_deleted = 0
                ORDER BY
                    p.post_id DESC
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->attachCommentsAndSanitize($posts);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getNewPostsSince(int $userId, int $sinceId): array
    {
        $sql = "SELECT
                    p.post_id AS postId,
                    p.post_text AS postText,
                    p.post_timestamp AS postDate,
                    u.user_id AS userId,
                    u.username,
                    u.first_name AS firstName,
                    u.last_name AS lastName,
                    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
                    EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.post_id AND user_id = :userId) AS userHasLiked
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                WHERE p.post_id > :sinceId AND p.is_deleted = 0
                ORDER BY p.post_id DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':sinceId', $sinceId, PDO::PARAM_INT);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->attachCommentsAndSanitize($posts);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getPostsByUserId(int $profileUserId, int $currentUserId): array {
        $sql = "SELECT
                p.post_id AS postId,
                p.post_text AS postText,
                p.post_timestamp AS postDate,
                u.user_id AS userId,
                u.username,
                u.first_name AS firstName,
                u.last_name AS lastName,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
                EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.post_id AND user_id = :current_user_id) AS userHasLiked
            FROM
                posts p
            JOIN
                users u ON p.user_id = u.user_id
            WHERE p.user_id = :profile_user_id AND p.is_deleted = 0
            ORDER BY p.post_timestamp DESC";

        $params = [
            'profile_user_id' => $profileUserId,
            'current_user_id' => $currentUserId
        ];

        $statement = $this->run($sql, $params);
        $posts = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachCommentsAndSanitize($posts);
    }

    public function getPostById(int $postId, int $currentUserId): ?array {
        $sql = "SELECT
                p.post_id AS postId,
                p.post_text AS postText,
                p.post_timestamp AS postDate,
                u.user_id AS userId,
                u.username,
                u.first_name AS firstName,
                u.last_name AS lastName,
                u.profile_image_url AS profileImageUrl,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
                EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.post_id AND user_id = :userId) AS userHasLiked
            FROM
                posts p
            JOIN
                users u ON p.user_id = u.user_id
            WHERE p.post_id = :post_id";

        $params = [
            'userId' => $currentUserId,
            'post_id' => $postId
        ];

        $stmt = $this->run($sql, $params);
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
        $sql = "UPDATE posts SET is_deleted = 1 WHERE post_id = :post_id AND user_id = :user_id";
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
        if (empty($posts)) {
            return [];
        }
        $postIds = array_column($posts, 'postId');
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $commentSql = "
            WITH RankedComments AS (
                SELECT
                    c.post_id, c.comment_text, c.comment_timestamp, c.user_id,
                    ROW_NUMBER() OVER(PARTITION BY c.post_id ORDER BY c.comment_timestamp DESC) as rn
                FROM comments c
                WHERE c.post_id IN ($placeholders)
            )
            SELECT
                rc.post_id AS postId, rc.comment_text AS commentText, rc.comment_timestamp AS commentTimestamp,
                u.username, rc.user_id as userId
            FROM RankedComments rc
            JOIN users u ON rc.user_id = u.user_id
            WHERE rc.rn <= 3
            ORDER BY rc.comment_timestamp ASC";

        $commentStmt = $this->run($commentSql, $postIds);
        $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

        $commentsByPostId = [];
        foreach ($comments as $comment) {
            $comment['commentText'] = htmlspecialchars($comment['commentText']);
            $comment['username'] = htmlspecialchars($comment['username']);
            $commentsByPostId[$comment['postId']][] = $comment;
        }

        foreach ($posts as &$post) {
            $post['postText'] = htmlspecialchars($post['postText']);
            $post['username'] = htmlspecialchars($post['username']);
            $post['firstName'] = htmlspecialchars($post['firstName']);
            $post['lastName'] = htmlspecialchars($post['lastName']);
            $post['comments'] = $commentsByPostId[$post['postId']] ?? [];
        }

        return $posts;
    }
}