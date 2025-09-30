<?php
class Posts extends Database {
    public $PostTimeStamp;
    public $PostText;
    public $UserId;

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
        return $statement->fetchAll(PDO::FETCH_CLASS, 'Posts');
    }

    public function getPostsByUserId() {

        $sql = "SELECT * FROM posts WHERE user_id = :user_id ORDER BY post_timestamp DESC";

        $statement = $this->run($sql, ['user_id' => $this->UserId]);
        return $statement->fetchAll(PDO::FETCH_CLASS, 'Posts');
    }
}