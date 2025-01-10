
<?php 



class Comments{
     private $blogId;
     private $userId;
     private $content;
     private $likes=[];


     static function getComments($articleId){
          
     }


     function save($pdo){

          $query = "INSERT INTO comments  (blog_id , user_id , content) VALUES (:blog_id,:user_id,:content)" ;

          $pdo->prepare($query);
          $pdo->excute([
               "blog_id"=>$this->blogId,
               "user_id"=>$this->userId,
               "content"=>$this->content,
          ]);

     }

     static function getCommentsByBlog($userId,$pdo){
          $query = "SELECT * FROM comments WHERE user_id = :user_id";

     }



}