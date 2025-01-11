

<?php

require_once __DIR__."\Person.php";

class User extends Person
{

    // blogger has blogs
    private $blogs = [];

    function __construct($pdo,$username, $email, $password, $picture_path)
    {
        parent::__construct($pdo,$username, $email, $password, $picture_path);
    }


    public function register(): array
    {
        try {

            // Check if user already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $this->email]);
            if ($stmt->rowCount() > 0) {
                return ["status" => "error", "message" => "This email already exists", "ok" => false];
            }

            // Hash the password
            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

            // Insert the new user
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, email, password, profile_picture) 
              VALUES (:username, :email, :password, :picture_path)"
            );
            $success = $stmt->execute([
                'username' => $this->username,
                'email' => $this->email,
                'password' => $hashedPassword,
                'picture_path' => $this->picture_path,
            ]);

            if ($success) {
                $userId = $this->pdo->lastInsertId();
                $createToken = self::generateToken($userId, $this->pdo);

                if ($createToken) {
                    return ["message" => "success", "message" => "user created ", "ok" => true];
                }

                return ['status' => 'error', 'message' => 'faild to create user', "ok" => false];
            }

            return ["status" => "error", "message" => "Registration failed", "ok" => false];
        } catch (PDOException $e) {
            $message = "Failed to register user:" . $e->getMessage();
            return ['status' => 'error', 'message' => $message, "ok" => false];
        }
    }
}
