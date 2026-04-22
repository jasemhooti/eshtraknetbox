<?php
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: install.php');
    exit;
}
require_once __DIR__ . '/config.php';

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;color:red;padding:20px">خطا در اتصال به دیتابیس: ' . htmlspecialchars($e->getMessage()) . '</div>');
        }
    }
    return $pdo;
}

function formatPrice($amount) {
    return number_format($amount) . ' تومان';
}

function formatVolume($mb) {
    if ($mb >= 1024) {
        $gb = $mb / 1024;
        return ($gb == floor($gb) ? (int)$gb : $gb) . ' گیگابایت';
    }
    return $mb . ' مگابایت';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}
