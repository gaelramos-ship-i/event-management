<?php

try {
    $db = new PDO(
        'mysql:host=localhost;dbname=events;charset=utf8',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
        ]
    );
} catch (Exception $e) {
    echo "Error: " .$e->getMessage();
    exit();
}