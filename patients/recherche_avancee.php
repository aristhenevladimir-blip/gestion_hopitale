<?php
require_once __DIR__ . "/../config/base.php";
$query = $_GET;
unset($query["page"]);
header("Location: " . url("/patients/rechercher.php") . ($query ? "?" . http_build_query($query) : ""));
exit();
