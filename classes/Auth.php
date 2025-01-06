<?php

require_once 'Database.php';

class Auth
{
    private PDO $pdo;
    private ?string $error = null;

    public function __construct(Database $database)
    {
        $this->pdo = $database->connect();
    }

    // Method to register a new user
    public function register(string $username, string $email, string $password, string $picture_path): array
    {
        try{


        // Check if user already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() > 0) {
            return ["status" => "error", "message"=>"This email already exists", "ok" => false];
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, email, password, profile_picture) 
             VALUES (:username, :email, :password, :picture_path)"
        );
        $success = $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'picture_path' => $picture_path,
        ]);

        if ($success) {
            $userId = $this->pdo->lastInsertId();

            // Generate and store a token
            $token = bin2hex(random_bytes(16));
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $browser = $_SERVER['HTTP_USER_AGENT'];

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

            return ["message" => "success","message"=>"user created ", "ok" => true];
        }

        return ["status" => "error","message" => "Registration failed", "ok" => false];
    }catch(PDOException $e){
        return ['status' => 'error', 'message' => 'Failed to register user: ' . $e->getMessage() , "ok"=> true];
    }
    }

    // Method to log in a user
    public function login(string $email, string $password): string
    {
        // Fetch the user by email
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->rowCount() === 0) {
            return "User not found.";
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if ($user && password_verify($password, $user['password'])) {
            // Start a session for the user
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            return ['status' => 'success', 'message' => 'Logged in successfully' , "ok"=>true];
        }

        return ['status' => "error", "message" => "Invalid password" , "ok"=>false];
    }

    // Method to check authentication
    public function checkAuth(): bool
    {
        session_start();
        return isset($_SESSION['user_id']);
    }

    // Method to log out a user
    public function logout(): bool
    {
        session_start();
        if (isset($_SESSION['auth_token'])) {
            $stmt = $this->pdo->prepare(
                "UPDATE user_logins 
                 SET is_active = 0, logout_time = NOW() 
                 WHERE token = :token"
            );
            $stmt->execute(['token' => $_COOKIE['auth_token']]);
        }

        session_destroy();
        setcookie("auth_token", "", time() - 3600, "/", "", true, true);
        setcookie("user_id", "", time() - 3600, "/", "", true, true);

        return true;
    }
}

// Example Usage
/*
require_once 'Database.php';
$database = new Database();

$auth = new Auth($database);

// Register a new user
$response = $auth->register('JohnDoe', 'johndoe@example.com', 'securepassword', '/images/profile.jpg');
print_r($response);

// Log in
echo $auth->login('johndoe@example.com', 'securepassword');

// Check authentication
var_dump($auth->checkAuth());

// Log out
$auth->logout();
*/
?>
