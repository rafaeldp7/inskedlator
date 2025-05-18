<?php
require_once 'config.php';

function createTables($conn)
{
    $tables = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lastname VARCHAR(50) NOT NULL,
            firstname VARCHAR(50) NOT NULL,
            middlename VARCHAR(50) NOT NULL,
            birthday DATE NOT NULL,
            userID VARCHAR(100) UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            
        )",


        "admins" => "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "admin" => "INSERT IGNORE INTO admins (name, username, password) 
            VALUES ('Admin', 'qwer', '" . password_hash('qwer', PASSWORD_BCRYPT) . "')",


        "schedules" => "CREATE TABLE IF NOT EXISTS schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            shift ENUM('Opening','Afternoon','Night') NOT NULL,
            day ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
            time VARCHAR(30) NOT NULL,
            status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",

        "announcements" => "CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        // "chat" => "CREATE TABLE IF NOT EXISTS chat (
        //     id INT AUTO_INCREMENT PRIMARY KEY,
        //     sender_id INT NOT NULL,
        //     sender_type ENUM('user', 'admin') NOT NULL,
        //     receiver_id INT NOT NULL,
        //     receiver_type ENUM('user', 'admin') NOT NULL,
        //     message TEXT NOT NULL,
        //     sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //     INDEX (sender_id, sender_type),
        //     INDEX (receiver_id, receiver_type),
        //     FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        // )",
        "chat" => "CREATE TABLE IF NOT EXISTS chat (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            sender_type ENUM('user', 'admin') NOT NULL,
            receiver_id INT NOT NULL,
            receiver_type ENUM('user', 'admin') NOT NULL,
            message TEXT NOT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

        )",



        "profile_pictures" => "CREATE TABLE IF NOT EXISTS profile_pictures (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            file_path VARCHAR(255) NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "events" => "CREATE TABLE IF NOT EXISTS events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",

        "deleted_data" => "CREATE TABLE IF NOT EXISTS deleted_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            table_name VARCHAR(50) NOT NULL,
            deleted_record TEXT NOT NULL,
            deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach ($tables as $name => $sql) {
        if (!$conn->query($sql)) {
            error_log("Table creation failed for $name: " . $conn->error);
        }
    }
}


createTables($conn);
?>