<?php
require_once 'Base_Model.php';

class Posts extends Model {
    public $PostId;
    public $PostTimeStamp;
    public $PostText;
    public $UserId;
    public $Username;

    protected $map = [
        'post_id' => 'PostId',
        'post_timestamp' => 'PostTimeStamp',
        'post_text' => 'PostText',
        'user_id' => 'UserId',
        'username' => 'Username'
    ];

    public function createPost(): bool {
        $sql = "INSERT INTO posts (post_text, user_id) VALUES (:post_text, :user_id)";
        $params = [
            'post_text' => $this->PostText,
            'user_id' => $this->UserId
        ];
        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function getAllPosts(int $currentUserId, int $sinceId = 0) {

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
            users u ON p.user_id = u.user_id";

        $params = ['current_user_id' => $currentUserId];

        if ($sinceId > 0) {
            $sql .= " WHERE p.post_id > :since_id";
            $params['since_id'] = $sinceId;
        }

        $sql .= " ORDER BY p.post_timestamp DESC;";

        $stmt = $this->run($sql, $params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($posts)) {
            return [];
        }

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
            $commentsByPostId[$comment['postId']][] = $comment;
        }

        foreach ($posts as $key => $post) {
            $posts[$key]['comments'] = $commentsByPostId[$post['postId']] ?? [];
        }

        return $posts;
    }
    public function getPostsByUserId(int $profileUserId, int $currentUserId): array {

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
        WHERE
            p.user_id = :profile_user_id
        ORDER BY
            p.post_timestamp DESC";

        $params = [
            'profile_user_id' => $profileUserId,
            'current_user_id' => $currentUserId
        ];

        $statement = $this->run($sql, $params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePost($postId, $userId): bool {
        $sql = "DELETE FROM posts WHERE post_id = :post_id AND user_id = :user_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();

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
}