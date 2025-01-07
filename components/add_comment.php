

<?php
require '../../db/conn.php';  
include "../../functions/helpers.php";
if (!check_user_authentication()) {
     redirect("../");
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $article_id = isset($_GET["article_id"]) ? sanitize_input($_GET["article_id"]) : null;
     $comment = isset($_POST["comment"]) ? sanitize_input($_POST["comment"]) : null;
     $user_id = isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : null;

     if (empty($article_id) || !is_numeric($article_id) || empty($comment)) {
          redirect("/");
     } else {


          $stmt = $conn->prepare("INSERT INTO commentaires (article_id, user_id, content) VALUES (?, ?, ?)");
          $stmt->bind_param("iis", $article_id, $user_id, $comment);
          $stmt->execute();
     }

     redirect("/");
}
