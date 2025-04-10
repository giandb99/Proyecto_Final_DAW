<?php

// logout.php tenemos que cerrar la sesion y redirigir a la pagina de catalogo

session_start();
session_unset();
session_destroy();
header('Location: catalog.php');