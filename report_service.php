<?php
session_start();
require_once('config.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าเป็นผู้ใช้งาน (customer) ที่ลงชื่อเข้าใช้แล้ว
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลอุปกรณ์เฉพาะของผู้ใช้งานจากฐานข้อมูล
$sql = "SELECT equipment_id, equipment_name FROM equipment WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งซ่อมอุปกรณ์</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Prompt', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            background: #ffffff;
            padding: 40px;
            width: 100%;
            max-width: 550px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        h1 {
            color: #007bff;
            font-weight: 700;
            margin-bottom: 25px;
        }

        label {
            display: block;
            text-align: left;
            margin: 15px 0 5px;
            font-weight: 600;
        }

        select,
        textarea,
        button {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            border-radius: 12px;
            border: none;
            font-size: 16px;
            transition: 0.3s ease;
        }

        select,
        textarea {
            border: 1px solid #ccc;
        }

        select:focus,
        textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.4);
        }

        button {
            margin-top: 20px;
            background: #28a745;
            color: white;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform 0.3s ease;
        }

        button:hover {
            background: #218838;
            transform: scale(1.05);
        }

        .btn-back {
            background: #6c757d;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .message {
            font-size: 16px;
            font-weight: bold;
            margin-top: 15px;
            padding: 12px;
            border-radius: 8px;
        }

        .text-success {
            color: #28a745;
            background: #d4edda;
        }

        .text-danger {
            color: #dc3545;
            background: #f8d7da;
        }

        .icon {
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><i class="fas fa-tools"></i> แจ้งซ่อมอุปกรณ์</h1>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_id = $_SESSION['user_id'];
            $equipment_id = $_POST['equipment_id'];
            $issue = $_POST['issue'];
            $service_date_in = date('Y-m-d');
            $price = 0.00;
            $service_status = 'รอดำเนินการ';

            if (empty($equipment_id) || empty($issue)) {
                echo "<p class='message text-danger'><i class='fas fa-exclamation-circle'></i> กรุณากรอกข้อมูลให้ครบถ้วน</p>";
            } else {
                $sql = "INSERT INTO service (user_id, equipment_id, issue, service_date_in, service_status, price)
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iisssd", $user_id, $equipment_id, $issue, $service_date_in, $service_status, $price);

                if ($stmt->execute()) {
                    echo "<p class='message text-success'><i class='fas fa-check-circle'></i> แจ้งซ่อมเรียบร้อยแล้ว</p>";
                } else {
                    echo "<p class='message text-danger'><i class='fas fa-times-circle'></i> เกิดข้อผิดพลาดในการแจ้งซ่อม</p>";
                }
            }
        }
        ?>

        <form method="POST" action="report_service.php">
            <label for="equipment_id"><i class="fas fa-laptop icon"></i> เลือกอุปกรณ์ของคุณ:</label>
            <select name="equipment_id" required>
                <option value="">เลือกอุปกรณ์</option>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['equipment_id']}'>{$row['equipment_name']}</option>";
                    }
                } else {
                    echo "<option value='' disabled>คุณยังไม่มีอุปกรณ์ในระบบ</option>";
                }
                ?>
            </select>

            <label for="issue"><i class="fas fa-exclamation-triangle icon"></i> ปัญหาของอุปกรณ์:</label>
            <textarea name="issue" rows="4" required placeholder="โปรดระบุรายละเอียดปัญหา..."></textarea>

            <button type="submit"><i class="fas fa-paper-plane"></i> แจ้งซ่อม</button>
            <button type="button" class="btn-back" onclick="history.back()"><i class="fas fa-arrow-left"></i> ย้อนกลับ</button>
        </form>
    </div>
</body>

</html>
