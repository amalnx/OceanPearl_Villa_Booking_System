<?php

// ==========================================
// OCEAN PEARL VILLA
// Database Configuration
// IT1208 - Web Technologies CA2
// ==========================================

$host = "localhost";
$dbname = "oceanpearl_db";
$username = "root";
$password = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Enable PDO error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch database results as associative arrays
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Database Error: " . $e->getMessage());

}

?>