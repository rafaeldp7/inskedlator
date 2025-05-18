<?php

require_once __DIR__ . '/../config.php';

class AdminModel
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists()
    {
        // Create Admins Table
        $sqlAdmins = "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        )";

        if (!$this->conn->query($sqlAdmins)) {
            error_log("Error creating admins table: " . $this->conn->error);
            die("Database setup error.");
        }
    }

    
    public function addAdmin($username, $password)
    {
        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Admin added successfully."];
        } else {
            return ["status" => "error", "message" => "Error adding admin: " . $stmt->error];
        }
    }

    public function logout()
    {
        session_start();
        session_destroy();
        echo json_encode(["status" => "success"]);
        exit();
    }
}

?>
