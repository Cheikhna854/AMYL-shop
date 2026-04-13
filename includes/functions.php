<?php
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function current_date() {
    return date('Y-m-d');
}

function current_time() {
    return date('H:i:s');
}

function format_cfa($value) {
    return number_format((float)$value, 2, ',', ' ') . ' CFA';
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
