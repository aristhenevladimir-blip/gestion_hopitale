<?php
function consultation_columns($pdo) {
    try { return array_flip($pdo->query("SHOW COLUMNS FROM consultation")->fetchAll(PDO::FETCH_COLUMN) ?: []); }
    catch (Exception $e) { return []; }
}
