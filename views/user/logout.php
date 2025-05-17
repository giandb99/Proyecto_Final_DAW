<?php

// Se cierra la sesión y se redirige a la pagina donde el usuario estaba antes de hacer logout
session_start();
session_unset();
session_destroy();
header("Location: catalog.php?logout=success");
exit;