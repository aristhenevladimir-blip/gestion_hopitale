<?php
$config = require __DIR__ . "/app.php";
$d = $config["db"];
$dsn = "mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}";
try {
    $pdo = new PDO($dsn, $d["user"], $d["pass"], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion a la base de donnees : " . $e->getMessage());
}
