<?php

function debuguear($variable) : string {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

function s($html) : string {
    return htmlspecialchars($html ?? '', ENT_QUOTES, 'UTF-8');
}

function esUltimo(string $actual, string $proximo) {
    return $actual !== $proximo;
}

function isAuth() {
    if(!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        header('Location: /');
        exit;
    }
}

function isAdmin() {
    if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header('Location: /');
        exit;
    }
}
