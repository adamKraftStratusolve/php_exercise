<?php
require_once 'Base_Model.php';

class Comment extends Model {
   public $postId;
   public $userId;
   public $commentText;
    public function create() {

        $sql = "INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)";
        $params = [
            $this->postId,
            $this->userId,
            $this->commentText
        ];

        $statement = $this->run($sql, $params);
        return $statement->rowCount() > 0;
    }
}