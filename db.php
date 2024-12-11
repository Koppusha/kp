<?php
$servername = "localhost";
$username = "root";
$password = "root";
$db = 'bank';

try{
    $conn = new mysqli($servername, $username, $password, $db);

}catch(Exception $e){
    echo ("Исключение $e было обработано");
}
if ($conn->connect_error) {
    echo "disconnect";
    die("Connection failed: " . $conn->connect_error);
}
?>