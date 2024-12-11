<?php
include("helpers.php");
require_once("db.php");

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['upload_blob']) && isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imageName = $_FILES['image']['name'];
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageSize = $_FILES['image']['size'];
        $imageType = $_FILES['image']['type'];



        if ($imageSize > 5 * 1024 * 1024) {
            setMessage("OutOfMemoryError", "Файл слишком большой");
            redirect("/profile.php");
        }


        $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];

        if (!in_array($imageType, $allowedTypes)) {
            setMessage("InvalidFileType", "Недопустимый тип файла");
            redirect("/profile.php");
        }

        if (exif_imagetype($imageTmpName) === false) {
            setMessage("InvalidFileType", "Файл поврежден или не является изображением");
            redirect("/profile.php");
        }

        $imageData = file_get_contents($imageTmpName);

        $query = "INSERT INTO user_data (user_id, user_image)
                  VALUES (?, ?)
                  ON DUPLICATE KEY UPDATE user_image = VALUES(user_image);";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ib", $userId, $imageData);
        $stmt->send_long_data(1, $imageData);


        if ($stmt->execute()) {
            header("Location: profile.php");
            exit();
        } else {
            echo "Ошибка: " . $conn->error;
        }

        $stmt->close();
    } elseif (isset($_POST['upload_pdf']) && isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === 0) {
        try {
            try {
                $file = $_FILES['pdfFile'];
                $fileName = $file['name'];
                $fileTmpPath = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileError = $file['error'];
            } catch (Exception $e) {
                setMessage("InvalidFileType", "$e");
                redirect("/profile");
            }


            if ($fileError !== UPLOAD_ERR_OK) {
                setMessage("UploadError", "Ошибка загрузки файла.");
                redirect("/profile.php");
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo);

            $allowedMimeType = 'application/pdf';

            if ($mimeType !== $allowedMimeType) {
                setMessage("InvalidFileType", "Файл поврежден или не является pdf");
                redirect("profile.php");
            }

            if ($fileSize > 5 * 1024 * 1024) {
                setMessage("OutOfMemoryError", "Файл слишком большой");
                redirect("profile.php");
            }

            $uploadDir = 'uploads/pdf/';
            $safeFileName = uniqid('pdf_', true) . '.pdf';
            $destination = $uploadDir . $safeFileName;

            if (move_uploaded_file($fileTmpPath, $destination)) {
                $insertQuery = "INSERT INTO user_data (user_id, file_path)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE file_path = VALUES(file_path)";
                $stmt = $conn->prepare($insertQuery);
                $stmt->bind_param("is", $userId, $safeFileName);
                $stmt->execute();
                $stmt->close();

                setMessage("SuccessfullUpload", "PDF файл успешно загружен");

                redirect("/profile.php");
            } else {
                echo "Ошибка загрузки файла.";
            }
        } catch (Exception $e) {
            setMessage("InvalidFileType", "$e");
            redirect("/profile");
        }
    } elseif (isset($_POST['download_history_file'])) {


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $query = "SELECT * FROM loan_applications as la
        JOIN loan_status as ls
        ON ls.id = la.status_id
        WHERE user_id = $userId";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {

            $sheet->setCellValue('A1', 'Application ID')
                ->setCellValue('B1', 'User ID')
                ->setCellValue('C1', 'Loan Amount')
                ->setCellValue('D1', 'Period')
                ->setCellValue('E1', 'Status');


            $row = 2;
            while ($rowData = $result->fetch_assoc()) {
                $sheet->setCellValue('A' . $row, $rowData['id'])
                    ->setCellValue('B' . $row, $rowData['user_id'])
                    ->setCellValue('C' . $row, $rowData['amount'])
                    ->setCellValue('D' . $row, $rowData['period'])
                    ->setCellValue('E' . $row, $rowData['status']);
                $row++;
            }
        }

        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


        $writer = new Xlsx($spreadsheet);


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="loan_applications.xlsx"');
        header('Cache-Control: max-age=0');


        $writer->save('php://output');


        $conn->close();
        exit();
    } elseif  (isset($_POST['theme'])) {
        $theme = $_POST['theme']; 
        try{
            $secretKey = 'very_secretno';
            $hashedData = hash_hmac('sha256', $theme, $secretKey);
            setcookie('theme', $hashedData, time() + (60 * 60 * 24 * 30 ), '/');
        }catch(Exception $e){
            setMessage("InvalidFileType", "$e");
        }
         
    } 




}
