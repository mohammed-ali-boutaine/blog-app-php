<?php
require '../../db/conn.php'; 
include "../../functions/helpers.php";

// Check user authentication
if (!check_user_authentication()) {
    redirect("../");
}

$article_id = sanitize_input($_GET['article_id']);
$user_id = $_COOKIE['user_id'];

if (empty($article_id) || !is_numeric($article_id)) {
    redirect("/");
}

// Check if the user already liked the article
$stmt = $conn->prepare("SELECT id FROM likes WHERE article_id = ? AND user_id = ?");
$stmt->bind_param("ii", $article_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $delete_stmt = $conn->prepare("DELETE FROM likes WHERE article_id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $article_id, $user_id);
    $delete_stmt->execute();
} else {
    $insert_stmt = $conn->prepare("INSERT INTO likes (article_id, user_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $article_id, $user_id);
    $insert_stmt->execute();
}

redirect("/");
