<?php
session_start();
require_once __DIR__ . '/db.php';

$page = $_GET['page'] ?? 'home';
$allowed_pages = ['home','login','logout','plans','order','subscriptions','download','about','admin','admin_login'];

if (!in_array($page, $allowed_pages)) $page = 'home';

// Logout
if ($page === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Page routing
ob_start();
switch ($page) {
    case 'home':      include 'pages/home.php'; break;
    case 'login':     include 'pages/login.php'; break;
    case 'plans':     include 'pages/plans.php'; break;
    case 'order':     include 'pages/order.php'; break;
    case 'subscriptions': include 'pages/subscriptions.php'; break;
    case 'download':  include 'pages/download.php'; break;
    case 'about':     include 'pages/about.php'; break;
    case 'admin':     include 'pages/admin.php'; break;
    case 'admin_login': include 'pages/admin_login.php'; break;
}
$content = ob_get_clean();

include 'layout.php';
