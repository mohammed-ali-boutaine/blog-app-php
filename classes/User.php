<?php
require_once 'Database.php';


class User
{
     private PDO $pdo;
     public function __construct(Database $database)
     {
          $this->pdo = $database->connect();
     }

     public function updateProfil() {}



     // blog events
     public function getBlogs($userId){
          try {


               $query = "
          SELECT 
          a.id AS article_id,
          a.title,
          a.content,
          a.image_path AS article_image,
          a.created_at as datetime,
          u.id AS user_id,
          u.username AS username,
          u.profile_picture AS user_picture,
          c.id AS comment_id,
          c.content AS comment_content,
          c.created_at AS comment_date,
          cu.id AS comment_user_id,
          cu.username AS comment_username,
          cu.profile_picture AS comment_user_picture,
          (SELECT COUNT(*) FROM likes l WHERE l.article_id = a.id) AS like_count,
          (SELECT COUNT(*) FROM commentaires c WHERE c.article_id = a.id) AS comment_count,
          EXISTS (
               SELECT 1
               FROM likes l
               WHERE l.article_id = a.id AND l.user_id = :userId
          ) AS user_liked
     FROM 
          articles a
     JOIN 
          users u ON u.id = a.user_id
     LEFT JOIN 
          likes l ON l.article_id = a.id
     LEFT JOIN 
          commentaires c ON c.article_id = a.id
     LEFT JOIN 
          users cu ON cu.id = c.user_id
     GROUP BY 
          a.id, u.id, c.id, c.content, c.created_at, cu.id, cu.username, cu.profile_picture
     ORDER BY 
          c.created_at ASC;  ";


     $stmt = $this->pdo->prepare($query);
     $stmt->execute(["userId" => $userId]);

     /*  u can also use 
     $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
     $stmt->execute();
     
     */

               // Fetch the data as an associative array
               $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
               $articles = [];
               foreach ($result as $row) {
                    $article_id = $row['article_id'];
                    if (!isset($articles[$article_id])) {
                         $articles[$article_id] = [
                              'id' => $article_id,
                              'title' => $row['title'],
                              'datetime' => $row['datetime'],
                              'content' => $row['content'],
                              'image' => $row['article_image'],
                              'author_name' => $row['username'],  // Correct key
                              'author_picture' => $row['user_picture'],  // Correct key
                              'user_liked' => $row['user_liked'],
                              'like_count' => $row['like_count'],
                              'comment_count' => $row['comment_count'],
                              'comments' => []
                         ];
                    }
                    if ($row['comment_id']) {
                         $articles[$article_id]['comments'][] = [
                              'content' => $row['comment_content'],
                              'date' => $row['comment_date'],
                              'user_name' => $row['comment_username'],  // Correct key
                              'user_picture' => $row['comment_user_picture']  // Correct key
                         ];
                    }
               }
               return [ 
                    "status"=>"succes",
                    "ok"=>true ,
                    "message" => "fetch succesflly",
                    "data" => $articles
               ];
          } catch (PDOException $e) {
               return [ 
                    "status"=>"error",
                    "ok"=>false ,
                    "message" => $e->getMessage()
               ];
          }
     }



     public function createBlog() {}

     public function deleteBlog() {}

     public function updateBlog() {}


     public function getBlogByUserId() {}
}
