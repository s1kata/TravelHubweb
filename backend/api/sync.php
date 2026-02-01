<?php
/**
 * API для прямой синхронизации данных из мобильного приложения
 * Работает напрямую с базой данных сайта без промежуточного сервера
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/config.php';

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка подключения к базе данных']);
    exit;
}

// Получение метода и действия
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($action) {
            case 'sync_bookings':
                syncBookings($pdo, $input);
                break;
                
            case 'sync_users':
                syncUsers($pdo, $input);
                break;
                
            case 'get_bookings':
                getBookings($pdo, $input);
                break;
                
            case 'get_users':
                getUsers($pdo);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
        }
    } elseif ($method === 'GET') {
        switch ($action) {
            case 'stats':
                getStats($pdo);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Синхронизация бронирований
 */
function syncBookings($pdo, $data) {
    if (!isset($data['bookings']) || !is_array($data['bookings'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Ожидается массив бронирований']);
        return;
    }
    
    $synced = 0;
    $errors = [];
    
    foreach ($data['bookings'] as $booking) {
        try {
            // Проверяем существование пользователя
            $userId = findOrCreateUser($pdo, $booking);
            
            if (!$userId) {
                $errors[] = "Не удалось найти/создать пользователя для бронирования {$booking['id']}";
                continue;
            }
            
            // Проверяем, существует ли уже такое бронирование
            $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ?");
            $stmt->execute([$booking['id']]);
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Обновляем существующее бронирование
                $stmt = $pdo->prepare("
                    UPDATE bookings SET
                        user_id = ?,
                        tour_title = ?,
                        hotel_name = ?,
                        destination = ?,
                        stars = ?,
                        nights = ?,
                        price = ?,
                        currency = ?,
                        meals = ?,
                        departure_date = ?,
                        return_date = ?,
                        status = ?,
                        notes = ?,
                        source = 'app'
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $userId,
                    $booking['tourTitle'] ?? '',
                    $booking['hotelName'] ?? '',
                    $booking['destination'] ?? '',
                    $booking['stars'] ?? null,
                    $booking['nights'] ?? null,
                    $booking['totalPrice'] ?? 0,
                    $booking['currency'] ?? 'RUB',
                    $booking['meals'] ?? null,
                    $booking['checkIn'] ?? null,
                    $booking['checkOut'] ?? null,
                    $booking['status'] ?? 'pending',
                    $booking['notes'] ?? null,
                    $booking['id']
                ]);
            } else {
                // Создаем новое бронирование
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (
                        id, user_id, tour_title, hotel_name, destination,
                        stars, nights, price, currency, meals,
                        departure_date, return_date, status, booking_date, notes, source
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, 'app')
                ");
                
                $stmt->execute([
                    $booking['id'],
                    $userId,
                    $booking['tourTitle'] ?? '',
                    $booking['hotelName'] ?? '',
                    $booking['destination'] ?? '',
                    $booking['stars'] ?? null,
                    $booking['nights'] ?? null,
                    $booking['totalPrice'] ?? 0,
                    $booking['currency'] ?? 'RUB',
                    $booking['meals'] ?? null,
                    $booking['checkIn'] ?? null,
                    $booking['checkOut'] ?? null,
                    $booking['status'] ?? 'pending',
                    $booking['notes'] ?? null
                ]);
            }
            
            $synced++;
        } catch (Exception $e) {
            $errors[] = "Ошибка синхронизации бронирования {$booking['id']}: " . $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => true,
        'synced' => $synced,
        'total' => count($data['bookings']),
        'errors' => $errors
    ]);
}

/**
 * Синхронизация пользователей
 */
function syncUsers($pdo, $data) {
    if (!isset($data['users']) || !is_array($data['users'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Ожидается массив пользователей']);
        return;
    }
    
    $synced = 0;
    $errors = [];
    
    foreach ($data['users'] as $user) {
        try {
            // Проверяем, существует ли пользователь по email или phone
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$user['email'] ?? '', $user['phone'] ?? '']);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Обновляем существующего пользователя
                $stmt = $pdo->prepare("
                    UPDATE users SET
                        name = ?,
                        email = ?,
                        phone = ?,
                        password = ?,
                        last_login = CURRENT_TIMESTAMP,
                        source = 'app'
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $user['name'] ?? '',
                    $user['email'] ?? '',
                    $user['phone'] ?? '',
                    $user['password'] ?? '',
                    $existing['id']
                ]);
            } else {
                // Создаем нового пользователя
                $stmt = $pdo->prepare("
                    INSERT INTO users (
                        name, email, password, phone, role, reg_date, status, source
                    ) VALUES (?, ?, ?, ?, 'user', CURRENT_TIMESTAMP, 'active', 'app')
                ");
                
                $stmt->execute([
                    $user['name'] ?? '',
                    $user['email'] ?? '',
                    $user['password'] ?? '',
                    $user['phone'] ?? ''
                ]);
            }
            
            $synced++;
        } catch (Exception $e) {
            $errors[] = "Ошибка синхронизации пользователя {$user['email']}: " . $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => true,
        'synced' => $synced,
        'total' => count($data['users']),
        'errors' => $errors
    ]);
}

/**
 * Поиск или создание пользователя
 */
function findOrCreateUser($pdo, $booking) {
    // Пытаемся найти пользователя по userId из приложения
    if (isset($booking['userId'])) {
        // Ищем по phone или email, если userId не совпадает с id в базе сайта
        // Для этого нужно будет хранить связь между userId приложения и id сайта
        // Пока просто ищем по phone
    }
    
    // Если есть phone в бронировании, ищем по нему
    if (isset($booking['userPhone'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$booking['userPhone']]);
        $user = $stmt->fetch();
        if ($user) {
            return $user['id'];
        }
    }
    
    // Если пользователь не найден, возвращаем null
    // В реальном сценарии можно создать пользователя или вернуть ошибку
    return null;
}

/**
 * Получение бронирований
 */
function getBookings($pdo, $data) {
    $userId = $data['userId'] ?? null;
    
    if ($userId) {
        // Получаем бронирования конкретного пользователя
        $stmt = $pdo->prepare("
            SELECT * FROM bookings 
            WHERE user_id = ? 
            ORDER BY booking_date DESC
        ");
        $stmt->execute([$userId]);
    } else {
        // Получаем все бронирования
        $stmt = $pdo->prepare("
            SELECT * FROM bookings 
            ORDER BY booking_date DESC
        ");
        $stmt->execute();
    }
    
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'count' => count($bookings)
    ]);
}

/**
 * Получение пользователей
 */
function getUsers($pdo) {
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, reg_date, status FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'count' => count($users)
    ]);
}

/**
 * Получение статистики
 */
function getStats($pdo) {
    $stats = [];
    
    // Статистика пользователей
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['users'] = $stmt->fetch()['count'];
    
    // Статистика бронирований
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $stats['bookings'] = $stmt->fetch()['count'];
    
    // Статистика подписок
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM newsletter_subscriptions WHERE is_active = 1");
    $stats['subscriptions'] = $stmt->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

