<?php
declare(strict_types=1);

// Session configuration (default settings)

/**
 * Global configuration and secure database bootstrap.
 *
 * This script loads environment variables from a local .env file (when present),
 * establishes a PDO connection using a SQL driver (MySQL by default) and exposes
 * the `$pdo` instance to the rest of the application.
 *
 * ⚠️ Place your database server outside the public web host or restrict remote
 * access by IP/SSL. Credentials should be stored in the .env file that is never
 * committed to the repository.
 */

// Harden PHP error display in production
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));
}

if (!APP_DEBUG) {
    ini_set('display_errors', '0');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

/**
 * Lightweight .env loader (supports KEY=VALUE, comments with #, and quoted values)
 */
if (!function_exists('load_env_file')) {
    function load_env_file(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            error_log('[ENV] File not found or not readable: ' . $path);
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            error_log('[ENV] File is empty or cannot be read');
            return;
        }

        $loaded = 0;
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                error_log("[ENV] Line " . ($lineNum + 1) . " skipped (no = sign): " . substr($line, 0, 50));
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));

            if ($key === '') {
                continue;
            }

            // Remove surrounding quotes
            if ($value !== '' && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
                $value = substr($value, 1, -1);
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $loaded++;
        }
        
        error_log('[ENV] Loaded ' . $loaded . ' variables from .env file');
    }
}

$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
// Загружаем .env файл только если он существует
if (file_exists($envPath)) {
    load_env_file($envPath);
}

// Альтернативная загрузка через parse_ini_file (если функция load_env_file не сработала)
if (file_exists($envPath) && function_exists('parse_ini_file')) {
    // Пытаемся использовать parse_ini_file как запасной вариант
    $envVars = parse_ini_file($envPath);
    if ($envVars !== false) {
        foreach ($envVars as $key => $value) {
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

$dbDriver = strtolower(getenv('DB_DRIVER') ?: 'sqlite');
$pdo = null;

// Попытка загрузить конфигурацию удаленной БД
$remoteDbConfigPath = __DIR__ . DIRECTORY_SEPARATOR . 'db_remote.php';
$useRemoteDb = false;
$remoteConfig = null;

if (file_exists($remoteDbConfigPath)) {
    $remoteConfig = require $remoteDbConfigPath;
    // Проверяем, что это не дефолтная конфигурация (есть реальные данные)
    if (isset($remoteConfig['host']) &&
        $remoteConfig['host'] !== 'aws.connect.psdb.cloud' &&
        isset($remoteConfig['database']) &&
        $remoteConfig['database'] !== 'your_database_name' &&
        isset($remoteConfig['username']) &&
        $remoteConfig['username'] !== 'your_username') {
        $useRemoteDb = true;
        error_log('[DB] Using remote database configuration from db_remote.php');
    }
}

try {
    if ($dbDriver === 'mysql') {
        if ($useRemoteDb && $remoteConfig) {
            // Используем удаленную конфигурацию
            $host = $remoteConfig['host'];
            $port = $remoteConfig['port'] ?? '3306';
            $database = $remoteConfig['database'];
            $username = $remoteConfig['username'];
            $password = $remoteConfig['password'];
            $charset = $remoteConfig['charset'] ?? 'utf8mb4';
            $useSsl = $remoteConfig['ssl'] ?? false;
        } else {
            // Получаем значения из окружения (приоритет) или из $_ENV (запасной вариант)
            $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
            $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306');
            $database = getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? 'travel_hub');
            $username = getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'travel_user');
            $password = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? '');
            $charset = getenv('DB_CHARSET') ?: ($_ENV['DB_CHARSET'] ?? 'utf8mb4');
            $useSsl = false;
        }

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // Настройка SSL для удаленных серверов (PlanetScale и др.)
        if ($useSsl) {
            // Для PlanetScale и других облачных MySQL сервисов
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            $options[PDO::MYSQL_ATTR_SSL_CA] = '';
            // Альтернативный способ для некоторых хостингов
            if (defined('PDO::MYSQL_ATTR_SSL_CA')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = '';
            }
        }

        // Optional SSL certificate (for managed MySQL services) из .env
        $sslCa = getenv('DB_SSL_CA') ?: null;
        if (!empty($sslCa) && defined('PDO::MYSQL_ATTR_SSL_CA') && !$useSsl) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
        }

        try {
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            error_log('[DB MySQL] Connection failed: ' . $e->getMessage());
            error_log('[DB MySQL] DSN: ' . $dsn);
            error_log('[DB MySQL] Username: ' . $username);
            error_log('[DB MySQL] Database: ' . $database);
            throw $e; // Пробрасываем исключение дальше
        }
    } elseif ($dbDriver === 'sqlite') {
        $sqlitePath = getenv('SQLITE_PATH');
        if (!$sqlitePath) {
            // Используем постоянную директорию data/ в корне проекта для сохранения при обновлениях
            $projectRoot = dirname(__DIR__, 2);
            $dataDir = $projectRoot . DIRECTORY_SEPARATOR . 'data';

            // Создаем директорию data/, если её нет
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
                // Создаем .gitkeep, чтобы директория сохранялась в git
                $gitkeepFile = $dataDir . DIRECTORY_SEPARATOR . '.gitkeep';
                if (!file_exists($gitkeepFile)) {
                    file_put_contents($gitkeepFile, "# Директория для постоянного хранения базы данных\n# База данных не удаляется при обновлении сайта\n");
                }
            }

            $sqlitePath = $dataDir . DIRECTORY_SEPARATOR . 'user_management.db';

            // Миграция: если база есть в старом месте, перемещаем её
            $oldPath1 = __DIR__ . DIRECTORY_SEPARATOR . 'user_management.db';
            $oldPath2 = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'user_management.db';

            if (!file_exists($sqlitePath)) {
                if (file_exists($oldPath1) && filesize($oldPath1) > 0) {
                    // Перемещаем базу из backend/config/
                    if (copy($oldPath1, $sqlitePath)) {
                        error_log('[DB MIGRATION] Database moved from backend/config/ to data/');
                    }
                } elseif (file_exists($oldPath2) && filesize($oldPath2) > 0) {
                    // Перемещаем базу из backend/
                    if (copy($oldPath2, $sqlitePath)) {
                        error_log('[DB MIGRATION] Database moved from backend/ to data/');
                    }
                }
            }
        } elseif (!str_starts_with($sqlitePath, DIRECTORY_SEPARATOR) && !preg_match('/^[A-Za-z]:\\\\/', $sqlitePath)) {
            // относительный путь — считаем от корня проекта
            $sqlitePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $sqlitePath;
        }

        // Создаем директорию для базы данных, если её нет
        $dbDir = dirname($sqlitePath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        $dsn = 'sqlite:' . $sqlitePath;
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Автоматическая инициализация таблиц, если их нет
        try {
            $tablesCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
            if ($tablesCheck->fetchColumn() === false) {
                // Таблицы не существуют, создаем их
                // Сначала включаем поддержку внешних ключей
                $pdo->exec("PRAGMA foreign_keys = ON");

                $schemaPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'schema_sqlite.sql';
                if (file_exists($schemaPath)) {
                    $schema = file_get_contents($schemaPath);
                    // Разделяем на отдельные запросы и выполняем каждый
                    $statements = array_filter(
                        array_map('trim', explode(';', $schema)),
                        function($stmt) {
                            $stmt = trim($stmt);
                            return !empty($stmt);
                        }
                    );
                    foreach ($statements as $statement) {
                        $statement = trim($statement);
                        if (!empty($statement)) {
                            $pdo->exec($statement);
                        }
                    }
                } else {
                    // Если файл схемы не найден, создаем таблицы напрямую
                    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT NOT NULL,
                        email TEXT NOT NULL UNIQUE,
                        password TEXT NOT NULL,
                        phone TEXT,
                        city TEXT,
                        age INTEGER,
                        gender TEXT,
                        passport_series TEXT,
                        passport_number TEXT,
                        passport_issued_by TEXT,
                        passport_issue_date DATE,
                        passport_expiry_date DATE,
                        role TEXT DEFAULT 'user',
                        reg_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                        last_login DATETIME,
                        status TEXT DEFAULT 'active',
                        source TEXT DEFAULT 'website' CHECK(source IN ('website', 'app'))
                    )");
                }
            } else {
                // Таблица существует, но убедимся, что foreign_keys включены
                $pdo->exec("PRAGMA foreign_keys = ON");
            }
        } catch (PDOException $e) {
            error_log('[DB] Table initialization failed: ' . $e->getMessage());
        }
    } else {
        throw new RuntimeException(sprintf('Unsupported database driver "%s".', $dbDriver));
    }

    // Restrict SQL modes for safety if MySQL/MariaDB
    if ($dbDriver === 'mysql') {
        $pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }
} catch (Throwable $e) {
    error_log('[DB] Connection failed: ' . $e->getMessage());
    $pdo = null;
}
?>