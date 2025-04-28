<?php
// เชื่อมต่อฐานข้อมูล
$conn = new mysqli('localhost', 'root', '', 'repair_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// รับ service_id จาก URL
$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
if ($service_id <= 0) die("ไม่พบข้อมูลการซ่อมที่ระบุ");

// ดึงข้อมูลการซ่อมจากฐานข้อมูล
$sql = "SELECT s.id, u.username, s.issue_title, s.issue_description, s.reported_date, 
        s.status, s.completed_date, s.price 
        FROM repairs s
        JOIN users u ON s.username = u.username
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) die("ไม่พบข้อมูลใบกำกับภาษี");
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ใบกำกับภาษี</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f4f4; color: #333; margin: 0; padding: 0; }
    .invoice { max-width: 800px; margin: 50px auto; background: #fff; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; }
    .logo { font-size: 24px; font-weight: bold; color: #2c3e50; }
    .company-info { text-align: right; font-size: 14px; line-height: 1.4; }
    .company-info p { margin: 2px 0; }
    .title { margin: 30px 0 10px; font-size: 20px; font-weight: bold; color: #2c3e50; }
    .meta { margin-bottom: 30px; }
    .meta .left, .meta .right { display: inline-block; vertical-align: top; width: 48%; font-size: 14px; }
    .meta .right { text-align: right; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    table thead th { background: #2c3e50; color: #fff; padding: 10px; font-size: 14px; }
    table tbody td { border: 1px solid #ddd; padding: 10px; font-size: 14px; }
    .summary { float: right; width: 300px; }
    .summary table { width: 100%; border-collapse: collapse; }
    .summary td { padding: 8px; font-size: 14px; }
    .summary .label { text-align: left; }
    .summary .value { text-align: right; }
    .footer { clear: both; margin-top: 50px; text-align: center; font-size: 12px; color: #777; }
    @media print {
      body { background: none; }
      .invoice { box-shadow: none; margin: 0; padding: 0; }
    }
  </style>
</head>
<body>
  <div class="invoice">
    <!-- Header -->
    <div class="header">
      <div class="logo">Montee Hom Co., Ltd.</div>
      <div class="company-info">
        <p>123 ถ.บางแวก แขวงบางแวก</p>
        <p>เขตภาษีเจริญ กรุงเทพฯ 10160</p>
        <p>โทร. 02-345-6789 | เลขผู้เสียภาษี: 1234567890123</p>
      </div>
    </div>

    <!-- Title -->
    <div class="title">ใบกำกับภาษี</div>

    <!-- Meta -->
    <div class="meta">
      <div class="left">
        <p><strong>เลขที่:</strong> INV-<?php echo str_pad($data['id'], 6, '0', STR_PAD_LEFT); ?></p>
        <p><strong>วันที่ออก:</strong> <?php echo date('d/m/Y'); ?></p>
      </div>
      <div class="right">
        <p><strong>ชื่อลูกค้า:</strong> <?php echo htmlspecialchars($data['username']); ?></p>
        <p><strong>รหัสอุปกรณ์:</strong> <?php echo htmlspecialchars($data['issue_title']); ?></p>
      </div>
    </div>

    <!-- Details -->
    <table>
      <thead>
        <tr>
          <th>ลำดับ</th>
          <th>คำอธิบายปัญหา</th>
          <th>วันที่แจ้ง</th>
          <th>วันที่เสร็จสิ้น</th>
          <th>สถานะ</th>
          <th>จำนวนเงิน (บาท)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td><?php echo htmlspecialchars($data['issue_description']); ?></td>
          <td><?php echo date('d/m/Y', strtotime($data['reported_date'])); ?></td>
          <td><?php echo date('d/m/Y', strtotime($data['completed_date'])); ?></td>
          <td><?php echo htmlspecialchars($data['status']); ?></td>
          <td style="text-align:right;"><?php echo number_format($data['price'], 2); ?></td>
        </tr>
      </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
      <table>
        <tr>
          <td class="label">รวมเป็นเงิน</td>
          <td class="value"><?php echo number_format($data['price'], 2); ?></td>
        </tr>
        <tr>
          <td class="label">ภาษี 7%</td>
          <td class="value"><?php echo number_format($data['price'] * 0.07, 2); ?></td>
        </tr>
        <tr>
          <td class="label"><strong>รวมทั้งสิ้น</strong></td>
          <td class="value"><strong><?php echo number_format($data['price'] * 1.07, 2); ?></strong></td>
        }</n        </tr>
      </table>
    </div>

    <!-- Footer -->
    <div class="footer">
      บริษัทขอสงวนสิทธิ์ในการเปลี่ยนแปลงเงื่อนไขการชำระเงินโดยไม่ต้องแจ้งล่วงหน้า
    </div>
  </div>
  <script>window.onload = function(){ window.print(); };</script>
</body>
</html>

<?php $conn->close(); ?>