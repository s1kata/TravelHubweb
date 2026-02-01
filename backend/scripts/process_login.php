<?php
require_once __DIR__ . '/../config/config.php';

session_start();

if (!defined('REMEMBER_TOKEN_SALT')) {
    define('REMEMBER_TOKEN_SALT', getenv('AUTH_REMEMBER_SALT') ?: 'travelhub-remember-token');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '') {
        $errors['email'] = 'Пожалуйста, введите email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Пожалуйста, введите корректный email.';
    }

    if ($password === '') {
        $errors['password'] = 'Пожалуйста, введите пароль.';
    }

    if (empty($errors)) {
        try {
            if (!$pdo) {
                $errors['database'] = 'База данных недоступна.';
            } else {
                $stmt = $pdo->prepare('SELECT id, name, email, password, role, status FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                if (!$user) {
                    $errors['account_not_found'] = true;
                } elseif ($user['status'] !== 'active') {
                    $errors['login'] = 'Учетная запись заблокирована. Свяжитесь с менеджером.';
                } elseif (!password_verify($password, $user['password'])) {
                    $errors['login'] = 'Неверный email или пароль.';
                } else {
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['logged_in'] = true;

                    if (!empty($_POST['remember'])) {
                        $cookiePayload = [
                            'expires' => time() + (30 * 24 * 60 * 60),
                            'path' => '/',
                            'domain' => $_SERVER['HTTP_HOST'] ?? '',
                            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ];

                        setcookie('user_id', (string) $user['id'], $cookiePayload);
                        setcookie('user_token', hash('sha256', $user['email'] . $user['password'] . REMEMBER_TOKEN_SALT), $cookiePayload);
                    }

                    try {
                        // Используем CURRENT_TIMESTAMP для совместимости с SQLite и MySQL
                        $dbDriver = strtolower(getenv('DB_DRIVER') ?: 'sqlite');
                        $timestampExpr = ($dbDriver === 'mysql') ? 'NOW()' : "datetime('now')";
                        $pdo->prepare("UPDATE users SET last_login = $timestampExpr WHERE id = :id")->execute([':id' => $user['id']]);
                    } catch (PDOException $updateException) {
                        error_log('[login] unable to update last_login: ' . $updateException->getMessage());
                    }

                    $redirectPage = ($user['role'] === 'admin') ? '/frontend/window/dashboard.php' : '/index.php';
                    echo "<script>localStorage.setItem('user_logged_in','true');localStorage.setItem('user_name','" . addslashes($user['name']) . "');localStorage.setItem('user_email','" . addslashes($user['email']) . "');localStorage.setItem('user_role','" . addslashes($user['role']) . "');window.location.href='$redirectPage';</script>";
                    exit;
                }
            }
        } catch (PDOException $e) {
            $errors['database'] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

if (!empty($errors)) {
    header('Location: /frontend/window/login.php?errors=' . urlencode(json_encode($errors)));
    exit;
}
?>