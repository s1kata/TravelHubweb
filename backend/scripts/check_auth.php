<?php
require_once __DIR__ . '/../config/config.php';

session_start();

if (!defined('REMEMBER_TOKEN_SALT')) {
    define('REMEMBER_TOKEN_SALT', getenv('AUTH_REMEMBER_SALT') ?: 'travelhub-remember-token');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if (isset($_COOKIE['user_id'], $_COOKIE['user_token']) && $pdo) {
        $userId = (int) $_COOKIE['user_id'];
        $token = $_COOKIE['user_token'];

        try {
            $stmt = $pdo->prepare('SELECT id, name, email, password, role, status FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();

            if ($user && $user['status'] === 'active') {
                $expectedToken = hash('sha256', $user['email'] . $user['password'] . REMEMBER_TOKEN_SALT);
                if (hash_equals($expectedToken, $token)) {
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['logged_in'] = true;
                }
            }
        } catch (PDOException $e) {
            error_log('[auth] remember me failed: ' . $e->getMessage());
        }
    }
}

header('Content-Type: application/json');

echo json_encode([
    'authenticated' => isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true,
    'user' => isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true ? [
        'name' => $_SESSION['user_name'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
    ] : null,
]);
?>