<?php
session_start();

function exigirRol(string $rol): void
{
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== $rol) {
        header('Location: ../index.php');
        exit;
    }
}
