<?php
// Запускаем сессию
session_start();

// Очищаем все данные сессии
$_SESSION = array();

// Удаляем cookies запоминания
setcookie('user_id', '', time() - 3600, '/', 'localhost');
setcookie('user_token', '', time() - 3600, '/', 'localhost');

// Уничтожаем сессию
session_destroy();

// Очищаем localStorage и перенаправляем
echo "<script>
    localStorage.removeItem('user_logged_in');
    localStorage.removeItem('user_name');
    localStorage.removeItem('user_email');
    window.location.href = '/index.php';
</script>";
exit();
?>