<?php

require_once "./Comments.php";
class Blog
{
     private $userId;
     private $title;
     private $content; // optinel
     private $image_path; // optienl
     private $comments = [];
     private $likes = [];

     public function __construct($userId, $title, $content = null, $image_path = null)
     {
          // $this->pdo = $database->connect();
          $this->userId = $userId;
          $this->title = $title;
          $this->content = $content;
          $this->image_path = $image_path;
          // $this->comments = new Comments::getComments($)
     }

     public function save($pdo)
     {
          $sql = "INSERT INTO blogs (user_id,title,content,image_path) VALUES ( :user_id , :title , :content , :image_path )";
          $stmt = $pdo->prepare($sql);
          return $stmt->execute([
               "user_id" => $this->userId,
               "title" => $this->title,
               "content" => $this->content,
               "image_path" => $this->image_path,

          ]);
     }



     public function update($pdo, $id, $data)
     {
          $sql = "UPDATE blogs SET title = :title, content = :content, image_path = :image_path WHERE id = :id";
          $stmt = $pdo->prepare($sql);
          return $stmt->execute([
               'title' => $data['title'] ?? $this->title,
               'content' => $data['content'] ?? $this->content,
               'image_path' => $data['image_path'] ?? $this->image_path,
               'id' => $id
          ]);
     }

     static public function getAll($pdo)
     {
          $sql = "SELECT * FROM blogs";
          $stmt = $pdo->query($sql);
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }

     public function delete($pdo, $id)
     {
          $sql = "DELETE FROM clients WHERE id = :id";
          $stmt = $pdo->prepare($sql);
          return $stmt->execute(['id' => $id]);
     }

     
     static public function getById($pdo, $id)
     {
          $sql = "SELECT * FROM blogs WHERE id = :id";
          $stmt = $pdo->prepare($sql);
          $stmt->execute(['id' => $id]);
          return $stmt->fetch(PDO::FETCH_ASSOC);
     }

     public function addComment($comment)
     {
          $this->comments[] = $comment;
     }

     public function getComments()
     {
          return $this->comments;
     }

     public function addLike($userId)
     {
          if (!in_array($userId, $this->likes)) {
               $this->likes[] = $userId;
          }
     }

     public function getLikes()
     {
          return count($this->likes);
     }



}
