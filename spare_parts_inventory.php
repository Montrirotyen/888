<?php
// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "repair_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงข้อมูลอะไหล่พร้อมการคำนวณ
$sql = "
    SELECT 
        sp.spare_name, 
        sp.spare_stock, 
        sp.spare_price,
        IFNULL(SUM(rsp.quantity_used), 0) AS total_used,
        (sp.spare_stock - IFNULL(SUM(rsp.quantity_used), 0)) AS remaining,
        IFNULL(SUM(rsp.quantity_used), 0) * sp.spare_price AS total_value
    FROM spare_parts sp
    LEFT JOIN repair_spare_parts rsp ON sp.spare_id = rsp.spare_id
    GROUP BY sp.spare_id, sp.spare_name, sp.spare_stock, sp.spare_price
";

$result = $conn->query($sql);

// เพิ่มการตรวจสอบสำหรับการ export ข้อมูล
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // ใช้ PhpSpreadsheet เพื่อ export ไปยัง Excel
    require 'vendor/autoload.php'; // ใช้ autoload ของ Composer (ถ้าติดตั้งผ่าน Composer)
    
    // เริ่มต้นการสร้าง Spreadsheet
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // ตั้งชื่อหัวตาราง
    $sheet->setCellValue('A1', 'ชื่ออะไหล่');
    $sheet->setCellValue('B1', 'ราคาต่อหน่วย (บาท)');
    $sheet->setCellValue('C1', 'จำนวนที่มีทั้งหมด');
    $sheet->setCellValue('D1', 'จำนวนที่เบิกไป');
    $sheet->setCellValue('E1', 'จำนวนคงเหลือ');
    $sheet->setCellValue('F1', 'ยอดรวม (บาท)');

    // กำหนดแถวในการแสดงข้อมูล
    $row_num = 2; // เริ่มจากแถวที่ 2 (แถวที่ 1 เป็นหัวตาราง)
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row_num, $row['spare_name']);
        $sheet->setCellValue('B' . $row_num, $row['spare_price']);
        $sheet->setCellValue('C' . $row_num, $row['spare_stock']);
        $sheet->setCellValue('D' . $row_num, $row['total_used']);
        $sheet->setCellValue('E' . $row_num, $row['remaining']);
        $sheet->setCellValue('F' . $row_num, $row['total_value']);
        $row_num++;
    }
    
    // กำหนดชื่อไฟล์ Excel
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = 'spare_parts_inventory.xlsx';
    
    // ส่งออกไฟล์ Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คลังอะไหล่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #87CEEB, #6a5acd);
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            margin-top: 40px;
        }
        h2 {
            color: #343a40;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .btn-back {
            margin-top: 20px;
        }
        .btn-export {
            margin-top: 20px;
            display: block;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-warehouse"></i> คลังอะไหล่</h2>

    <!-- ปุ่ม Export ข้อมูลไปยัง Excel -->
    <div class="text-center">
        <a href="?export=excel" class="btn btn-success btn-export">
            <i class="fas fa-file-excel"></i> Export to Excel
        </a>
    </div>

    <!-- แสดงข้อมูลตาราง -->
    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-striped table-bordered text-center">
            <thead>
                <tr>
                    <th>ชื่ออะไหล่</th>
                    <th>ราคาต่อหน่วย (บาท)</th>
                    <th>จำนวนที่มีทั้งหมด</th>
                    <th>จำนวนที่เบิกไป</th>
                    <th>จำนวนคงเหลือ</th>
                    <th>ยอดรวม (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['spare_name']); ?></td>
                        <td><?php echo number_format($row['spare_price'], 2); ?></td>
                        <td><?php echo $row['spare_stock']; ?></td>
                        <td><?php echo $row['total_used']; ?></td>
                        <td><?php echo $row['remaining']; ?></td>
                        <td><?php echo number_format($row['total_value'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center text-danger">ไม่พบข้อมูลอะไหล่</p>
    <?php endif; ?>

    <div class="text-center btn-back">
        <a href="admin-dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> กลับสู่แดชบอร์ด</a>
    </div>
</div>

<!-- FontAwesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

</body>
</html>

<?php $conn->close(); ?>
