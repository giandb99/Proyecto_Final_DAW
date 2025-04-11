<?php

session_start();
session_unset();
session_destroy();

// Se cierra la sesión y se redirige al usuario al catálogo
header('Location: catalog.php?logout=success');
exit;

?>

<style>
    .custom-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #f8f9fa;
        color: #333;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        text-align: center;
        width: 300px;
    }

    .custom-popup p {
        margin: 0 0 15px;
        font-size: 16px;
    }

    .custom-popup button {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    .custom-popup button:hover {
        background-color: #0056b3;
    }
</style>