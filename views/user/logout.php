<?php

// Se cierra la sesión y se redirige a la pagina donde el usuario estaba antes de hacer logout
session_start();
session_unset();
session_destroy();

// si el que cierra la sesión es el admin, se redirige a la pagina del login

if (isset($_SESSION['usuario']['admin']) && $_SESSION['usuario']['admin'] == 1) {
    header("Location: .catalog.php?logout=success");
    exit;
}else {
    header("Location: " . $_SERVER['HTTP_REFERER'] . "?logout=success");
}


// header("Location: catalog.php?logout=success");
exit;

?>