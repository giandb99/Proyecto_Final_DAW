<?php

session_start();

if (isset($_SESSION['usuario']['id'])) {
    // Si existe la marca de tiempo y han pasado más de 5 minutos (300 segundos)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 300)) {
        session_unset();
        session_destroy();
        header("Location: ../views/user/login.php?logout=timeout");
        exit;
    }
    // Actualiza el tiempo de la última actividad
    $_SESSION['last_activity'] = time();
}