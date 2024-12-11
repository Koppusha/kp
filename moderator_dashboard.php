<?php
include 'helpers.php';
require_once("db.php");
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    redirect("/index.php");
    exit();
} else if ($_SESSION["is_active"] == false) {
    redirect("/account_blocked.php");
    exit();
}
$bank_id = $_SESSION['user_id'];
$credits_sql = "
    SELECT 
        credit_offers.*, 
        COUNT(loan_applications.id) AS applications_count
    FROM 
        credit_offers
    JOIN 
        bank_offers ON credit_offers.id = bank_offers.offer_id
    LEFT JOIN 
        loan_applications ON loan_applications.offer_id = credit_offers.id
    WHERE 
        bank_offers.bank_id = $bank_id
    GROUP BY 
        credit_offers.id
";

try {
    $credits_result = $conn->query($credits_sql);
} catch (Exception $e) {
    echo ("Исключение $e было обработано");
}

$loan_applications_sql = "SELECT la.id as loan_id, u.name as name,la.amount as amount, la.goal as goal, ls.status as status
FROM loan_applications as la
JOIN loan_status as ls
ON ls.id = la.status_id
JOIN users as u
ON la.user_id = u.id
JOIN bank_offers as bo
ON bo.offer_id = la.offer_id
WHERE bo.bank_id = $bank_id";

try {
    $loan_applications_result = $conn->query($loan_applications_sql);
} catch (Exception $e) {
    echo "obrabotrano $e";
}

$weights_sql = "SELECT value FROM weight_coefficients WHERE bank_id = ".$_SESSION['user_id'];

try {
    $weights_result = $conn->query($weights_sql);
    $weights = [];
    if ($weights_result) {
        while ($row = $weights_result->fetch_assoc()) {
            $weights[] = $row['value'];
        }
    }

    list($amount_weight, $period_weight, $rate_weight) = $weights;
} catch (Exception $e) {
    echo "obrabotrano $e";
}

?>


<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель Модератора</title>
    <link rel="stylesheet" href="moderator.css">
</head>

<body>

    <div class="moderator-container">
        <nav class="moderator-nav">
            <h2>Панель Модератора
                <?php
                echo $_SESSION["username"];
                ?>
            </h2>
            <ul>
                <li><a href="#credits">Кредитные предложения</a></li>
                <li><a href="#applications">Заявки на кредиты</a></li>
            </ul>
            <a href="\">Выход</a>
        </nav>

        <div class="moderator-content">

            <button id="openWeightsModalBtn" class="add-btn">Настроить коэффициенты</button>

            <div id="weightsModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>
                        Настройка коэффициентов
                        <span class="info-icon">i
                            <span class="tooltip">Следует вводить значения от 0.00 до 999999.00. Для коэффицента суммы лучше значения рекомендуется делать в 10-100 раз меньше, чем другие два</span>
                        </span>
                    </h2>

                    <form action="credit_offers_handler.php" method="post">
                        <div class="form-group">
                            <label for="rate_weight">Коэффициент для ставки</label>
                            <input type="number" id="rate_weight" name="rate_weight" step="0.01" min="0" max="999999"
                                value="<?php echo htmlspecialchars($rate_weight); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="period_weight">Коэффициент для срока</label>
                            <input type="number" id="period_weight" name="period_weight" step="0.01" min="0" max="999999"
                                value="<?php echo htmlspecialchars($period_weight); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="amount_weight">Коэффициент для суммы</label>
                            <input type="number" id="amount_weight" name="amount_weight" step="0.01" min="0" max="999999"
                                value="<?php echo htmlspecialchars($amount_weight); ?>" required>
                        </div>
                        <button type="submit" id="apply_weights" name="apply_weights" class="submit-btn">Применить</button>
                    </form>
                </div>
            </div>

            <section id="credits" class="moderator-section">
                <h2>Управление кредитами</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Название кредита</th>
                            <th>Процентная ставка</th>
                            <th>Максимальная сумма</th>
                            <th>Срок погашения</th>
                            <th>Действия</th>
                            <th>Популярность<br>(кол-во человек)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($credits_result->num_rows > 0) {
                            while ($row = $credits_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>Кредит " . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['rate']) . "% </td>";
                                echo "<td>" . htmlspecialchars($row['max_amount']) . " руб.</td>";
                                echo "<td>" . htmlspecialchars($row['period']) . "</td>";
                                echo "<td><button type='submit' name='edit_offers' onClick='openEditModal(" . $row['id'] . ", \"" . $row['name'] . "\", " . $row['rate'] . ", " . $row['max_amount'] . ", " . $row['period'] . ")' class=\"edit-btn\">Редактировать</button>";
                                echo "<form action='credit_offers_handler.php' method='POST'>";
                                echo "<input type='hidden' name='credit_deal_id' value='" . $row['id'] . "'>";
                                echo "<button type='submit' name='delete_deal' class=\"delete-btn\">Удалить</button></td></form>";
                                echo "<td>" . $row['applications_count'] . "</td>"; 
                                echo "</tr>";
                            }
                        }

                        ?>

                    </tbody>
                </table>
                <button id="openModalBtn" class="add-btn">Добавить кредитное предложение</button>
            </section>

            <section id="applications" class="moderator-section">
                <h2>Заявки на кредиты</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Имя пользователя</th>
                            <th>Сумма кредита</th>
                            <th>Цель кредита</th>
                            <th>Статус заявки</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($loan_applications_result->num_rows > 0) {
                            while ($row = $loan_applications_result->fetch_assoc()) {
                                echo  "<tr>";
                                echo  "<td>" . $row['name'] . "</td>";
                                echo "<td>" . $row['amount'] . "</td>";
                                echo "<td>" . $row['goal'] . "</td>";
                                $status_name = match ($row['status']) {
                                    "pending" => 'В обработке',
                                    "approved" => 'Одобрен',
                                    "rejected" => 'Отклонен',
                                    default => 'Unknown',
                                };
                                echo "<td>" . $status_name . "</td>";
                                echo "<td>";
                                echo "<form action='credit_offers_handler.php' method='POST'>";
                                echo "<input type='hidden' name='loan_id' value='" . $row['loan_id'] . "'>";
                                echo "<button name=\"approve\" class=\"approve-btn\">Одобрить</button>";
                                echo "<button name=\"reject\" type='submit' class=\"reject-btn\">Отклонить</button>";
                                echo "</form>";
                                echo "</td>";
                                echo  "</tr>";
                            }
                        }

                        ?>
                    </tbody>
                </table>
            </section>

        </div>

        <div id="addLoanModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Добавить кредитное предложение</h2>

                <form id="addLoanForm" action="credit_offers_handler.php" method="POST">
                    <div class="form-group">
                        <label for="loanName">Название кредита</label>
                        <input type="text" id="loanName" name="loanName" placeholder="Введите название кредита" required>
                    </div>
                    <div class="form-group">
                        <label for="interestRate">Процентная ставка</label>
                        <input type="number" id="interestRate" max="100" min="0" step="0.01" name="interestRate" placeholder="Введите процентную ставку" required>
                    </div>
                    <div class="form-group">
                        <label for="maxAmount">Максимальная сумма</label>
                        <input type="number" id="maxAmount" name="maxAmount" max="10000000" min="0" placeholder="Введите максимальную сумму" required>
                    </div>
                    <div class="form-group">
                        <label for="repaymentTerm">Срок погашения</label>
                        <input type="number" id="repaymentTerm" name="repaymentTerm" max="40" min="0" placeholder="Введите срок погашения" required>
                    </div>

                    <button name="add_credit_deal" type="submit" class="btn">Добавить</button>

                </form>
            </div>
        </div>

        <div id="editLoanModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Редактировать кредитное предложение</h2>

                <form id="editLoanForm" action="credit_offers_handler.php" method="POST">
                    <div class="form-group">
                        <label for="editloanName">Название кредита</label>
                        <input type="text" id="editloanName" name="editloanName" placeholder="Введите название кредита" required>
                    </div>
                    <div class="form-group">
                        <label for="editInterestRate">Процентная ставка</label>
                        <input type="number" id="editInterestRate" name="editInterestRate" max="100" min="0" step="0.01" placeholder="Введите процентную ставку" required>
                    </div>
                    <div class="form-group">
                        <label for="editAmount">Максимальная сумма</label>
                        <input type="number" id="editAmount" name="editAmount" max="100000000" min="0" placeholder="Введите максимальную сумму" required>
                    </div>
                    <div class="form-group">
                        <label for="editRepaymentTerm">Срок погашения</label>
                        <input type="number" id="editRepaymentTerm" name="editRepaymentTerm" max="40" min="0" placeholder="Введите срок погашения" required>
                    </div>
                    <input type='hidden' id='offer_id' name='offer_id' value="">
                    <button name="apply_edit" type="submit" class="btn">Применить изменения</button>

                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const weightsModal = document.getElementById("weightsModal");
            const openWeightsBtn = document.getElementById("openWeightsModalBtn");
            const weightsClose = weightsModal.querySelector(".close");

            openWeightsBtn.onclick = function() {
                weightsModal.style.display = "block";
            }
            weightsClose.onclick = function() {
                weightsModal.style.display = "none";
            }
            window.onclick = function(event) {
                if (event.target == weightsModal) {
                    weightsModal.style.display = "none";
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const addModal = document.getElementById("addLoanModal");
            const addBtn = document.getElementById("openModalBtn");
            const addClose = addModal.querySelector(".close"); 

            const editModal = document.getElementById("editLoanModal");
            const editClose = editModal.querySelector(".close"); 

            addBtn.onclick = function() {
                addModal.style.display = "block";
            }

            addClose.onclick = function() {
                addModal.style.display = "none";
            }

            editClose.onclick = function() {
                editModal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == addModal) {
                    addModal.style.display = "none";
                } else if (event.target == editModal) {
                    editModal.style.display = "none";
                }
            }

            document.getElementById('addLoanForm').addEventListener('submit', function(e) {
                const loanName = document.getElementById('loanName').value;
                const interestRate = document.getElementById('interestRate').value;
                const maxAmount = document.getElementById('maxAmount').value;
                const repaymentTerm = document.getElementById('repaymentTerm').value;

                console.log("Добавлено кредитное предложение:", loanName, interestRate, maxAmount, repaymentTerm);

                addModal.style.display = "none";
            });
        });

        function openEditModal(id, loanName, rate, amount, repaymentTerm) {
            const editModal = document.getElementById("editLoanModal");
            editModal.style.display = "block";

            document.getElementById('editloanName').value = loanName;
            document.getElementById('editInterestRate').value = rate;
            document.getElementById('editAmount').value = amount;
            document.getElementById('editRepaymentTerm').value = repaymentTerm;
            document.getElementById('offer_id').value = id;
        }
    </script>
</body>

</html>