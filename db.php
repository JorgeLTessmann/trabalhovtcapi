<?php
$host = 'db';
$username = 'satoshi';
$password = 'S3nh-a';
$dbname = 'btc_prices';

try {
    // Primeiro, conectar sem especificar o banco de dados
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar o banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    
    // Agora conectar ao banco de dados específico
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabela se não existir
    $pdo->exec("CREATE TABLE IF NOT EXISTS prices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        price DECIMAL(10, 2) NOT NULL
    )");
    
    echo "Database and table ready!";
} catch (PDOException $e) {
    die("Database connection failed!!: " . $e->getMessage());
}
?>