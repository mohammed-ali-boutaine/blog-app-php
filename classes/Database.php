
<?php

class Database
{
    private $host = "localhost";
    private $dbName = "blogapp";
    private $username = "root";
    private $password = "root";
    private $charset = "utf8mb4";
    private $pdo;

    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exceptions for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch as associative array
        PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
    ];

    public function connect()
    {
        if ($this->pdo === null) {

            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";

                $this->pdo = new PDO($dsn, $this->username, $this->password, $this->options);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return $this->pdo;
    }
    // Execute an SQL query
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die('Query Error: ' . $e->getMessage());
        }
    }
    // Execute an SQL query and return results
    public function execute($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    // Close the connection
    public function disconnect()
    {
        $this->pdo = null;
    }

    // get post

    public function getBlogs(){
        
    }


}


/*

require_once 'Database.php';

$db = new Database();

// Insert example
$sql = "INSERT INTO users (name, email) VALUES (:name, :email)";
$params = ['name' => 'John Doe', 'email' => 'john@example.com'];
$db->query($sql, $params);
echo "User inserted successfully!";

///  2

require_once 'Database.php';

$db = new Database();

// Select example
$sql = "SELECT * FROM users WHERE email = :email";
$params = ['email' => 'john@example.com'];
$results = $db->execute($sql, $params);

print_r($results);

*/