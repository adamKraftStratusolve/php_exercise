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
        return $statement->rowCount() > 0;
    }

    public function getAllPosts() {
        $sql = "SELECT p.*, u.username 
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                ORDER BY p.post_timestamp DESC";

        $statement = $this->run($sql);
        $results = $statement->fetchAll();

        $postObjects = [];
        foreach ($results as $row) {
            $post = new Posts($this->pdo);
            $post->mapData($row);
            $postObjects[] = $post;
        }
        return $postObjects;
    }

    public function getPostsByUserId(int $userId) {
        $sql = "SELECT p.*, u.username 
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                WHERE p.user_id = :user_id 
                ORDER BY p.post_timestamp DESC";

        $statement = $this->run($sql, ['user_id' => $userId]);
        $results = $statement->fetchAll();

        $postObjects = [];
        foreach ($results as $row) {
            $post = new Posts($this->pdo);
            $post->mapData($row);
            $postObjects[] = $post;
        }
        return $postObjects;
    }
}