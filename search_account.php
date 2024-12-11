<?php
require_once 'db.php';  // Подключение к базе данных
include 'helpers.php';
$sql = "SELECT id, name, currency, balance FROM account";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_text = isset($_POST['search']) ? trim($_POST['search']) : "";
    $sql .= " WHERE name LIKE '%$search_text%' OR currency LIKE '%$search_text%'";
}
$result = $conn->query($sql);

?>