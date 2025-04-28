<?php
session_start();
require_once('config.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าเป็นผู้ดูแลระบบ (admin) เท่านั้นที่สามารถเข้าถึงหน้านี้ได้
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit;
}

// ดึงข้อมูลการแจ้งซ่อมที่มีสถานะเป็น "รอดำเนินการ" จากฐานข้อมูล
$sql_pending = "SELECT service.service_id, service.user_id, service.equipment_id, service.issue, 
                       service.service_date_in, service.price, service.service_status, equipment.equipment_price
                FROM service
                JOIN equipment ON service.equipment_id = equipment.equipment_id
                WHERE service.service_status = 'รอดำเนินการ'";

// ดึงข้อมูลการแจ้งซ่อมที่มีสถานะเป็น "รอการยืนยันราคา" จากฐานข้อมูล
$sql_waiting_price = "SELECT service.service_id, service.user_id, service.equipment_id, service.issue, 
                             service.service_date_in, service.price, service.service_status, equipment.equipment_price
                      FROM service
                      JOIN equipment ON service.equipment_id = equipment.equipment_id
                      WHERE service.service_status = 'รอการยืนยันราคา'";

$result_pending = $conn->query($sql_pending);
$result_waiting_price = $conn->query($sql_waiting_price);

?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ข้อมูลการแจ้งซ่อม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 1200px;
      margin: auto;
    }
    h2, h3 {
      text-align: center;
      margin-bottom: 20px;
    }
    .card {
      border: none;
      border-radius: 8px;
      margin-bottom: 30px;
      background-color: #ffffff;
    }
    .card-header {
      background-color: #ffffff;
      font-size: 18px;
      font-weight: bold;
      padding: 12px 16px;
      border-bottom: 1px solid #dee2e6;
      color: black;
    }
    .card-body {
      padding: 16px;
    }
    .table-responsive {
      max-height: 450px;
      overflow-y: auto;
    }
    .table thead th {
      background-color: #ffffff;
      position: sticky;
      top: 0;
      z-index: 1;
      color: black;
    }
    .status {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: bold;
      color: #fff;
    }
    .status.in-progress {
      background-color: #ffc107;
      color: #333;
    }
    .status.completed {
      background-color: #28a745;
    }
    .status.delayed {
      background-color: #dc3545;
    }
    .btn-action {
      font-size: 14px;
      padding: 6px 12px;
    }
    .back-btn {
      text-align: center;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2><i class="fas fa-cogs"></i> ข้อมูลการแจ้งซ่อมทั้งหมด</h2>
    
    <!-- ส่วนข้อมูลการแจ้งซ่อม (รอดำเนินการ) -->
    <div class="row">
      <div class="col-12">
        <h3><i class="fas fa-spinner"></i> สถานะ: รอดำเนินการ</h3>
        <?php if ($result_pending->num_rows > 0) { ?>
        <div class="card">
          <div class="card-header">ข้อมูลการแจ้งซ่อม (รอดำเนินการ)</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead>
                  <tr>
                    <th><i class="fas fa-tools"></i> รหัสบริการ</th>
                    <th><i class="fas fa-user"></i> รหัสผู้ใช้งาน</th>
                    <th><i class="fas fa-desktop"></i> รหัสอุปกรณ์</th>
                    <th><i class="fas fa-bug"></i> ปัญหา</th>
                    <th><i class="fas fa-calendar-day"></i> วันที่รับบริการ</th>
                    <th><i class="fas fa-money-bill-wave"></i> ราคา</th>
                    <th><i class="fas fa-cogs"></i> สถานะ</th>
                    <th><i class="fas fa-edit"></i> การกระทำ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    while ($row = $result_pending->fetch_assoc()) {
                      $price = isset($row['price']) && !is_null($row['price']) ? $row['price'] : 0.00;
                      echo "<tr>
                              <td>{$row['service_id']}</td>
                              <td>{$row['user_id']}</td>
                              <td>{$row['equipment_id']}</td>
                              <td>{$row['issue']}</td>
                              <td>{$row['service_date_in']}</td>
                              <td>" . number_format($price, 2) . "</td>
                              <td><span class='status in-progress'><i class='fas fa-spinner fa-spin'></i> {$row['service_status']}</span></td>
                              <td>
                                <form method='POST' action='confirm_repair.php' class='d-flex'>
                                  <input type='hidden' name='service_id' value='{$row['service_id']}'>
                                  <input type='number' name='price' step='0.01' class='form-control form-control-sm me-1' placeholder='ราคา' required>
                                  <button type='submit' class='btn btn-success btn-action'>
                                    <i class='fas fa-check'></i> เสนอ
                                  </button>
                                </form>
                              </td>
                            </tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php } else {
          echo "<p class='text-center text-danger'>ไม่พบข้อมูลการแจ้งซ่อมในสถานะรอดำเนินการ</p>";
        } ?>
      </div>
    </div>
    
    <!-- ส่วนข้อมูลการแจ้งซ่อม (รอการยืนยันราคา) -->
    <div class="row">
      <div class="col-12">
        <h3><i class="fas fa-clock"></i> สถานะ: รอการยืนยันราคา</h3>
        <?php if ($result_waiting_price->num_rows > 0) { ?>
        <div class="card">
          <div class="card-header">ข้อมูลการแจ้งซ่อม (รอการยืนยันราคา)</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead>
                  <tr>
                    <th><i class="fas fa-tools"></i> รหัสบริการ</th>
                    <th><i class="fas fa-user"></i> รหัสผู้ใช้งาน</th>
                    <th><i class="fas fa-desktop"></i> รหัสอุปกรณ์</th>
                    <th><i class="fas fa-bug"></i> ปัญหา</th>
                    <th><i class="fas fa-calendar-day"></i> วันที่รับบริการ</th>
                    <th><i class="fas fa-money-bill-wave"></i> ราคา</th>
                    <th><i class="fas fa-cogs"></i> สถานะ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    while ($row = $result_waiting_price->fetch_assoc()) {
                      $price = isset($row['price']) && !is_null($row['price']) ? $row['price'] : 0.00;
                      echo "<tr>
                              <td>{$row['service_id']}</td>
                              <td>{$row['user_id']}</td>
                              <td>{$row['equipment_id']}</td>
                              <td>{$row['issue']}</td>
                              <td>{$row['service_date_in']}</td>
                              <td>" . number_format($price, 2) . "</td>
                              <td><span class='status delayed'><i class='fas fa-clock'></i> รอการยืนยัน</span></td>
                            </tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php } else {
          echo "<p class='text-center text-danger'>ไม่พบข้อมูลการแจ้งซ่อมในสถานะรอการยืนยันราคา</p>";
        } ?>
      </div>
    </div>
    
    <!-- ปุ่มย้อนกลับ -->
    <div class="back-btn">
      <a href="admin-dashboard.php" class="btn btn-primary">กลับสู่แดชบอร์ด</a>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
