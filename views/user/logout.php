<?php

session_start();
session_unset();
session_destroy();

// Se cierra la sesión y se redirige al usuario al catálogo
header('Location: catalog.php?logout=success');
exit;

?>