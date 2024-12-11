<?php
require_once 'helpers.php';

$fileName = $_GET['file'] ?? '';
$filePath = 'uploads/pdf/' . $fileName;

if (!file_exists($filePath)) {
    setMessage('FileNotFound', 'Файл не найден.');
    header('Location: profile.php');
    exit;
}
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
?>
