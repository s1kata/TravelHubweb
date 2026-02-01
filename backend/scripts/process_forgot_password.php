<?php
require_once __DIR__ . '/../config/config.php';

session_start();

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $errors['email'] = 'Пожалуйста, введите email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Пожалуйста, введите корректный email.';
    }

    if (empty($errors)) {
        try {
            if (!$pdo) {
                $errors['general'] = 'База данных недоступна.';
            } else {
                $stmt = $pdo->prepare('SELECT id, name FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                if (!$user) {
                    $errors['email'] = 'Пользователь с таким email не найден.';
                } else {
                    // В реальном приложении здесь должна быть логика отправки email
                    // Пока просто перенаправляем с сообщением об успехе
                    header('Location: /frontend/window/forgot-password.php?success=1&email=' . urlencode($email));
                    exit;
                }
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Ошибка базы данных.';
            error_log('[forgot_password] Database error: ' . $e->getMessage());
        }
    }
}

if (!empty($errors)) {
    header('Location: /frontend/window/forgot-password.php?errors=' . urlencode(json_encode($errors)) . '&email=' . urlencode($email));
    exit;
}
?>