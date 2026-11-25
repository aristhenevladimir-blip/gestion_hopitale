<?php
require_once __DIR__ . "/../config/db.php";

function ensure_activity_log_table($pdo) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS journal_activite (
            id_journal INT AUTO_INCREMENT PRIMARY KEY, id_utilisateur INT NULL, username VARCHAR(50) NULL,
            action VARCHAR(50) NOT NULL, details VARCHAR(255) NULL, date_action DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("ALTER TABLE utilisateur ADD COLUMN derniere_connexion DATETIME NULL");
    } catch (Exception $e) {}
}

function log_activity($pdo, $action, $details = "") {
    try {
        ensure_activity_log_table($pdo);
        $stmt = $pdo->prepare("INSERT INTO journal_activite (id_utilisateur, username, action, details) VALUES (?,?,?,?)");
        $stmt->execute([$_SESSION["user"]["id"] ?? null, $_SESSION["user"]["username"] ?? null, $action, $details]);
    } catch (Exception $e) {}
}
