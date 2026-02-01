<?php
/**
 * Управление фотографиями офисов
 * Загрузка фотографий в папки офисов
 */

// Подключаем конфигурационный файл
$configPath = realpath(__DIR__ . '/../config/config.php');
if (!$configPath || !file_exists($configPath)) {
    die('Configuration file not found: ' . __DIR__ . '/../config/config.php');
}
require_once $configPath;

session_start();

// Debug database connection
if (isset($pdo)) {
    error_log('[Manage Office Photos] Database connection successful');
} else {
    error_log('[Manage Office Photos] Database connection failed');
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'admin') {
    header('Location: ../../frontend/window/login.php');
    exit;
}

$message = '';
$messageType = '';

// Обработка удаления фото
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_photo' && isset($_POST['photo_path'])) {
        $photoPath = $_POST['photo_path'];
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $photoPath;

        if (file_exists($fullPath)) {
            if (unlink($fullPath)) {
                $message = 'Фото успешно удалено';
                $messageType = 'success';
                error_log('Deleted photo: ' . $fullPath);
            } else {
                $message = 'Ошибка при удалении фото';
                $messageType = 'error';
            }
        } else {
            $message = 'Фото не найдено';
            $messageType = 'error';
        }
    } elseif ($_POST['action'] === 'delete_office_photos' && isset($_POST['office_id'])) {
        $officeId = intval($_POST['office_id']);

        try {
            $stmt = $pdo->prepare("SELECT city, name FROM offices WHERE id = ?");
            $stmt->execute([$officeId]);
            $office = $stmt->fetch();

            if ($office) {
                $officeSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $office['name']));
                $officeDir = $_SERVER['DOCUMENT_ROOT'] . '/img/offices/' . $office['city'] . '/' . $officeSlug . '/';

                if (is_dir($officeDir)) {
                    $files = scandir($officeDir);
                    $deletedCount = 0;

                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..' || $file === '.gitkeep') continue;

                        $filePath = $officeDir . $file;
                        if (is_file($filePath) && unlink($filePath)) {
                            $deletedCount++;
                        }
                    }

                    $message = "Удалено фото офиса: {$deletedCount}";
                    $messageType = 'success';
                    error_log("Deleted {$deletedCount} photos from office: {$office['name']}");
                } else {
                    $message = 'Папка офиса не найдена';
                    $messageType = 'error';
                }
            } else {
                $message = 'Офис не найден';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Ошибка базы данных: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Обработка добавления фото в галерею офиса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_photos_to_gallery') {
    $officeId = intval($_POST['office_id'] ?? 0);
    $selectedPhotos = $_POST['selected_photos'] ?? [];

    if ($officeId > 0 && !empty($selectedPhotos)) {
        $addedCount = 0;
        $errors = 0;

        try {
            $stmt = $pdo->prepare("
                INSERT INTO office_photos (office_id, image_url, title)
                VALUES (?, ?, ?)
            ");

            foreach ($selectedPhotos as $photoUrl) {
                $title = pathinfo($photoUrl, PATHINFO_FILENAME);
                try {
                    $stmt->execute([$officeId, $photoUrl, $title]);
                    $addedCount++;
                } catch (PDOException $e) {
                    $errors++;
                    error_log('[Add Photos to Gallery] Error adding photo: ' . $e->getMessage());
                }
            }

            if ($addedCount > 0) {
                $message = "Успешно добавлено фото в галерею: {$addedCount}";
                if ($errors > 0) {
                    $message .= " (ошибок: {$errors})";
                }
                $message .= '! Страница обновится через 2 секунды.';
                $messageType = 'success';
                echo '<meta http-equiv="refresh" content="2">';
            } else {
                $message = 'Не удалось добавить ни одного фото в галерею';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Ошибка при работе с базой данных: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Выберите офис и фотографии для добавления';
        $messageType = 'error';
    }
}

// Обработка загрузки фото офисов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_office_photo') {
    error_log('Upload photo request received');
    $officeId = intval($_POST['office_id'] ?? 0);
    error_log('Office ID: ' . $officeId);

    if ($officeId > 0 && isset($_FILES['photo'])) {
        // Получаем информацию об офисе
        try {
            $stmt = $pdo->prepare("SELECT city, name FROM offices WHERE id = ?");
            $stmt->execute([$officeId]);
            $office = $stmt->fetch();

            if ($office) {
                // Создаем папку для офиса: /img/offices/{city}/{office-slug}/
                $officeSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $office['name']));
                $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/img/offices/';
                $cityDir = $baseDir . $office['city'] . '/';
                $uploadDir = $cityDir . $officeSlug . '/';

                error_log('Base directory: ' . $baseDir);
                error_log('City directory: ' . $cityDir);
                error_log('Upload directory: ' . $uploadDir);
                error_log('Office name: ' . $office['name'] . ', slug: ' . $officeSlug);

                // Создаем базовую папку, если она не существует
                if (!is_dir($baseDir)) {
                    $created = mkdir($baseDir, 0755, true);
                    error_log('Created base directory: ' . $baseDir . ' - ' . ($created ? 'success' : 'failed'));
                }

                // Создаем папку города, если она не существует
                if (!is_dir($cityDir)) {
                    $created = mkdir($cityDir, 0755, true);
                    error_log('Created city directory: ' . $cityDir . ' - ' . ($created ? 'success' : 'failed'));
                }

                // Создаем папку офиса, если она не существует
                if (!is_dir($uploadDir)) {
                    $created = mkdir($uploadDir, 0755, true);
                    if (!$created) {
                        $message = 'Не удалось создать папку для фото офиса: ' . $uploadDir;
                        $messageType = 'error';
                        error_log('Failed to create office directory: ' . $uploadDir);
                    } else {
                        error_log('Created office directory: ' . $uploadDir . ' for office: ' . $office['name'] . ' (slug: ' . $officeSlug . ')');
                    }
                } else {
                    error_log('Office directory already exists: ' . $uploadDir . ' for office: ' . $office['name'] . ' (slug: ' . $officeSlug . ')');
                }

                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $uploadedCount = 0;
                $errors = 0;

                // Обрабатываем массив файлов
                if (is_array($_FILES['photo']['name'])) {
                    $fileCount = count($_FILES['photo']['name']);

                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($_FILES['photo']['error'][$i] === UPLOAD_ERR_OK) {
                            $fileName = basename($_FILES['photo']['name'][$i]);
                            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                            if (in_array($fileExt, $allowedExts)) {
                                // Создаем уникальное имя файла
                                $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $fileName);
                                $targetPath = $uploadDir . $newFileName;

                                if (move_uploaded_file($_FILES['photo']['tmp_name'][$i], $targetPath)) {
                                    $uploadedCount++;
                                    error_log('Successfully uploaded file: ' . $targetPath);
                                } else {
                                    $errors++;
                                    error_log('Failed to upload file to: ' . $targetPath);
                                }
                            } else {
                                $errors++;
                            }
                        } else {
                            $errors++;
                        }
                    }
                } else {
                    // Обработка одиночного файла
                    if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $fileName = basename($_FILES['photo']['name']);
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        if (in_array($fileExt, $allowedExts)) {
                            $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $fileName);
                            $targetPath = $uploadDir . $newFileName;

                            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                                $uploadedCount++;
                            } else {
                                $errors++;
                            }
                        } else {
                            $errors++;
                        }
                    } else {
                        $errors++;
                    }
                }

                if ($uploadedCount > 0) {
                    $message = "Успешно загружено фото: {$uploadedCount}";
                    if ($errors > 0) {
                        $message .= " (ошибок: {$errors})";
                    }
                    $message .= '! Страница обновится через 2 секунды.';
                    $messageType = 'success';
                    echo '<meta http-equiv="refresh" content="2">';
                } else {
                    $message = 'Не удалось загрузить ни одного фото. Проверьте формат файлов и права доступа.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Не удалось найти выбранный офис';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Ошибка при работе с базой данных: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Выберите офис и файлы для загрузки';
        $messageType = 'error';
    }
}

// Функция для сканирования папок с фотографиями
function scanOfficePhotos($baseDir) {
    $photos = [];
    $cities = ['samara', 'moscow', 'tolyatti'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    foreach ($cities as $city) {
        $cityDir = $baseDir . $city . '/';
        error_log('Scanning city directory: ' . $cityDir);
        if (is_dir($cityDir)) {
            // Сканируем подпапки офисов
            $officeDirs = scandir($cityDir);
            error_log('Found office directories in ' . $city . ': ' . implode(', ', array_diff($officeDirs, ['.', '..', '.gitkeep'])));
            foreach ($officeDirs as $officeDir) {
                if ($officeDir === '.' || $officeDir === '..' || $officeDir === '.gitkeep') {
                    continue;
                }
                $officePath = $cityDir . $officeDir;
                if (is_dir($officePath)) {
                    error_log('Scanning office directory: ' . $officePath);
                    $files = scandir($officePath);
                    $imageFiles = array_filter($files, function($file) use ($officePath, $allowedExtensions) {
                        if ($file === '.' || $file === '..' || $file === '.gitkeep') return false;
                        $filePath = $officePath . '/' . $file;
                        if (!is_file($filePath)) return false;
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        return in_array($ext, $allowedExtensions);
                    });
                    error_log('Found image files in ' . $officeDir . ': ' . implode(', ', $imageFiles));

                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                            continue;
                        }
                        $filePath = $officePath . '/' . $file;
                        if (is_file($filePath)) {
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (in_array($ext, $allowedExtensions)) {
                                $relativePath = '/img/offices/' . $city . '/' . $officeDir . '/' . $file;
                                $photos[] = [
                                    'path' => $relativePath,
                                    'filename' => $file,
                                    'city' => $city,
                                    'office_slug' => $officeDir,
                                    'full_path' => $filePath
                                ];
                                error_log('Added photo: ' . $relativePath);
                            }
                        }
                    }
                }
            }
        } else {
            error_log('City directory does not exist: ' . $cityDir);
        }
    }

    return $photos;
}

// Проверяем и создаем базовую структуру папок
$baseDir = $_SERVER['DOCUMENT_ROOT'] . '/img/offices/';
$samaraDir = $baseDir . 'samara/';
$moscowDir = $baseDir . 'moscow/';

error_log('Checking directories...');
error_log('Base dir exists: ' . (is_dir($baseDir) ? 'yes' : 'no') . ' - ' . $baseDir);
error_log('Samara dir exists: ' . (is_dir($samaraDir) ? 'yes' : 'no') . ' - ' . $samaraDir);
error_log('Moscow dir exists: ' . (is_dir($moscowDir) ? 'yes' : 'no') . ' - ' . $moscowDir);

// Создаем базовую структуру
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0755, true);
    error_log('Created base directory: ' . $baseDir);
}
if (!is_dir($samaraDir)) {
    mkdir($samaraDir, 0755, true);
    error_log('Created samara directory: ' . $samaraDir);
}
if (!is_dir($moscowDir)) {
    mkdir($moscowDir, 0755, true);
    error_log('Created moscow directory: ' . $moscowDir);
}

// Сканируем доступные фотографии
$availablePhotos = scanOfficePhotos($baseDir);
error_log('Scanned ' . count($availablePhotos) . ' photos from directory: ' . $baseDir);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление фотографиями офисов - Travel Hub</title>
    <link rel="icon" type="image/svg+xml" href="/frontend/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #f4f9ff;
            --bg-surface: #ffffff;
            --accent-primary: #3ba3ff;
            --text-primary: #1f2a44;
        }
        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(180deg, #f8fbff 0%, #eff5ff 45%, #fdfdff 100%);
            color: var(--text-primary);
            margin: 0;
            padding: 0;
        }
        .heading-font { font-family: 'Montserrat', sans-serif; }
        .surface-card {
            background: var(--bg-surface);
            border-radius: 20px;
            border: 1px solid rgba(59, 163, 255, 0.18);
            box-shadow: 0 22px 48px rgba(59, 163, 255, 0.18);
        }
    </style>
</head>
<body class="min-h-screen">
    <header class="backdrop-blur-md bg-white/90 border-b border-sky-100 sticky top-0 z-40 shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 py-3 sm:py-5">
            <div class="flex items-center justify-between">
                <a href="admin.php" class="flex items-center gap-2 sm:gap-3">
                    <span class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gradient-to-r from-sky-300 via-sky-400 to-sky-500 flex items-center justify-center shadow-lg">
                        <i class="fas fa-plane text-white text-xs sm:text-base"></i>
                    </span>
                    <span class="heading-font text-lg sm:text-2xl font-bold text-sky-600">Управление фотографиями офисов</span>
                </a>
                <a href="admin.php" class="px-4 py-2 rounded-full bg-gradient-to-r from-sky-300 via-sky-400 to-sky-500 text-white shadow-md hover:shadow-lg transition">Назад</a>
            </div>
        </div>
    </header>

    <main class="py-8 sm:py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $messageType === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Загрузка фото офисов -->
            <div class="surface-card p-6 mb-6">
                <h2 class="heading-font text-2xl font-bold text-slate-900 mb-4">Загрузить фото офисов</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_office_photo">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Офис *</label>
                            <select name="office_id" required class="w-full px-4 py-2 border border-sky-200 rounded-xl focus:ring-2 focus:ring-sky-500">
                                <option value="">Выберите офис</option>
                                <?php
                                $offices = [];
                                try {
                                    $stmt = $pdo->query("SELECT id, city, name, address FROM offices ORDER BY city, name");
                                    $offices = $stmt->fetchAll();
                                } catch (PDOException $e) {
                                    error_log('[Manage Office Photos] Error loading offices: ' . $e->getMessage());
                                }
                                foreach ($offices as $office): ?>
                                    <option value="<?php echo $office['id']; ?>"><?php echo htmlspecialchars($office['city'] . ' - ' . $office['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Фото *</label>
                            <div id="office-drop-zone" class="relative border-2 border-dashed border-sky-300 rounded-xl p-6 text-center hover:border-sky-400 transition-colors cursor-pointer bg-sky-50/50">
                                <input type="file" name="photo[]" accept="image/*" multiple required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="office-photo-input">
                                <div class="text-sky-600">
                                    <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
                                    <p class="font-medium">Перетащите фото сюда или нажмите для выбора</p>
                                    <p class="text-sm text-sky-500 mt-1">Можно выбрать несколько файлов одновременно</p>
                                </div>
                            </div>
                            <div id="office-file-list" class="mt-2 space-y-1"></div>
                        </div>
                    </div>

                    <button type="submit" id="upload-btn" class="w-full px-6 py-3 bg-gradient-to-r from-purple-300 via-purple-400 to-purple-500 text-white rounded-xl font-semibold shadow-md hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-upload mr-2"></i>
                        <span id="upload-text">Загрузить фото</span>
                        <div id="upload-spinner" class="hidden inline-block ml-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </button>
                </form>

                <div class="mt-4 text-sm text-slate-600">
                    <p><strong>Поддерживаемые форматы:</strong> JPG, PNG, GIF, WebP</p>
                    <p><strong>Рекомендуемый размер:</strong> Фото офисов для галереи</p>
                    <p><strong>Множественная загрузка:</strong> Можно выбрать и загрузить несколько фото одновременно</p>
                </div>
            </div>

            <!-- Выбор загруженных фотографий для галереи офиса -->
            <div class="surface-card p-6 mb-6">
                <h2 class="heading-font text-2xl font-bold text-slate-900 mb-4">Выбрать загруженные фотографии для галереи офиса</h2>

                <?php if (empty($availablePhotos)): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                        <p class="text-yellow-800">Нет загруженных фотографий. Используйте форму выше для загрузки фото офисов.</p>
                    </div>
                <?php else: ?>
                    <!-- Выбор офиса -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Выберите офис для добавления фото *</label>
                        <select id="selectedOffice" class="w-full px-4 py-2 border border-sky-200 rounded-xl focus:ring-2 focus:ring-sky-500">
                            <option value="">Выберите офис</option>
                            <?php
                            // Создаем таблицу offices, если её нет
                            try {
                                $pdo->exec("CREATE TABLE IF NOT EXISTS offices (
                                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                                    name TEXT NOT NULL,
                                    city TEXT NOT NULL,
                                    address TEXT,
                                    phone TEXT,
                                    email TEXT,
                                    working_hours TEXT,
                                    description TEXT,
                                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                                )");

                                // Проверяем и добавляем офис Anex Tour в Москве, если его нет
                                $stmt = $pdo->prepare("SELECT id FROM offices WHERE name = ? AND city = ?");
                                $stmt->execute(['Anex Tour', 'moscow']);
                                $existingOffice = $stmt->fetch();

                                if (!$existingOffice) {
                                    $stmt = $pdo->prepare("INSERT INTO offices (name, city, address, phone, email, working_hours, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                    $stmt->execute([
                                        'Anex Tour',
                                        'moscow',
                                        'г. Москва, Первомайская ул., 42, этаж 1',
                                        '+7 (499) 322-02-89',
                                        'moscow@travelhub63.ru',
                                        'Пн-Пт: 9:00 - 21:00, Сб-Вс: 10:00 - 18:00',
                                        'Надежный туроператор Anex Tour в Москве. Специализируемся на организации отдыха для всей семьи.'
                                    ]);
                                    error_log('[Manage Office Photos] Added Anex Tour office in Moscow to database');
                                }

                                // Также добавляем другие офисы на основе существующих папок
                                $existingOffices = [
                                    ['Coral Travel', 'samara', 'г. Самара, ул. Ленина, 1', '+7 (846) 123-45-67', 'samara@travelhub63.ru', 'Пн-Пт: 9:00 - 18:00, Сб: 10:00 - 16:00', 'Туроператор Coral Travel в Самаре'],
                                    ['Coral Elite Service', 'moscow', 'г. Москва, ул. Тверская, 10', '+7 (495) 987-65-43', 'elite@travelhub63.ru', 'Пн-Пт: 10:00 - 20:00, Сб-Вс: 11:00 - 17:00', 'Элитный сервис Coral Elite Service в Москве']
                                ];

                                foreach ($existingOffices as $officeData) {
                                    $stmt = $pdo->prepare("SELECT id FROM offices WHERE name = ? AND city = ?");
                                    $stmt->execute([$officeData[0], $officeData[1]]);
                                    $exists = $stmt->fetch();

                                    if (!$exists) {
                                        $stmt = $pdo->prepare("INSERT INTO offices (name, city, address, phone, email, working_hours, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                        $stmt->execute($officeData);
                                        error_log('[Manage Office Photos] Added office: ' . $officeData[0] . ' in ' . $officeData[1]);
                                    }
                                }
                            } catch (PDOException $e) {
                                error_log('[Manage Office Photos] Error creating/checking offices table: ' . $e->getMessage());
                            }
                            
                            $offices = [];
                            try {
                                $stmt = $pdo->query("SELECT id, city, name, address FROM offices ORDER BY city, name");
                                $offices = $stmt->fetchAll();
                                error_log('[Manage Office Photos] Found ' . count($offices) . ' offices in database');
                                foreach ($offices as $office) {
                                    error_log('[Manage Office Photos] Office: ' . $office['city'] . ' - ' . $office['name']);
                                }
                            } catch (PDOException $e) {
                                error_log('[Manage Office Photos] Error loading offices: ' . $e->getMessage());
                            }
                            foreach ($offices as $office): ?>
                                <option value="<?php echo $office['id']; ?>"><?php echo htmlspecialchars($office['city'] . ' - ' . $office['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <p class="text-sm text-slate-600">Найдено фотографий: <?php echo count($availablePhotos); ?></p>
                        <p class="text-xs text-slate-500">Выберите фотографии и нажмите "Добавить выбранные фотографии в галерею"</p>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="flex gap-2 mb-4">
                        <button onclick="selectAllPhotos()" class="px-4 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition">
                            <i class="fas fa-check-square mr-1"></i>Выбрать все
                        </button>
                        <button onclick="deselectAllPhotos()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                            <i class="fas fa-square mr-1"></i>Снять выбор
                        </button>
                        <button onclick="addSelectedPhotosToGallery()" class="px-6 py-2 bg-gradient-to-r from-green-300 via-green-400 to-green-500 text-white rounded-xl font-semibold shadow-md hover:shadow-lg transition">
                            <i class="fas fa-plus-circle mr-2"></i>Добавить выбранные фотографии в галерею
                        </button>
                    </div>

                    <?php
                    $officeGroups = [];
                    foreach ($availablePhotos as $photo) {
                        $officeKey = $photo['city'] . ' - ' . $photo['office_slug'];
                        $officeGroups[$officeKey][] = $photo;
                    }
                    ?>

                    <?php foreach ($officeGroups as $officeName => $officePhotos): ?>
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-slate-900"><?php echo ucfirst($officeName); ?> (<?php echo count($officePhotos); ?> фото)</h3>
                                <?php
                                // Получаем ID офиса для кнопки удаления всех фото
                                $officeParts = explode(' - ', $officeName);
                                if (count($officeParts) >= 2) {
                                    $officeCity = $officeParts[0];
                                    $officeSlug = $officeParts[1];

                                    // Находим ID офиса по названию
                                    $officeId = null;
                                    try {
                                        $stmt = $pdo->prepare("SELECT id FROM offices WHERE city = ? AND LOWER(REPLACE(name, ' ', '-')) = ?");
                                        $stmt->execute([$officeCity, $officeSlug]);
                                        $officeData = $stmt->fetch();
                                        if ($officeData) {
                                            $officeId = $officeData['id'];
                                        }
                                    } catch (PDOException $e) {
                                        error_log('Error finding office ID: ' . $e->getMessage());
                                    }

                                    if ($officeId && count($officePhotos) > 0): ?>
                                        <button onclick="deleteAllOfficePhotos(<?php echo $officeId; ?>, '<?php echo htmlspecialchars($officeName); ?>')"
                                                class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition">
                                            <i class="fas fa-trash mr-1"></i>Удалить все
                                        </button>
                                    <?php endif;
                                }
                                ?>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4 bg-slate-50 rounded-xl">
                                <?php foreach ($officePhotos as $photo): ?>
                                    <div class="relative group">
                                        <label class="block cursor-pointer">
                                            <input type="checkbox" name="selected_photos[]" value="<?php echo htmlspecialchars($photo['path']); ?>" class="photo-checkbox absolute top-2 left-2 z-10 w-4 h-4">
                                            <div class="relative overflow-hidden rounded-lg border-2 border-sky-200 group-hover:border-sky-400 transition">
                                                <img src="<?php echo htmlspecialchars($photo['path']); ?>"
                                                     alt="<?php echo htmlspecialchars($photo['filename']); ?>"
                                                     class="w-full h-32 object-cover"
                                                     onerror="this.src='https://via.placeholder.com/150?text=Error'">
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition flex items-center justify-center">
                                                    <i class="fas fa-check-circle text-white text-xl opacity-0 group-hover:opacity-100 transition check-icon"></i>
                                                </div>
                                                <!-- Кнопка удаления отдельного фото -->
                                                <button onclick="deleteSinglePhoto('<?php echo htmlspecialchars($photo['path']); ?>', '<?php echo htmlspecialchars($photo['filename']); ?>')"
                                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition hover:bg-red-600"
                                                        title="Удалить фото">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-1 truncate" title="<?php echo htmlspecialchars($photo['filename']); ?>">
                                                <?php echo htmlspecialchars($photo['filename']); ?>
                                            </p>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Просмотр загруженных фотографий -->
            <div class="surface-card p-6 mb-6">
                <h2 class="heading-font text-2xl font-bold text-slate-900 mb-4">Все загруженные фотографии по офисам</h2>

                <?php if (empty($availablePhotos)): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                        <p class="text-yellow-800">Нет загруженных фотографий. Используйте форму выше для загрузки фото офисов.</p>
                    </div>
                <?php else: ?>
                    <div class="mb-4">
                        <p class="text-sm text-slate-600">Найдено фотографий: <?php echo count($availablePhotos); ?></p>
                    </div>

                    <?php
                    $officeGroups = [];
                    foreach ($availablePhotos as $photo) {
                        $officeKey = $photo['city'] . ' - ' . $photo['office_slug'];
                        $officeGroups[$officeKey][] = $photo;
                    }
                    ?>

                    <?php foreach ($officeGroups as $officeName => $officePhotos): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-slate-900 mb-3"><?php echo ucfirst($officeName); ?> (<?php echo count($officePhotos); ?> фото)</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4 bg-slate-50 rounded-xl">
                                <?php foreach ($officePhotos as $photo): ?>
                                    <div class="relative">
                                        <div class="overflow-hidden rounded-lg border-2 border-sky-200">
                                            <img src="<?php echo htmlspecialchars($photo['path']); ?>"
                                                 alt="<?php echo htmlspecialchars($photo['filename']); ?>"
                                                 class="w-full h-32 object-cover"
                                                 onerror="this.src='https://via.placeholder.com/150?text=Error'">
                                        </div>
                                        <p class="text-xs text-slate-600 mt-1 truncate" title="<?php echo htmlspecialchars($photo['filename']); ?>">
                                            <?php echo htmlspecialchars($photo['filename']); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Инструкция -->
            <div class="surface-card p-6 mt-6">
                <h3 class="heading-font text-xl font-bold text-slate-900 mb-4">Инструкция</h3>
                <ol class="list-decimal list-inside space-y-2 text-slate-600">
                    <li><strong>Загрузка фото:</strong> Выберите офис из списка и загрузите фото через форму "Загрузить фото офисов"</li>
                    <li><strong>Добавление в галерею офиса:</strong> Выберите офис из списка в разделе "Выбрать загруженные фотографии для галереи офиса"</li>
                    <li><strong>Выбор фото:</strong> Отметьте нужные фотографии (или используйте кнопку "Выбрать все" / "Снять выбор")</li>
                    <li><strong>Добавление:</strong> Нажмите "Добавить выбранные фотографии в галерею"</li>
                    <li><strong>Результат:</strong> Выбранные фотографии будут добавлены в галерею конкретного офиса и отобразятся на странице офиса</li>
                    <li><strong>Хранение:</strong> Фото хранятся в папках <code class="bg-sky-50 px-2 py-1 rounded">frontend/window/img/offices/[город]/[офис-slug]/</code></li>
                </ol>
            </div>
        </div>
    </main>

    <script>
        // Функции для управления галереей фото офисов
        function selectAllPhotos() {
            document.querySelectorAll('.photo-checkbox').forEach(checkbox => {
                checkbox.checked = true;
                updatePhotoSelection(checkbox);
            });
        }

        function deselectAllPhotos() {
            document.querySelectorAll('.photo-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                updatePhotoSelection(checkbox);
            });
        }

        function updatePhotoSelection(checkbox) {
            const label = checkbox.closest('label');
            const checkIcon = label.querySelector('.check-icon');
            if (checkbox.checked) {
                label.querySelector('div').classList.add('border-sky-500', 'ring-2', 'ring-sky-300');
                checkIcon.classList.remove('opacity-0');
            } else {
                label.querySelector('div').classList.remove('border-sky-500', 'ring-2', 'ring-sky-300');
                checkIcon.classList.add('opacity-0');
            }
        }

        function addSelectedPhotosToGallery() {
            const selectedOffice = document.getElementById('selectedOffice').value;
            if (!selectedOffice) {
                alert('Выберите офис для добавления фото');
                return;
            }

            const selectedPhotos = Array.from(document.querySelectorAll('.photo-checkbox:checked')).map(cb => cb.value);
            if (selectedPhotos.length === 0) {
                alert('Выберите хотя бы одно фото');
                return;
            }

            if (confirm(`Добавить ${selectedPhotos.length} фото в галерею выбранного офиса?`)) {
                // Создаем форму и отправляем
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="add_photos_to_gallery">
                    <input type="hidden" name="office_id" value="${selectedOffice}">
                    ${selectedPhotos.map(url => `<input type="hidden" name="selected_photos[]" value="${url}">`).join('')}
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Обработка изменения чекбоксов
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('photo-checkbox')) {
                updatePhotoSelection(e.target);
            }
        });

        // Drag & Drop для загрузки фото офисов
        const officeDropZone = document.getElementById('office-drop-zone');
        const officeFileInput = document.getElementById('office-photo-input');
        const officeFileList = document.getElementById('office-file-list');

        if (officeDropZone && officeFileInput) {
            // Обработка drag & drop событий
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                officeDropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                officeDropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                officeDropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                officeDropZone.classList.add('border-sky-500', 'bg-sky-100');
            }

            function unhighlight(e) {
                officeDropZone.classList.remove('border-sky-500', 'bg-sky-100');
            }

            officeDropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }

            officeFileInput.addEventListener('change', function(e) {
                handleFiles(e.target.files);
            });

            function handleFiles(files) {
                officeFileList.innerHTML = '';
                [...files].forEach(file => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'flex items-center gap-2 text-sm text-slate-600 bg-slate-100 px-2 py-1 rounded';
                    fileItem.innerHTML = `
                        <i class="fas fa-file-image text-sky-500"></i>
                        <span>${file.name}</span>
                        <span class="text-xs text-slate-400">(${formatFileSize(file.size)})</span>
                    `;
                    officeFileList.appendChild(fileItem);
                });
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        }

        // Функции удаления фото
        function deleteSinglePhoto(photoPath, fileName) {
            if (confirm(`Удалить фото "${fileName}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_photo">
                    <input type="hidden" name="photo_path" value="${photoPath}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteAllOfficePhotos(officeId, officeName) {
            if (confirm(`Удалить ВСЕ фото офиса "${officeName}"? Это действие нельзя отменить!`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_office_photos">
                    <input type="hidden" name="office_id" value="${officeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Обработка отправки формы загрузки фото
        const uploadForm = document.querySelector('form[action*="upload_office_photo"]');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                const uploadBtn = document.getElementById('upload-btn');
                const uploadText = document.getElementById('upload-text');
                const uploadSpinner = document.getElementById('upload-spinner');

                if (uploadBtn && uploadText && uploadSpinner) {
                    uploadBtn.disabled = true;
                    uploadText.textContent = 'Загружаем...';
                    uploadSpinner.classList.remove('hidden');
                }
            });
        }
    </script>
</body>
</html>