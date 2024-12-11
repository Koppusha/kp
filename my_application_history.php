<?php
require_once('db.php');
include "helpers.php";

$current_user_id = $_SESSION['user_id'];
$get_applications_sql = "SELECT bo.offer_id as offer_id, la.goal as goal, u.name as bank_name, la.amount as amount, ls.status as status
    FROM loan_applications as la
    JOIN loan_status as ls
    ON ls.id = la.status_id
    JOIN bank_offers as bo
    ON bo.offer_id = la.offer_id
    JOIN users as u
    ON u.id = bo.bank_id
    WHERE la.user_id = $current_user_id";

try {
    $applications_result = $conn->query($get_applications_sql);
} catch (Exception $e) {
    echo "obrabotano: $e";
}
?>


<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История моих заявок</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        
        }

        body.dark {
            font-family: Arial, sans-serif;
            background-color: black;
            color: #000;

        }


        .header-container {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: #f8f8f8;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            position: sticky;
            margin-bottom: 20px;
            z-index: 1000;
        }

        .menu-button {
            color: #007aff;
            font-size: 17px;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 10px;
            transition: background-color 0.3s;
        }

        .menu-button:hover {
            background-color: rgba(0, 122, 255, 0.1);
        }

        .logout-button {
            color: #ff3b30;
            font-size: 17px;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 10px;
            transition: background-color 0.3s;
        }




        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .status-approved {
            color: green;
            font-weight: bold;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        .status-rejected {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header-container">
        <a href="/credits.php"  class="menu-button">НАЗАД</a>
        <a href="/"  class="logout-button">ВЫХОД</a>
    </div>
    <div class="container">
        <h1>История моих заявок</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название банка</th>
                    <th>Цель кредита</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>

                <?php
                if ($applications_result->num_rows > 0) {
                    while ($row = $applications_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['offer_id'] . "</td>";
                        echo "<td>" . $row['bank_name'] . "</td>";
                        echo "<td>" . $row['goal'] . "</td>";
                        echo "<td>" . $row['amount'] . "</td>";
                        $status_name = match ($row['status']) {
                            "pending" => 'В обработке',
                            "approved" => 'Одобрен',
                            "rejected" => 'Отклонен',
                            default => 'Unknown',
                        };
                        echo "<td class=\"status-" . $row['status'] . "\">" . $status_name . "</td>";
                        echo "</tr>";
                    }
                }
                ?>



            </tbody>
        </table>
    </div>
    <script src="theme.js"></script>
</body>

</html>