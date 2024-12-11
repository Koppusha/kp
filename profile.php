<?php
include("helpers.php");
require_once("db.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);


$userId = $_SESSION['user_id'];  
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
    redirect("/index.php");
    exit();
}

$query = "SELECT user_image, file_path FROM user_data WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($imageBlob, $filePath);
$stmt->fetch();
$stmt->close();



$query = "SELECT file_path FROM user_data WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($fileName);
$stmt->fetch();
$stmt->close();


$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="profile.css">
    <script>
        function triggerFileInput() {
            const fileInput = document.getElementById("image");
            fileInput.onchange = function() {
                if (fileInput.files.length > 0) {
                    document.getElementById("upload_blob").click();
                }
            };
            fileInput.click();
        }
        setTimeout(() => {
            const notice = document.querySelector('.notice-error');
            if (notice) {
                notice.remove();
            }
        }, 4000);


        function handleImageError(imgElement) {
            imgElement.src = '/images/damage.png';
            imgElement.alt = 'Файл поврежден';
        }
    </script>
</head>

<body>
    <div class="header-container" id="header-container">
        <a href="/account.php" class="back-button">Назад</a>
        <a href="logout.php" class="logout-button">Выход</a>
    </div>

    <div class="theme-switcher">
        <input type="checkbox" id="theme-toggle" />
        <label for="theme-toggle" class="toggle-label">
            <span class="toggle-icon moon">🌙</span>
            <span class="toggle-icon sun">☀️</span>
        </label>
    </div>

    <?php if (hasMessage('SuccessfullUpload')): ?>
        <div class="success"><?php echo getMessage('SuccessfullUpload'); ?></div>
    <?php endif; ?>

    <?php if (hasMessage('OutOfMemoryError')): ?>
        <div class="notice-error"><?php echo getMessage('OutOfMemoryError'); ?></div>
    <?php endif; ?>

    <?php if (hasMessage('FileNotFound')): ?>
        <div class="notice-error"><?php echo getMessage('FileNotFound'); ?></div>
    <?php endif; ?>

    <?php if (hasMessage('InvalidFileType')): ?>
        <div class="notice-error"><?php echo getMessage('InvalidFileType'); ?></div>
    <?php endif; ?>


    <div class="profile-container">
        <div class="profile-image">
            <?php if ($imageBlob) : ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($imageBlob); ?>" alt="Изображение профиля" onerror="handleImageError(this)">
            <?php else : ?>
                <p>Изображение отсутствует</p>
            <?php endif; ?>
            <div class="upload-buttons">
                <form action="profile_handler.php" method="post" enctype="multipart/form-data">
                    <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                    <button type="button" onclick="triggerFileInput()">Загрузить как BLOB</button>
                    <button type="submit" id="upload_blob" name="upload_blob" style="display: none;"></button>
                </form>
            </div>
        </div>
    </div>
    <h3><?php echo $_SESSION['username'] ?></h3>

    <form id="uploadForm" action="profile_handler.php" method="post" enctype="multipart/form-data">
        <?php if ($fileName): ?>
            <a href="download_pdf.php?file=<?php echo urlencode($fileName); ?>" target="_blank">Скачать PDF</a>
        <?php else: ?>
            <p>Файл отсутствует.</p>
        <?php endif; ?>

        <label for="pdfFile" class="upload-label">Выберите файл:</label>
        <input type="file" name="pdfFile" id="pdfFile" class="upload-input" accept="application/pdf" required hidden>
        <button type="button" class="custom-file-button" onclick="document.getElementById('pdfFile').click()">Выбрать PDF</button>
        <span id="file-name" class="file-name" style="color: orange;">Файл не выбран</span>
        <button type="submit" id="uploadButton" class="upload-button" name="upload_pdf">Выгрузить PDF</button>
    </form>

    <form action="profile_handler.php" method="post">
        <button type="submit" name="download_history_file" class="download-history-file-button">Скачать историю заявок</button>
    </form>
    <script src="theme.js"></script>
    <script>
        document.getElementById('pdfFile').addEventListener('change', function() {
            document.getElementById('file-name').textContent = this.files[0] ? this.files[0].name : 'Вы не выбрали файл';
        });

        const pdfFileInput = document.getElementById('pdfFile');
        const uploadForm = document.getElementById('uploadForm');

        uploadForm.addEventListener('submit', (event) => {
            const file = pdfFileInput.files[0];

            if (!file) {
                event.preventDefault();
                showErrorMessage("Файл не найден. Возможно, он был удален или перемещен.");
                return;
            }

            if (file.size === 0) {
                event.preventDefault();
                showErrorMessage("Файл пустой .")
                return;
            }

            if (file.type !== 'application/pdf') {
                event.preventDefault();
                showErrorMessage("Файл не является PDF.");
                return;
            }

        });


        function saveTheme(theme) {
            fetch("profile_handler.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `theme=${encodeURIComponent(theme)}` 
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        console.log(data.message);
                    } else {
                        console.error(data.message);
                    }
                })
                .catch(error => {
                    console.error("Ошибка:", error);
                });
        }

        document.getElementById("theme-toggle").addEventListener("change", function() {
            const theme = this.checked ? "dark" : "light";
            saveTheme(theme);
            setTheme(theme);
        });

        window.addEventListener("load", () => {
            var theme = getCookie("theme") || "light";
            if (theme === "efec50a51a1112b721a2472aa6eea65f827d59420a27d892bac878aa34517d52") {
                theme = "light"
            } else if (theme === "3d1b6e5178bcb728f9ddc6c1fdc0a1d809f88f707c0566dcbc1206ee5fcc5d61") {
                theme = "dark"
            } 
            document.getElementById("theme-toggle").checked = theme === "dark"; 
            setTheme(theme);
        });

        function showErrorMessage(message) {
            if (document.querySelector('.notice-error')) return;

            const errorDiv = document.createElement('div');
            errorDiv.className = 'notice-error';
            errorDiv.textContent = message;

            document.body.appendChild(errorDiv);

            setTimeout(() => {
                errorDiv.remove();
            }, 4000);
        }
    </script>
</body>

</html>