<?php
// Указываем диапазон ID
$maxId = 2085; // Максимальный ID
$minId = 200;  // Минимальный ID

// Проверка наличия текущего ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $currentId = $maxId; // Если ID не указан, начинаем с максимального
} else {
    $currentId = intval($_GET['id']);
}

// Ограничиваем ID в пределах диапазона
if ($currentId > $maxId) {
    $currentId = $maxId;
} elseif ($currentId < $minId) {
    echo json_encode(['success' => false, 'error' => 'Достигнут минимальный ID']);
    exit;
}

// Формируем URL для текущего ID
$url = "https://t.me/s/ob_rus/$currentId";

// Загружаем HTML-страницу
$html = @file_get_contents($url);
if ($html === false) {
    echo json_encode([
        'success' => false,
        'error' => 'Не удалось загрузить страницу',
        'nextId' => $currentId - 1 // Переходим к следующему (меньшему) ID
    ]);
    exit;
}

// Извлечение изображения
preg_match("/tgme_widget_message_photo_wrap.*?background-image:url\\('(.*?)'\\)/", $html, $imageMatch);
$image = isset($imageMatch[1]) ? $imageMatch[1] : null;

// Извлечение текста сообщения
preg_match('/<div class="tgme_widget_message_text js-message_text".*?>(.*?)<\/div>/s', $html, $textMatch);
$text = isset($textMatch[1]) ? $textMatch[1] : null;

// Преобразование текста
if ($text) {
    $text = html_entity_decode($text); // Декодируем HTML-сущности
    $text = preg_replace('/<br\s*\/?>/i', "\n", $text); // Заменяем <br> на \n
    $text = nl2br($text); // Преобразуем новые строки обратно в <br>
}

// Если найден контент, отправляем успешный ответ
if ($image || $text) {
    echo json_encode([
        'success' => true,
        'image' => $image,
        'text' => $text,
        'nextId' => $currentId - 1 // Указываем следующий ID для загрузки
    ]);
} else {
    // Если контент не найден, переходим к следующему ID
    echo json_encode([
        'success' => false,
        'error' => 'Контент не найден',
        'nextId' => $currentId - 1
    ]);
}
?>
