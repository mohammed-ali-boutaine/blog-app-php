<?php

// require '../db/conn.php';
include "../functions/helper.php";
include "../classes/User.php";
include "../classes/Database.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // get data from form
    $username = sanitize_input($_POST["username"]);
    $email = sanitize_input($_POST["email"]);
    $password = sanitize_input($_POST["password"]);

    $isValid = true;
    $error = "";
    $succes = "";


    // Validate form inputs
    if (empty($email) || empty($password) || empty($username)) {
        $error = "All fields are required.";
        $isValid = false;

        // email validation
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
        $isValid = false;
    }


    // Profile Image Handling
    if (!empty($_FILES['profile_image']['name'])) {
        $targetDir = "../public/images/users/"; // Folder to save uploaded images
        $fileName = basename($_FILES["profile_image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        // Generate a unique file name
        $uniqueName = uniqid('user_', true) . '.' . $fileType;

        // Construct the full path for the file
        $targetFilePath = $targetDir . $uniqueName;

    // Allow only image file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($fileType), $allowedTypes)) {
        $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        $isValid = false;
    }
    // Check if the directory exists and create it if it doesn't
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            $error = "Failed to create the directory.";
            $isValid = false;
        }
    }
         // Move file to the target directory
    if ($isValid && !move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)) {
        $error = "Failed to upload profile image.";
        $isValid = false;
    }
    } else {
        $error = "Profile image is required.";
        $isValid = false;
    }


    if ($isValid) {

        $db = new Database();
        $pdo = $db->connect();
        $user = new User($pdo,$username,$email,$password,$targetFilePath);
        $response = $user->register();

        if($response["ok"]){
            $success = $response["message"];

        }else{
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
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<?php
include "./inc/nav.php";
?>


<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-sm bg-white rounded-lg shadow-md p-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <h2 class="text-2xl font-bold text-gray-800 text-center">Register</h2>

            <?php if (!empty($error)): ?>
                <div class="bg-red-500 text-white text-sm p-3 rounded-md">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="space-y-4">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input
                        type="text"
                        name="username"
                        id="username"
                        placeholder="Username"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-800">
                </div>

                <!-- Email -->
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

                <!-- Password -->
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

                <!-- Profile Image -->
                <div>
                    <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Image</label>
                    <input
                        type="file"
                        name="profile_image"
                        id="profile_image"
                        accept="image/*"
                        required
                        class="mt-1 block w-full text-gray-800">
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Register
            </button>

            <!-- Login Link -->
            <p class="text-sm text-center text-gray-600">
                Already have an account?
                <a href="user_login.php" class="text-blue-500 hover:underline">Login here</a>
            </p>
        </form>
    </div>
</div>





<?php
include "./inc/footer.php";
?>