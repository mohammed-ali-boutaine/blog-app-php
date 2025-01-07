<?php
// require '../../db/conn.php';  // Include your database connection file
// include "../../functions/helpers.php";
// if (!check_user_authentication()) {
//      redirect("../");
//  }
// function showpost($title,$userId,)
$userId = $_COOKIE["user_id"];

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
        WHERE l.article_id = a.id AND l.user_id = ?
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
     c.created_at ASC;
";

$stmt = $conn->prepare($query);

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
// Organizing data into articles array
$articles = [];
while ($row = $result->fetch_assoc()) {
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

$stmt->close();

// echo "<pre>";
// print_r($articles);
// echo "</pre>";
?>


<!-- -----------------  posts   -->

          <!-- -----------  post loop  -->
          <?php foreach ($articles as $article): ?>
               <div id="post" class="post flex box-border border-2 border-slate-800 mb-4 rounded-lg">
               <div class="max-w-2xl shadow-md rounded-lg overflow-hidden border-gray-700 bg-gray-950">

               <!-- Post Content -->
               <div class="p-6 relative">
                    <!-- Three-dot Menu -->
                    <div class="absolute top-4 right-4">
                         <button class="text-gray-300 hover:text-gray-100 focus:outline-none" id="menu-button">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                   <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v.01M12 12v.01M12 18v.01" />
                              </svg>
                         </button>
                         <!-- Menu Popup -->
                         <div id="menu-popup" class="hidden absolute right-0 mt-2 bg-gray-800 text-gray-300 rounded shadow-lg z-10">
                              <ul>
                                   <li class="px-4 py-2 hover:bg-gray-700 cursor-pointer">Edit</li>
                                   <li class="px-4 py-2 hover:bg-gray-700 cursor-pointer">Delete</li>
                                   <li class="px-4 py-2 hover:bg-gray-700 cursor-pointer">Signal</li>
                              </ul>
                         </div>
                    </div>

                    <!-- Author Info -->
                    <div class="flex items-center space-x-4 mb-4">
                         <img class="w-12 h-12 rounded-full object-cover" src="../../<?= htmlspecialchars($article['author_picture']) ?>" alt="Author Image" />
                         <div>
                              <p class="text-white font-bold text-lg"><?= htmlspecialchars($article['author_name']) ?></p>
                              <p class="text-gray-400 text-sm"><?= htmlspecialchars($article['datetime']) ?></p>
                         </div>
                    </div>

                    <!-- Post Title -->
                    <h2 class="text-2xl font-semibold text-gray-100 mb-2"><?= htmlspecialchars($article['title']) ?></h2>

                    <!-- Post Content -->
                    <?php
                    if (isset($article['content'])) {
                         $content = $article['content'];
                         if(strlen($content)>120){
                              $smallcontent = substr($content, 120);
                         }else{
                              $smallcontent =$content;
                         }
                         echo `
                    <p class="text-gray-400 w-full text-sm" id="post-content">
                         $smallcontent
                         <span id="full-content" class="hidden">
                         $content
                         </span>
                    </p>`;
                    }
                    ?>


                    <!-- Read More Button -->
                    <button id="read-more-btn" class="text-blue-400 text-sm font-medium hover:underline">Read more</button>
               </div>

               <!-- Buttons: Like and Comment -->
               <div class="flex items-center justify-between p-4">
                    <div class="flex space-x-4">
                         <!-- Like Button -->
                         <form action="./article/add_like.php?article_id=<?= $article['id'] ?>" method="post">

                         <button type="submit" class="flex items-center space-x-1 <?= $article['user_liked']? 'text-red-500' : 'text-gray-400 hover:text-red-500' ?> ">
                              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                   <path stroke-linecap="round" stroke-linejoin="round" d="M14 9l-.882-3.528a1 1 0 00-.97-.772H9a1 1 0 00-.707.293l-3 3a1 1 0 00-.293.707v6a1 1 0 001 1h3v4a1 1 0 001 1h3a1 1 0 001-1v-4h3a1 1 0 001-1v-2.768a2 2 0 00-.586-1.414l-2-2A2 2 0 0014 9z" />
                              </svg>
                              <span class="text-sm">
                                        <?= htmlspecialchars($article['like_count']) ?>

                              </span>
                         </button>
                    </form>

                         <!-- Comment Button -->
                         <button class="comments-btn flex items-center space-x-1 text-gray-400 hover:text-blue-500">
                              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                   <path stroke-linecap="round" stroke-linejoin="round" d="M3 10v11a1 1 0 001 1h7a1 1 0 001-1v-5h5a1 1 0 001-1V3a1 1 0 00-1-1H4a1 1 0 00-1 1v7z" />
                              </svg>
                              <span class="text-sm"><?= htmlspecialchars($article['comment_count']) ?></span>
                         </button>
                    </div>
               </div>

     <!-- Comments Section -->
     <div class="comments-section rounded-lg p-6 bg-gray-900 text-gray-200 h-full max-h-[520px] overflow-y-scroll">
          <!-- Add New Comment -->
          <div>
               <form action="./article/add_comment.php?article_id=<?= $article['id'] ?>" method="post">
               <textarea name="comment" class="w-full p-3 rounded-md bg-gray-800 text-gray-200 placeholder-gray-400" rows="3" placeholder="Add a comment..."></textarea>
               <button class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">Post Comment</button>
               </form>

          </div>
          <h3 class="text-lg font-bold mb-4">Comments</h3>

          <div class="space-y-4">
               <?php foreach ($article['comments'] as $comment): ?>

               <!-- Comment -->
               <div class="p-4 bg-gray-800 rounded-md">
               <p class="font-semibold"><?= htmlspecialchars($comment['user_name']) ?></p>
               <p class="text-sm text-gray-400"><?= htmlspecialchars($comment['content']) ?></p>
               </div>
    
               <?php endforeach; ?>

          </div>
     </div>



     </div>
</div>
     <?php endforeach; ?>

