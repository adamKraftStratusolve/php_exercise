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

    public function createPost() {
        $sql = "INSERT INTO posts (post_text, user_id) VALUES (:post_text, :user_id)";
        $params = [
            'post_text' => $this->PostText,
            'user_id' => $this->UserId
        ];
        $statement = $this->run($sql, $params);

        if ($statement->rowCount() > 0) {
            $this->pdo->commit();
        }

        return $statement->rowCount() > 0;
    }

    public function getAllPosts() {

        $sql = "SELECT
                p.post_text AS PostText,
                p.post_timestamp AS CreatedAt,
                u.Username,
                u.first_name AS FirstName,
                u.last_name AS LastName
            FROM
                posts p
            JOIN
                users u ON p.user_id = u.user_id
            ORDER BY
                p.post_timestamp DESC;";

        $statement = $this->run($sql);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostsByUserId(int $userId) {
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

    public function deletePost($postId, $userId) {
        $sql = "DELETE FROM posts WHERE post_id = :post_id AND user_id = :user_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}