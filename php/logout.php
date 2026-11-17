<?php
// Cierra la sesión y vuelve al login.

require_once __DIR__ . '/../includes/config.php';

$_SESSION = [];
session_destroy();

header('Location: ' . url('/index.php'));
exit;
