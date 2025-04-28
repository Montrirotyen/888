<?php
session_start();
require_once('config.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าเป็นผู้ใช้ที่มีสิทธิ์ (customer) หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header('Location: login.php');
    exit;
}

// ดึงข้อมูลการซ่อมทั้งหมดที่เกี่ยวข้องกับลูกค้าคนนี้
$user_id = $_SESSION['user_id']; // ใช้ user_id จาก session ของลูกค้า
$sql = "SELECT service_id, issue, service_date_in, service_status, price 
        FROM service 
        WHERE user_id = ? AND service_status = 'รอการยืนยันราคา'
        ORDER BY service_date_in DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);  // ใช้การ bind parameter
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($service_id, $issue, $service_date_in, $service_status, $price);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการซ่อม</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.5s ease-in-out;
        }

        h2 {
            text-align: center;
            font-size: 28px;
            color: #0a8fdc;
            margin-bottom: 30px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 14px 12px;
            text-align: center;
            border-bottom: 1px solid #f1f1f1;
            font-size: 16px;
        }

        th {
            background-color: #eaf6ff;
            color: #0a8fdc;
            font-weight: 600;
        }

        td {
            background-color: #ffffff;
            color: #555;
        }

        .status {
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
            min-width: 130px;
        }

        .status.pending {
            background-color: #fff4cc;
            color: #e0a800;
        }

        .status.confirmed {
            background-color: #c9f7cf;
            color: #28a745;
        }

        a {
            text-decoration: none;
            color: #0a8fdc;
            font-weight: 500;
        }

        a:hover {
            color: #0971b2;
        }

        .btn-back {
            display: inline-block;
            margin-top: 30px;
            background-color: #0a8fdc;
            color: white;
            padding: 12px 25px;
            text-align: center;
            border-radius: 30px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn-back:hover {
            background-color: #0869a1;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ยืนยันการซ่อม</h2>

        <!-- ถ้าไม่มีการแจ้งซ่อมจะแสดงข้อความแจ้ง -->
        <?php if ($stmt->num_rows > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>รหัสบริการ</th>
                        <th>ปัญหาที่แจ้ง</th>
                        <th>วันที่แจ้งซ่อม</th>
                        <th>สถานะการซ่อม</th>
                        <th>ราคา</th>
                        <th>รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($stmt->fetch()) { ?>
                        <tr>
                            <td><?php echo $service_id; ?></td>
                            <td><?php echo htmlspecialchars($issue); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($service_date_in)); ?></td>
                            <td><?php echo htmlspecialchars($service_status); ?></td>
                            <td><?php echo number_format($price, 2); ?> บาท</td>
                            <td>
                                <?php if ($service_status == 'รอการยืนยันราคา') { ?>
                                    <a href="customer_confirm.php?service_id=<?php echo $service_id; ?>">ยืนยันราคา</a>
                                <?php } else { ?>
                                    <a href="customer_detail.php?service_id=<?php echo $service_id; ?>">ดูรายละเอียด</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>คุณยังไม่ได้แจ้งซ่อมอุปกรณ์ใดๆ</p>
        <?php } ?>

        <!-- ปุ่มกลับไปยังหน้าหลักของลูกค้า -->
        <a href="user-dashboard.php" class="btn-back">กลับไปยังหน้าหลัก</a>
    </div>
</body>
</html>
