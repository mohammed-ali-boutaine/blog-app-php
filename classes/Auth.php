<?php


class Auth
{
    private PDO $pdo;
    
    private static $mode = "dev"; 
    // private ?string $error = null;

    public function __construct(Database $database)
    {
        $this->pdo = $database->connect();
    }

    // Method to register a new user
    public function register(string $username, string $email, string $password, string $picture_path): array
    {
        try {

            // sanitization and validation
            $username = htmlspecialchars(strip_tags($username), ENT_QUOTES, 'UTF-8');
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);


            // Check if user already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->rowCount() > 0) {
                return ["status" => "error", "message" => "This email already exists", "ok" => false];
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

                return ["message" => "success", "message" => "user created ", "ok" => true];
            }

            return ["status" => "error", "message" => "Registration failed", "ok" => false];
        } catch (PDOException $e) {
            $message = self::$mode == "dev" ? "Failed to register user:" . $e->getMessage() : "Failed to register user";
            return ['status' => 'error', 'message' => $message, "ok" => false];
        }
    }

    // Method to log in a user
    public function login(string $email, string $password): array
    {
        // Fetch the user by email
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->rowCount() === 0) {
            return ['status' => 'error', 'message' => 'user Not Found', "ok" => false];
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $paaswordIsValdie = password_verify($password, $user['password']);

        // Verify the password
        if ($user && $paaswordIsValdie ) {
            // Start a session for the user
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            return ['status' => 'success', 'message' => 'Logged in successfully', "ok" => true];
        }else if(!$paaswordIsValdie){
            return ['status' => 'error', 'message' => 'Invalid token', 'ok' => false];
        }

        return ['status' => "error", "message" => "Invalid password", "ok" => false];
    }

    // Method to check authentication
     function checkAuth(): array {

        try{
            if (isset($_COOKIE['user_id']) && isset($_COOKIE['auth_token'])) {


                $user_id = sanitize_input($_COOKIE['user_id']);
                $auth_token = sanitize_input($_COOKIE['auth_token']);

                $query = "
                select r.role_name 
                from users u 
                join user_logins ul 
	                on u.id = ul.user_id 
                join roles r 
	                on u.role_id = r.id
                WHERE 
	                user_id = :user_id 
	                AND token = :token
	                AND is_active = 1";
                $stmt = $this->pdo->prepare($query);
                // excute statment
                $stmt->execute(["user_id" => $user_id , "token"=> $auth_token]);

                $userRole = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userRole) {
                    return [
                        'status' => 'success', 
                        'message' => 'Authentication successful.', 
                        'ok' => true, 
                        'role' => $userRole['role_name']
                    ];
                } else {
                    return [
                        'status' => 'error', 
                        'message' => 'Invalid token or user.', 
                        'ok' => false
                    ];
                }
               
            }else{
                return ['status' => 'success', 'message' => 'Error , No token found', "ok" => false];
            }
        }catch(PDOException $e){
            return ['status' => 'error', 'message' => 'Failed to register user: ' . $e->getMessage(), "ok" => false];

        }

    }

    // Method to log out a user
    public function logout(): array
    {
        try {
            if (isset($_COOKIE['auth_token'])) {
              
                $auth_token = $_COOKIE['auth_token'];
                $stmt = $this->pdo->prepare(
                    "UPDATE user_logins 
                 SET is_active = 0, logout_time = NOW() 
                 WHERE token = :token"
                );
                $stmt->execute(['token' => $auth_token]);
                setcookie("auth_token", "", time() - 3600, "/", "", true, true);
                setcookie("user_id", "", time() - 3600, "/", "", true, true);
                return ['status' => 'success', 'message' => 'Lougout succesfly', "ok" => true];
            }else{
                
                return ['status' => 'success', 'message' => 'Error , No token found', "ok" => false];
            }


        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Failed to register user: ' . $e->getMessage(), "ok" => false];
        }
    }
}


