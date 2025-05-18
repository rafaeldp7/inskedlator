<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "inskedlator";

// Create connection without selecting a database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the database exists
$db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");

if ($db_check->num_rows == 0) {
    // Database does not exist, create it
    if ($conn->query("CREATE DATABASE $database") === TRUE) {
        //echo "Database '$database' created successfully.<br>";
    } else {
        die("Error creating database: " . $conn->error);
    }
}

// Close the initial connection
$conn->close();

// Reconnect with the selected database
$conn = new mysqli($servername, $username, $password, $database);

// Check new connection
if ($conn->connect_error) {
    die("Connection failed after creating database: " . $conn->connect_error);
}

?>







<?php
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// // Database credentials
// $servername = "localhost";
// $username = "root";
// $password = "";
// $database = "inskedlator";

// try {
//     // Connect to MySQL server without selecting a database
//     $pdo = new PDO("mysql:host=$servername", $username, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//     // Check if database exists
//     $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
//     if ($stmt->rowCount() == 0) {
//         // Create database if it doesn't exist
//         $pdo->exec("CREATE DATABASE $database");
//     }

//     // Now connect to the database
//     $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Database connection failed: " . $e->getMessage());
// }
?>
