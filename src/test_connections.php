<?php
try {
    $pdo = new PDO('mysql:host=db;dbname=laravel', 'root', 'root');
    echo "Connected successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
