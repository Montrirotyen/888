<?php
// เริ่ม session เพื่อตรวจสอบการล็อกอินของผู้ใช้
session_start();

// เชื่อมต่อฐานข้อมูลผ่านไฟล์ config.php
require_once('config.php');

// ตรวจสอบว่า user_id มีการเซสชั่นหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// รับ user_id จาก session
$user_id = $_SESSION['user_id'];

// ดึงข้อมูลการบริการ (service) ของลูกค้าคนนี้ โดยใช้ user_id
$sql = "SELECT e.equipment_name, r.issue, r.service_date_in, r.service_status, r.price 
        FROM service r
        JOIN equipment e ON r.equipment_id = e.equipment_id
        WHERE r.user_id = ? ORDER BY r.service_date_in DESC";

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
    <title>ตรวจสอบสถานะการซ่อม</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
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

        h1 {
            color: #4e73df;
            text-align: center;
            margin-bottom: 40px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: center;
        }

        th {
            background: linear-gradient(45deg, #4e73df, #6a5acd);
            color: white;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background: #f3f4f6;
        }

        tr:hover {
            background: #e2e6ea;
            transition: background 0.3s;
        }

        .status {
            padding: 8px 12px;
            border-radius: 12px;
            font-weight: bold;
            color: black;
        }

        /* สีสถานะ */
.pending { 
    background-color: #ffc107; 
    color: #333; /* เปลี่ยนสีข้อความให้เป็นสีดำ */
}

.in-progress { 
    background-color: #007bff; 
    color: #fff; /* สีขาวเพื่อให้อ่านง่าย */
}

.waiting-price { 
    background-color: #fd7e14; 
    color: #fff; /* สีขาว */
}

.completed { 
    background-color: #28a745; 
    color: #fff; /* สีขาว */
}

.cancelled { 
    background-color: #dc3545; 
    color: #fff; /* สีขาว */
}


        .no-data {
            color: #e74c3c;
            font-weight: bold;
        }

        /* ปุ่มย้อนกลับ */
        .btn-back {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(45deg, #6c757d, #495057);
            color: white;
            border-radius: 30px;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
            transition: transform 0.3s;
        }

        .btn-back:hover {
            transform: scale(1.1);
            background: #343a40;
        }

        .icon {
            margin-right: 8px;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- หัวเรื่อง -->
        <h1><i class="fas fa-clipboard-list"></i> ตรวจสอบสถานะการซ่อม</h1>

        <!-- ปุ่มย้อนกลับ -->
        <a href="user-dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left icon"></i> กลับสู่หน้าหลัก
        </a>

        <!-- ตารางแสดงข้อมูล -->
        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-laptop icon"></i> ชื่ออุปกรณ์</th>
                    <th><i class="fas fa-exclamation-triangle icon"></i> ปัญหาที่แจ้ง</th>
                    <th><i class="fas fa-calendar-day icon"></i> วันที่รับบริการ</th>
                    <th><i class="fas fa-info-circle icon"></i> สถานะการบริการ</th>
                    <th><i class="fas fa-coins icon"></i> ราคา</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $statusClass = '';
                        switch ($row['service_status']) {
                            case 'รอดำเนินการ': $statusClass = 'pending'; break;
                            case 'รอซ่อม': $statusClass = 'in-progress'; break;
                            case 'รอการยืนยันราคา': $statusClass = 'waiting-price'; break;
                            case 'เสร็จสิ้น': $statusClass = 'completed'; break;
                            case 'ยกเลิก': $statusClass = 'cancelled'; break;
                        }

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['equipment_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['issue']) . "</td>";
                        echo "<td>" . date("d-m-Y", strtotime($row['service_date_in'])) . "</td>";
                        echo "<td><span class='status $statusClass'>" . htmlspecialchars($row['service_status']) . "</span></td>";
                        echo "<td>" . htmlspecialchars($row['price']) . " บาท</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='no-data'><i class='fas fa-exclamation-circle'></i> ไม่มีการบริการ</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>

</body>

</html>
