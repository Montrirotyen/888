<?php
require_once('config.php');

// ค้นหาข้อมูลการบริการทั้งหมด
$search_query = '';
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
}

// ดึงข้อมูลจากตาราง service, customer และ equipment
$sql = "SELECT 
            service.service_id, 
            equipment.equipment_name, 
            service.issue, 
            service.service_date_in, 
            service.price, 
            service.service_status, 
            customer.name AS customer_name
        FROM service
        JOIN customer ON service.user_id = customer.user_id
        JOIN equipment ON service.equipment_id = equipment.equipment_id
        WHERE service.service_status NOT IN ('เสร็จสิ้น', 'ยกเลิก', 'รอดำเนินการ')";

if ($search_query != '') {
    $sql .= " AND (service.service_id LIKE '%$search_query%' OR service.issue LIKE '%$search_query%')";
}

// ตรวจสอบการอัปเดตสำเร็จหรือไม่
if (isset($_GET['success'])) {
    if ($_GET['success'] == '1') {
        echo "<div class='alert success'>การแก้ไขเสร็จสิ้น</div>";
    } elseif ($_GET['success'] == '0') {
        echo "<div class='alert error'>เกิดข้อผิดพลาดในการแก้ไข</div>";
    }
}

$result = $conn->query($sql);
$repairs = ($result->num_rows > 0) ? $result : null;
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ค้นหาการบริการ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh; /* ทำให้ความสูงของ body เท่ากับความสูงของหน้าจอ */
            background-size: cover; /* ให้พื้นหลังขยายเต็มจอ */

        }

    header {
      text-align: center;
      margin-bottom: 30px;
    }
    header h1 {
      font-size: 28px;
      font-weight: bold;
      color: #333;
    }
    form {
      max-width: 600px;
      margin: 0 auto 30px;
      display: flex;
      gap: 10px;
    }
    form input[type="text"] {
      flex: 1;
      padding: 10px;
      font-size: 16px;
    }
    form button {
      padding: 10px 20px;
      font-size: 16px;
    }
    .alert {
      max-width: 600px;
      margin: 0 auto 20px;
      text-align: center;
    }
    table {
      font-size: 14px;
    }
    .table-responsive {
      margin-bottom: 30px;
      max-height: 450px;
      overflow-y: auto;
    }
    .back-btn {
      text-align: center;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <header>
    <h1>ค้นหาข้อมูลการบริการ</h1>
  </header>

  <section>
    <form method="POST">
      <input type="text" name="search" placeholder="ค้นหาจากหมายเลขหรือปัญหา" value="<?= htmlspecialchars($search_query) ?>">
      <button type="submit" class="btn btn-primary">ค้นหา</button>
    </form>

    <!-- Alert แสดงผลการแก้ไข -->
    <?php if (isset($_GET['status'])): ?>
      <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
        <?= $_GET['status'] == 'success' ? 'การแก้ไขเสร็จสิ้น' : 'เกิดข้อผิดพลาดในการแก้ไข' ?>
      </div>
    <?php endif; ?>

    <?php if ($repairs): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="table-light">
            <tr>
              <th>หมายเลขการบริการ</th>
              <th>อุปกรณ์</th>
              <th>ปัญหาที่แจ้ง</th>
              <th>วันที่แจ้ง</th>
              <th>ราคา</th>
              <th>สถานะการบริการ</th>
              <th>ลูกค้า</th>
              <th>จัดการ</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $repairs->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['service_id']) ?></td>
                <td><?= htmlspecialchars($row['equipment_name']) ?></td>
                <td><?= htmlspecialchars($row['issue']) ?></td>
                <td><?= htmlspecialchars($row['service_date_in']) ?></td>
                <td><?= htmlspecialchars($row['price']) ?></td>
                <td><?= htmlspecialchars($row['service_status']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td>
                <td>
  <button class="btn btn-sm btn-outline-primary" onclick="openModal(<?= $row['service_id'] ?>)">แก้ไข</button>
  <a href="assign_spare.php?service_id=<?= $row['service_id'] ?>" class="btn btn-sm btn-outline-success mt-1">
    เบิกอะไหล่
  </a>
</td>

              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-center text-danger">ไม่พบข้อมูลการบริการที่ตรงกับการค้นหา</p>
    <?php endif; ?>
  </section>

  <!-- Bootstrap Modal สำหรับแก้ไขข้อมูลการบริการ -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">แก้ไขข้อมูลการบริการ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editForm" method="POST" action="service-update.php">
            <div class="mb-3">
              <label for="equipment_name" class="form-label">อุปกรณ์</label>
              <input type="text" id="equipment_name" class="form-control" disabled>
            </div>
            <div class="mb-3">
              <label for="issue" class="form-label">ปัญหาที่แจ้ง</label>
              <textarea id="issue" name="issue" class="form-control"></textarea>
            </div>
            <div class="mb-3">
              <label for="service_status" class="form-label">สถานะการบริการ</label>
              <select id="service_status" name="service_status" class="form-select">
                <option value="รอดำเนินการ">รอดำเนินการ</option>
                <option value="กำลังดำเนินการ">กำลังดำเนินการ</option>
                <option value="เสร็จสิ้น">เสร็จสิ้น</option>
                <option value="ยกเลิก">ยกเลิก</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="price" class="form-label">ราคา</label>
              <input type="number" id="price" name="price" class="form-control">
            </div>
            <input type="hidden" id="service_id" name="service_id">
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
          <button type="submit" form="editForm" class="btn btn-primary">บันทึกการแก้ไข</button>
        </div>
      </div>
    </div>
  </div>

  <div class="back-btn">
    <a href="admin-dashboard.php" class="btn btn-outline-secondary">กลับสู่หน้าหลัก</a>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // เปิด modal โดยใช้ Bootstrap Modal API พร้อม AJAX ดึงข้อมูล
    function openModal(serviceId) {
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "get-service-data.php?service_id=" + serviceId, true);
      xhr.onload = function() {
        if (xhr.status === 200) {
          var data = JSON.parse(xhr.responseText);
          document.getElementById("service_id").value = data.service_id;
          document.getElementById("equipment_name").value = data.equipment_name;
          document.getElementById("issue").value = data.issue;
          document.getElementById("service_status").value = data.service_status;
          document.getElementById("price").value = data.price;
          var editModal = new bootstrap.Modal(document.getElementById("editModal"));
          editModal.show();
        }
      };
      xhr.send();
    }
  </script>
</body>
</html>
