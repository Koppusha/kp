<?php
session_start();


function setMessage(string $key, string $message): void
{
    $_SESSION['message'][$key] = $message;
}

function redirect(string $path)
{
    header("Location: $path");
    die();
}

function hasValidationError(string $fieldName): bool
{
    return isset($_SESSION['validation'][$fieldName]);
}

function hasMessage(string $key): bool
{
    return isset($_SESSION['message'][$key]);
}

function getMessage(string $key): string
{
    $message = $_SESSION['message'][$key] ?? '';
    unset($_SESSION['message'][$key]);
    return $message;
}