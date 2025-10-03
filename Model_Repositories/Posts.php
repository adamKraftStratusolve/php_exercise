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

    public function getAllPosts(int $currentUserId): array {

        $sql = "SELECT
                p.post_id AS PostID,
                p.post_text AS PostText,
                p.post_timestamp AS CreatedAt,
                u.username AS Username,
                u.first_name AS FirstName,
                u.last_name AS LastName,
                u.profile_image_url,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id) AS like_count,
                (SELECT COUNT(*) FROM post_likes WHERE post_id = p.post_id AND user_id = :current_user_id) AS user_has_liked
            FROM
                posts p
            JOIN
                users u ON p.user_id = u.user_id
            ORDER BY
                p.post_timestamp DESC;";

        $stmt = $this->run($sql, ['current_user_id' => $currentUserId]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($posts)) {
            return [];
        }

        $postIds = array_column($posts, 'PostID');
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $commentSql = "SELECT 
                        c.post_id, c.comment_text, c.comment_timestamp, 
                        u.username, u.profile_image_url 
                       FROM comments c 
                       JOIN users u ON c.user_id = u.user_id 
                       WHERE c.post_id IN ($placeholders) 
                       ORDER BY c.comment_timestamp ASC";
        $commentStmt = $this->run($commentSql, $postIds);
        $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

        $commentsByPostId = [];
        foreach ($comments as $comment) {
            $commentsByPostId[$comment['post_id']][] = $comment;
        }

        foreach ($posts as $key => $post) {
            $posts[$key]['comments'] = $commentsByPostId[$post['PostID']] ?? [];
        }

        return $posts;
    }

    public function getPostsByUserId(int $userId): array {
        $sql = "SELECT 
                p.post_id AS PostID,
                p.post_text AS PostText,
                p.post_timestamp AS CreatedAt,
                u.username AS Username 
            FROM 
                posts p
            JOIN 
                users u ON p.user_id = u.user_id
            WHERE 
                p.user_id = :user_id 
            ORDER BY 
                p.post_timestamp DESC";

        $statement = $this->run($sql, ['user_id' => $userId]);
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