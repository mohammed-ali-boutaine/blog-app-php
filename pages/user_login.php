<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 32-byte random token
}

$csrf_token = $_SESSION['csrf_token'];


include "../functions/helper.php";
include "../classes/User.php";
include "../classes/Database.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $error = "Invalid CSRF token.";
    $isValid = false;
  }
  // get data from form
  $email = sanitize_input($_POST["email"]);
  $password = sanitize_input($_POST["password"]);

  $isValid = true;
  $error = "";
  $succes = "";


  // Validate form inputs
  if (empty($email) || empty($password)) {
    $error = "All fields are required.";
    $isValid = false;
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
    $isValid = false;
  }

  if ($isValid) {

    $db = new Database();
    $pdo = $db->connect();
    $user = new User($pdo, "", $email, $password, "");;
    $response = $user->login();
    if ($response["ok"]) {
      $success = $response["message"];
    } else {
      $error = $response["message"];
    }
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
      <!-- CSRF Token -->
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

      <?php if (!empty($error)): ?>
        <div class="bg-red-500 text-white text-sm p-3 rounded-md">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php elseif (!empty($success)): ?>
        <div class="bg-green-500 text-white text-sm p-3 rounded-md">
          <?= htmlspecialchars($success) ?>
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
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-800">
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            type="password"
            name="password"
            id="password"
            placeholder="Password"
            required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-800">
        </div>
      </div>
      <button
        type="submit"
        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
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