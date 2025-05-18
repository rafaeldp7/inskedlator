<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/INSKEDLATOR/config.php';


class User
{

    private \mysqli $conn;

    public function __construct(\mysqli $dbConnection)
    {
        $this->conn = $dbConnection;
    }


    public function findByUserID(string $userID): ?array
    {
        $sql = "SELECT id, lastname, firstname, middlename, userID, password
                  FROM users
                 WHERE userID = ?
                 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (! $stmt) {
            // Log error if you like: error_log($this->conn->error);
            return null;
        }

        $stmt->bind_param('s', $userID);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $stmt->close();
            return null;
        }

        $stmt->bind_result(
            $id,
            $lastname,
            $firstname,
            $middlename,
            $fetchedUserID,
            $hashedPassword
        );
        $stmt->fetch();
        $stmt->close();

        return [
            'id'         => $id,
            'lastname'   => $lastname,
            'firstname'  => $firstname,
            'middlename' => $middlename,
            'userID'     => $fetchedUserID,
            'password'   => $hashedPassword,
        ];
    }



    private function secureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.use_strict_mode', 1);
            session_start();
        }
    }



    public function register($lastname, $firstname, $middlename, $userID, $password)
    {
        if ($this->userIDExists($userID))
            return "UserID already exists!";


        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (lastname, firstname, middlename, userID, password) VALUES (?, ?, ?, ?, ?)");

        if (!$stmt)
            return "Something went wrong!";

        $stmt->bind_param("sssss", $lastname, $firstname, $middlename, $userID, $hashed_password);

        if ($stmt->execute()) {
            $stmt->close();
            return true;  // Success
        } else {
            $stmt->close();
            return "Something went wrong!";
        }
    }

    private function userIDExists($userID)
    {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE userID = ?");
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }



    public function login($userID, $password)
    {
        $stmt = $this->conn->prepare("SELECT id, lastname, firstname, middlename, userID , password FROM users WHERE userID = ?");
        if (!$stmt)
            return "Something went wrong!";

        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $stmt->store_result();

        $id = null;
        $lastname = "";
        $firstname = "";
        $middlename = null;
        $userID = "";
        $hashed_password = "";

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $lastname, $firstname, $middlename, $userID, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['userID'] = $userID;
                $_SESSION['full_name'] = (!empty($lastname) && !empty($firstname)) ? "$firstname, $firstname!" : "Unknown User";
                $_SESSION['lastName'] = $lastname;
                $_SESSION['firstName'] = $firstname;
                $_SESSION['middleName'] = $middlename;

                header("Location: ./User/home.php");
                // echo "User ID: " . $_SESSION['user_id'] . "<br>";
                // echo "Username: " . $_SESSION['username'] . "<br>";
                // echo "Full Name: " . $_SESSION['full_name'] . "<br>";
                exit(); // Stop execution to see debug output
            } else {
                return "Invalid userID or password!";
            }
        } else {
            return "Invalid userID or password!";
        }
    }
    public function usernameExists($username) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
    
    public function addUser($username, $name, $hashedPassword, $role) {
        $stmt = $this->conn->prepare("INSERT INTO users (username, name, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $name, $hashedPassword, $role);
        return $stmt->execute();
    }
    public function getAllUsers(): array
{
    $users = [];
    $sql = "SELECT id, lastname, firstname, middlename, birthday, userID, created_at FROM users ORDER BY created_at DESC";
    $result = $this->conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    return $users;
}

    public function lastInsertId(): int
{
    return $this->conn->insert_id;
}

    
}


?>