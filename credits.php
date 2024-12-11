<?php
require_once 'db.php';  
include 'helpers.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    redirect("/index.php");
    exit();
}

$get_offers_sql = "CALL GetWeightedPopularLoans()";

try {
    $offer_result = $conn->query($get_offers_sql);
    $bank_offers = [];
    $total_applications = 0;

    while ($row = $offer_result->fetch_assoc()) {
        $total_applications += $row['applications_count']; 
        $bank_offers[$row['bank_name']][] = $row;
    }
} catch (Exception $e) {
    echo ("Исключение $e было обработано");
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кредитные предложения</title>
    <link rel="stylesheet" href="credits.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
</head>

<body>
    <div class="header-container">
        <a href="/account.php" class="menu-button">Мои cчета</a>
        <a href="/my_application_history.php" class="menu-button">История заявок</a>
        <a href="logout.php" class="logout-button">Выход</a>
    </div>

    <div class="container">
        <h1>Кредитные предложения от банков</h1>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php
                foreach ($bank_offers as $bank_name => $offers) {
                    echo "<div class=\"swiper-slide\">
                        <div class=\"bank-card\">
                            <h2>" . htmlspecialchars($bank_name) . "</h2>
                            <ul>";

                    $prev_count = null;
                    $color_class = 'gray';
                    foreach ($offers as $index => $offer) {
                        if ($index == 0) {
                            $color_class = 'gold';
                        } elseif ($index == 1 || ($prev_count && $offer['applications_count'] == $prev_count)) {
                            $color_class = 'orange';
                        } else {
                            $color_class = 'gray';
                        }

                        $prev_count = $offer['applications_count'];

                        $submission_percentage = $total_applications > 0 ? round(($offer['applications_count'] / $total_applications) * 100, 2) : 0;

                        echo "<li>
                            <button class=\"credit-btn $color_class\" onclick=\"openApplicationForm('" . htmlspecialchars($offer['offer_id']) . "','" . htmlspecialchars($offer['offer_name']) . "', '" . htmlspecialchars($bank_name) . "')\">
                                <div>Название: " . htmlspecialchars($offer['offer_name']) . "</div>
                                <div>Ставка: " . htmlspecialchars($offer['rate']) . "%</div>
                                <div>Сумма: до " . htmlspecialchars($offer['max_amount']) . " руб</div>
                                <div>Срок: " . htmlspecialchars($offer['period']) . " лет</div>
                                <div>Процент подачи: $submission_percentage%</div>
                            </button>
                        </li>";
                    }
                    echo "</ul>
                        </div>
                    </div>";
                }
                ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <div id="applicationModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Подать заявку на кредит</h2>
            <form id="applicationForm" action="submit_credit.php" method="POST">
                <input type="hidden" id="offerId" name="offerId" readonly>
                <div class="form-group">
                    <label for="bankName">Банк</label>
                    <input type="text" id="bankName" name="bankName" readonly>
                </div>
                <div class="form-group">
                    <label for="creditName">Кредитное предложение</label>
                    <input type="text" id="creditName" name="creditName" readonly>
                </div>
                <div class="form-group">
                    <label for="amount">Сумма кредита</label>
                    <input type="number" id="amount" name="amount" max="10000000" min="0" required>
                </div>
                <div class="form-group">
                    <label for="goal">Цель кредита</label>
                    <input type="text" id="goal" name="goal" required>
                </div>
                <div class="form-group">
                    <label for="period">Желаемый срок кредита</label>
                    <input type="number" id="period" name="period" max="40" min="0" required>
                </div>
                <div class="form-group">
                    <label for="salary">Ваш ежемесячный доход</label>
                    <input type="number" id="salary" name="salary" max="10000000" min="0" required>
                </div>
                <button type="submit" name="submit_application" class="btn">Подать заявку</button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="credits.js"></script>
    <script src="theme.js"></script>

</body>

</html>