<?php
const DB_HOST = "localhost",
    DB_NAME = "ssis",
    DB_USER = "root",
    DB_PASS = "";

session_set_cookie_params(["httponly" => true, "samesite" => "Lax"]);
session_start();
if (empty($_SESSION["t"])) {
    $_SESSION["t"] = bin2hex(random_bytes(16));
}

$pdo = new PDO(
    "mysql:host=127.0.0.1;port=3307;dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
);

function q($sql, $args = [])
{
    global $pdo;
    $s = $pdo->prepare($sql);
    $s->execute($args);
    return $s;
}
function e($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8");
}
