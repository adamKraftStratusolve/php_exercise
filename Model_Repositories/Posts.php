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

    public function getAllPosts(int $userId, int $limit, int $offset) {
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
                WHERE
                    p.is_deleted = 0
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
            return $this->attachCommentsAndSanitize($posts); // Ensure sanitization
        } catch (PDOException $e) {
            // In production, you would log the error. For now, we die to see the error.
            die("Database Error in getAllPosts: " . $e->getMessage());
        }
    }

    public function getPostsByUserId(int $profileUserId, int $currentUserId): array {
        $sql = "SELECT
                p.post_id AS postId,
                p.post_text AS postText,
                p.post_timestamp AS postDate,
                u.username,
                u.first_name AS firstName,
                u.last_name AS lastName,
                u.profile_image_url AS profileImageUrl,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
                EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.post_id AND user_id = :current_user_id) AS userHasLiked
            FROM
                posts p
            JOIN
                users u ON p.user_id = u.user_id
            WHERE p.user_id = :profile_user_id
            ORDER BY p.post_timestamp DESC";

        $statement = $this->run($sql, ['profile_user_id' => $profileUserId, 'current_user_id' => $currentUserId]);
        $posts = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $this->attachCommentsAndSanitize($posts);
    }

    public function getPostById(int $postId, int $currentUserId): ?array {
        $sql = "SELECT
                    p.post_id AS postId,
                    p.post_text AS postText,
                    p.post_timestamp AS postDate,
                    u.username,
                    u.first_name AS firstName,
                    u.last_name AS lastName,
                    u.profile_image_url AS profileImageUrl,
                    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
                    EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.post_id AND user_id = :current_user_id) AS userHasLiked
                FROM
                    posts p
                JOIN
                    users u ON p.user_id = u.user_id
                WHERE
                    p.user_id = :profile_user_id AND p.is_deleted = 0
                ORDER BY
                    p.post_timestamp DESC";

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
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        // Fetch Like Updates
        $likeSql = "SELECT post_id, COUNT(*) AS likeCount FROM post_likes WHERE post_id IN ($placeholders) GROUP BY post_id";
        $likeStmt = $this->run($likeSql, $postIds);
        $likeCounts = $likeStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $userLikeSql = "SELECT post_id, 1 FROM post_likes WHERE user_id = ? AND post_id IN ($placeholders)";
        $userLikeStmt = $this->run($userLikeSql, array_merge([$currentUserId], $postIds));
        $userLikes = $userLikeStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Fetch Comment Updates
        $commentSql = "SELECT
                        c.post_id AS postId,
                        c.comment_text AS commentText,
                        u.username,
                        u.profile_image_url AS profileImageUrl
                       FROM comments c
                       JOIN users u ON c.user_id = u.user_id
                       WHERE c.post_id IN ($placeholders)
                       ORDER BY c.comment_timestamp ASC";
        $commentStmt = $this->run($commentSql, $postIds);
        $allComments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

        $updates = [];
        foreach ($postIds as $id) {
            $updates[$id] = [
                'likeCount' => $likeCounts[$id] ?? 0,
                'userHasLiked' => isset($userLikes[$id]),
                'comments' => []
            ];
        }

        foreach ($allComments as $comment) {
            // Sanitize comment text and username here
            $comment['commentText'] = htmlspecialchars($comment['commentText']);
            $comment['username'] = htmlspecialchars($comment['username']);
            $updates[$comment['postId']]['comments'][] = $comment;
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
                    u.profile_image_url AS profileImageUrl,
                    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS likeCount,
                    EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.post_id AND user_id = :userId) AS userHasLiked
                FROM
                    posts p
                JOIN
                    users u ON p.user_id = u.user_id
                WHERE
                    p.post_id > :sinceId AND p.is_deleted = 0
                ORDER BY
                    p.post_id DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':sinceId', $sinceId, PDO::PARAM_INT);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Sanitize the data before returning it
            foreach ($posts as &$post) {
                $post['postText'] = htmlspecialchars($post['postText']);
                $post['username'] = htmlspecialchars($post['username']);
                $post['firstName'] = htmlspecialchars($post['firstName']);
                $post['lastName'] = htmlspecialchars($post['lastName']);
            }

            return $this->attachCommentsAndSanitize($posts);

        } catch (PDOException $e) {
            die("Database Error in getNewPostsSince: " . $e->getMessage());
        }
    }

    private function attachCommentsAndSanitize(array $posts): array {
        if (empty($posts)) {
            return [];
        }
        $postIds = array_column($posts, 'postId');
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $commentSql = "SELECT
                        c.post_id AS postId,
                        c.comment_text AS commentText,
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