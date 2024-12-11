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

function updateTable($conn, string $tableName, array $keys, array $values, int $id)
{

    $setClauses = [];
    foreach ($keys as $key) {
        $setClauses[] = "$key = ?";
    }
    $setString = implode(", ", $setClauses);

    try {
        $sql = "UPDATE $tableName SET $setString WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $types = '';
        foreach ($values as $value) {
            switch (gettype($value)) {
                case 'integer':
                    $types .= 'i';  
                    break;
                case 'double':
                    $types .= 'd';  
                    break;
                case 'string':
                    $types .= 's';  
                    break;
                default:
                    $types .= 'b';  
                    break;
            }
        }

        $types .= 'i';

        $values[] = $id;

        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return true;
        } else {
            echo "Ошибка при обновлении записи: " . $conn->error;
            return false;
        }

        $stmt->close();
        $conn->close();

       
        exit();
    } catch (Exception $e) {
        echo ("Исключение было обработано: " . $e->getMessage());
        return false;
    }
    return true;
}
