<?php
require '../db/conn.php';
include "../functions/helpers.php";



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

     // get data from form
    $email =sanitize_input($_POST["email"]);
    $password =sanitize_input($_POST["password"]);

    $isValid = true;
    $error = "";

    
     // Validate form inputs
     if (empty($email) || empty($password)) {
          $error = "All fields are required.";
          $isValid = false;
     } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $error = "Invalid email format.";
          $isValid = false;
     }

     if($isValid){
          
   

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {

     if(password_verify($password, $user["password"])){
          $token = bin2hex(random_bytes(16));
          $ip_address = $_SERVER['REMOTE_ADDR'];
          $browser = $_SERVER['HTTP_USER_AGENT'];
  
          // Insert into userLogin table
          $stmt = $conn->prepare("INSERT INTO user_logins (user_id, ip_address, browser, token) VALUES (?, ?, ?, ?)");
          $stmt->bind_param("isss", $user['id'], $ip_address, $browser, $token);
          $stmt->execute();
  
          // Set cookies
          setcookie("user_id", $user['id'], time() + (86400 * 7), "/", "", true, true);
          setcookie("auth_token", $token, time() + (86400 * 7), "/", "", true, true);          
  
          header("Location: ../index.php");
          exit;
     }else{
          $error = "Invalide Password";
     }
    } else {
          $error = "Email not exists.";
     }

    $stmt->close();
}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Login</title>
     <script src="https://cdn.tailwindcss.com"></script>

</head>

<?php 
include "./inc/nav.php";
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100">
  <div class="w-full max-w-sm bg-white rounded-lg shadow-md p-6">
    <form method="POST" class="space-y-6">
      <h2 class="text-2xl font-bold text-gray-800 text-center">Login</h2>
      <?php if (!empty($error)): ?>
        <div class="text-sm text-white bg-red-500 p-3 rounded-md">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <div class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input 
            type="email" 
            name="email" 
            id="email" 
            placeholder="Email" 
            required 
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-800"
          >
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input 
            type="password" 
            name="password" 
            id="password" 
            placeholder="Password" 
            required 
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-800"
          >
        </div>
      </div>
      <button 
        type="submit" 
        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      >
        Login
      </button>
      <p class="text-sm text-center text-gray-600">
        Don't have an account? 
        <a href="user_register.php" class="text-blue-500 hover:underline">Register here</a>
      </p>
    </form>
  </div>
</div>




<?php 
include "./inc/footer.php";
?>