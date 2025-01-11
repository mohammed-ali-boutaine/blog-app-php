

<?php

// require_once "./Database.php";

abstract class Person 
{
     protected  $pdo;
     protected string $username;
     protected string $email;
     protected string $password;
     protected string $picture_path;


     function __construct($pdo,$username, $email, $password, $picture_path)
     {

          $this->pdo = $pdo;
          $this->username = $username;
          $this->email = $email;
          $this->password = $password;
          $this->picture_path = $picture_path;
     }



      function generateToken($userId): bool
     {
          try {

               // Generate and store a token
               $token = bin2hex(random_bytes(16));
               $ip_address = $_SERVER['REMOTE_ADDR'];
               $browser = $_SERVER['HTTP_USER_AGENT'];

               // insert token and info
               $stmt = $this->pdo->prepare(
                    "INSERT INTO user_logins (user_id, ip_address, browser, token) 
               VALUES (:user_id, :ip_address, :browser, :token)"
               );
               $stmt->execute([
                    'user_id' => $userId,
                    'ip_address' => $ip_address,
                    'browser' => $browser,
                    'token' => $token,
               ]);

               // Set cookies
               setcookie("user_id", $userId, time() + (86400 * 7), "/", "", true, true);
               setcookie("auth_token", $token, time() + (86400 * 7), "/", "", true, true);

               return true;
          } catch (PDOException $e) {

               return false;
          }
     }

     function findByEmail(): array
     {
          try {

               // Fetch the user by email
               $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
               $stmt->execute(['email' => $this->email]);

               if ($stmt->rowCount() === 0) {
                    return ['status' => 'error', 'message' => 'user Not Found', "ok" => false];
               }
               $user = $stmt->fetch(PDO::FETCH_ASSOC);
               return ['status' => 'succes', 'message' => $user, "ok" => true];
          } catch (PDOException $e) {
               return ['status' => 'error', 'message' => 'Error fetching user: ' . $e->getMessage(), 'ok' => false];
          }
     }


     function login(): array
     {
          $response = $this->findByEmail();
          if (!$response["ok"]) {
               return $response;
          }

          $user = $response["message"];


          $paaswordIsValdie = password_verify($this->password, $user['password']);

          // Verify the password
          if ($paaswordIsValdie) {

               // generate token and save it in user_login
               $userId = $user["id"];

               $createToken = $this->generateToken($userId, $this->pdo);

               if ($createToken) {
                    return ['status' => 'success', 'message' => 'Logged in successfully', "ok" => true];
               }

               return ['status' => 'error', 'message' => 'Token Problem', "ok" => false];
          } else if (!$paaswordIsValdie) {
               return ['status' => 'error', 'message' => 'Invalid token', 'ok' => false];
          }

          return ['status' => "error", "message" => "Invalid password", "ok" => false];
     }

     public function logout($pdo): array
     {
          try {
               // check if cookies exits
               if (isset($_COOKIE['auth_token'])) {

                    $auth_token = $_COOKIE['auth_token'];
                    $stmt = $pdo->prepare(
                         "UPDATE user_logins 
                         SET is_active = 0, logout_time = NOW() 
                         WHERE token = :token"
                    );
                    $stmt->execute(['token' => $auth_token]);
                    setcookie("auth_token", "", time() - 3600, "/", "", true, true);
                    setcookie("user_id", "", time() - 3600, "/", "", true, true);
                    return ['status' => 'success', 'message' => 'Lougout succesfly', "ok" => true];
               } else {

                    return ['status' => 'success', 'message' => 'Error , No token found', "ok" => false];
               }
          } catch (PDOException $e) {
               return ['status' => 'error', 'message' => 'Failed to register user: ' . $e->getMessage(), "ok" => false];
          }
     }
}
