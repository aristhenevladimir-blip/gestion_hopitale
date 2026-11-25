<?php
date_default_timezone_set("America/Port-au-Prince");

if (!defined("BASE_URL")) {
    $docRoot = isset($_SERVER["DOCUMENT_ROOT"]) ? str_replace("\\", "/", realpath($_SERVER["DOCUMENT_ROOT"])) : "";
    $appRoot = str_replace("\\", "/", realpath(__DIR__ . "/.."));
    $base = ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) ? substr($appRoot, strlen($docRoot)) : "";
    $base = "/" . trim((string) $base, "/");
    define("BASE_URL", $base === "/" ? "" : $base);
}

if (!function_exists("url")) {
    function url($path = "") { return BASE_URL . "/" . ltrim($path, "/"); }
}

if (!function_exists("start_app_session")) {
    function start_app_session() {
        if (session_status() === PHP_SESSION_NONE) {
            $secure = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
            session_set_cookie_params(["lifetime" => 0, "path" => BASE_URL !== "" ? BASE_URL : "/", "secure" => $secure, "httponly" => true, "samesite" => "Lax"]);
            ini_set("session.use_strict_mode", "1");
            session_start();
        }
    }
}
