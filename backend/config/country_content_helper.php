<?php
/**
 * Общая функция для загрузки контента стран из БД
 * Используется на всех страницах стран
 */

function loadCountryContentFromDB($pdo, $slug) {
    if (!$pdo) return null;
    
    try {
        // Проверяем существование таблицы
        $tableCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='country_content'");
        if (!$tableCheck->fetchColumn()) {
            return null;
        }
        
        $stmt = $pdo->prepare('SELECT * FROM country_content WHERE country_slug = ?');
        $stmt->execute([$slug]);
        $content = $stmt->fetch();
        
        if ($content) {
            return [
                'bio' => $content['bio'],
                'highlights' => $content['highlights'] ? json_decode($content['highlights'], true) : null,
                'useful_info' => $content['useful_info'] ? json_decode($content['useful_info'], true) : null,
                'detailed_info' => $content['detailed_info'] ? json_decode($content['detailed_info'], true) : null,
            ];
        }
    } catch (PDOException $e) {
        error_log('[country_content_helper] Error loading content from DB: ' . $e->getMessage());
    }
    
    return null;
}

/**
 * Применяет контент из БД к массиву данных страны
 */
function applyCountryContentFromDB($pdo, $slug, &$countryData) {
    $dbContent = loadCountryContentFromDB($pdo, $slug);
    
    if ($dbContent) {
        // Обновляем данные из БД
        if (!empty($dbContent['bio'])) {
            $countryData['bio'] = $dbContent['bio'];
        }
        if (!empty($dbContent['highlights']) && is_array($dbContent['highlights'])) {
            $countryData['highlights'] = $dbContent['highlights'];
        }
        if (!empty($dbContent['useful_info'])) {
            if (!empty($dbContent['useful_info']['bestTime'])) {
                $countryData['bestTime'] = $dbContent['useful_info']['bestTime'];
            }
            if (!empty($dbContent['useful_info']['currency'])) {
                $countryData['currency'] = $dbContent['useful_info']['currency'];
            }
            if (!empty($dbContent['useful_info']['language'])) {
                $countryData['language'] = $dbContent['useful_info']['language'];
            }
            if (!empty($dbContent['useful_info']['visa'])) {
                $countryData['visa'] = $dbContent['useful_info']['visa'];
            }
        }
        if (!empty($dbContent['detailed_info'])) {
            $countryData['detailedInfo'] = $dbContent['detailed_info'];
        }
    }
}

























