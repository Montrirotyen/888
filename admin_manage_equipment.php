<?php
session_start();
require_once('config.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าเป็นผู้ใช้งานประเภท admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit;
}

// ดึงข้อมูลอุปกรณ์ทั้งหมดจากฐานข้อมูล
$sql = "SELECT e.equipment_id, e.equipment_name, e.equipment_status, e.warranty, 
               e.warranty_start_date, e.warranty_end_date, u.username 
        FROM equipment e
        JOIN users u ON e.user_id = u.user_id";
$result = $conn->query($sql);

// ฟังก์ชันแก้ไขอุปกรณ์
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_equipment'])) {
    $equipment_id = $_POST['equipment_id'];
    $equipment_name = $_POST['equipment_name'];
    $equipment_status = $_POST['equipment_status'];
    $warranty = $_POST['warranty'];
    $warranty_start_date = ($warranty == 'มีประกัน') ? $_POST['warranty_start_date'] : null;
    $warranty_end_date = ($warranty == 'มีประกัน') ? $_POST['warranty_end_date'] : null;

    // ตรวจสอบข้อมูลให้ครบถ้วน
    if (empty($equipment_name) || empty($equipment_status)) {
        echo "<p class='text-danger'>กรุณากรอกข้อมูลให้ครบถ้วน</p>";
    } else {
        // แก้ไขอุปกรณ์ในฐานข้อมูล
        // เปลี่ยนจาก 'ssssssi' เป็น 'sssssii' เพราะบางค่าจะเป็น NULL
        $sql = "UPDATE equipment SET equipment_name = ?, equipment_status = ?, warranty = ?, 
                warranty_start_date = ?, warranty_end_date = ? WHERE equipment_id = ?";
        $stmt = $conn->prepare($sql);
        
        // ตรวจสอบว่า warranty_start_date หรือ warranty_end_date เป็น NULL หรือไม่
        if ($warranty_start_date === null || $warranty_end_date === null) {
            $stmt->bind_param('sssssi', $equipment_name, $equipment_status, $warranty, 
                             $warranty_start_date, $warranty_end_date, $equipment_id);
        } else {
            $stmt->bind_param('ssssss', $equipment_name, $equipment_status, $warranty, 
                             $warranty_start_date, $warranty_end_date, $equipment_id);
        }

        if ($stmt->execute()) {
            echo "<p class='text-success'>อัปเดตอุปกรณ์เรียบร้อยแล้ว</p>";
        } else {
            echo "<p class='text-danger'>เกิดข้อผิดพลาดในการอัปเดตอุปกรณ์</p>";
        }
    }
}

// ฟังก์ชันลบอุปกรณ์
if (isset($_GET['delete'])) {
    $equipment_id = $_GET['delete'];
    $sql = "DELETE FROM equipment WHERE equipment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $equipment_id);

    if ($stmt->execute()) {
        header('Location: admin_manage_equipment.php');
    } else {
        echo "<p class='text-danger'>เกิดข้อผิดพลาดในการลบอุปกรณ์</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>จัดการอุปกรณ์</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Add Font Awesome -->
  <style>
  body {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    font-family: 'Arial', sans-serif;
     margin: 0;
    padding: 0;
    height: 100vh; /* ทำให้ความสูงของ body เท่ากับความสูงของหน้าจอ */
    background-size: cover; /* ให้พื้นหลังขยายเต็มจอ */
    background-position: center; /* จัดตำแหน่งพื้นหลังให้กลาง */
    }
    h1, h3 {
      text-align: center;
      margin-bottom: 20px;
    }
    .card {
      border: none;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }
    .card-header {
      background-color: #e9ecef;
      font-size: 18px;
      font-weight: bold;
      padding: 12px 16px;
      border-bottom: 1px solid #dee2e6;
    }
    table {
      font-size: 14px;
    }
    th, td {
      vertical-align: middle !important;
    }
    .btn-edit {
      margin-right: 5px;
    }
    /* Optional: เพิ่มสไตล์ให้กับลิงก์ลบ */
    .btn-delete {
      font-size: 14px;
      padding: 5px 10px;
    }

    .back-button-container {
            display: flex;
            justify-content: center;  /* จัดให้อยู่กึ่งกลางแนวนอน */
            align-items: center;      /* จัดให้อยู่กึ่งกลางแนวตั้ง */
        }
        .back-button {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 18px;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
        .back-button i {
            margin-right: 8px;
        }


  
  </style>
</head>
<body>
  <div class="container">
    <h1 class="mb-4">จัดการอุปกรณ์</h1>
    
    <!-- แสดงรายการอุปกรณ์ทั้งหมด -->
  <div class="card">
  <div class="card-header">
    <h3 class="mb-0"><i class="fas fa-cogs"></i> รายการอุปกรณ์ทั้งหมด</h3>
  </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="table-light">
            <tr>
              <th><i class="fas fa-desktop"></i> ชื่ออุปกรณ์</th>
              <th><i class="fas fa-cogs"></i> สถานะ</th>
              <th><i class="fas fa-calendar-alt"></i> ระยะเวลารับประกัน</th>
              <th><i class="fas fa-calendar-day"></i> วันที่เริ่มรับประกัน</th>
              <th><i class="fas fa-calendar-xmark"></i> วันที่สิ้นสุดรับประกัน</th>
              <th><i class="fas fa-user"></i> ผู้ใช้</th>
              <th><i class="fas fa-cogs"></i> จัดการ</th>
            </tr>
          </thead>
            <tbody>
              <?php
              if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      echo "<tr>
                              <td>" . htmlspecialchars($row['equipment_name'] ?? '', ENT_QUOTES) . "</td>
                              <td>" . htmlspecialchars($row['equipment_status'] ?? '', ENT_QUOTES) . "</td>
                              <td>" . htmlspecialchars($row['warranty'] ?? '', ENT_QUOTES) . "</td>
                              <td>" . htmlspecialchars($row['warranty_start_date'] ?? '', ENT_QUOTES) . "</td>
                              <td>" . htmlspecialchars($row['warranty_end_date'] ?? '', ENT_QUOTES) . "</td>
                              <td>" . htmlspecialchars($row['username'] ?? '', ENT_QUOTES) . "</td>
                              <td>
                                <button class='btn btn-sm btn-primary btn-edit' onclick='openEditModal({$row['equipment_id']}, \"{$row['equipment_name']}\", \"{$row['equipment_status']}\", \"{$row['warranty']}\", \"{$row['warranty_start_date']}\", \"{$row['warranty_end_date']}\")'>
                                  <i class='fas fa-edit'></i> แก้ไข
                                </button>
                                <a href='?delete={$row['equipment_id']}' class='btn btn-sm btn-danger btn-delete' onclick='return confirm(\"คุณต้องการลบอุปกรณ์นี้หรือไม่?\")'>
                                  <i class='fas fa-trash-alt'></i> ลบ
                                </a>
                              </td>
                          </tr>";
                  }
              } else {
                  echo "<tr><td colspan='7' class='text-center'>ยังไม่มีอุปกรณ์ในระบบ</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Modal สำหรับแก้ไขข้อมูลอุปกรณ์ -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">แก้ไขอุปกรณ์</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="admin_manage_equipment.php">
            <div class="modal-body">
              <input type="hidden" name="equipment_id" id="edit_equipment_id">
              <div class="mb-3">
                <label for="edit_equipment_name" class="form-label">ชื่ออุปกรณ์:</label>
                <input type="text" class="form-control" name="equipment_name" id="edit_equipment_name" required>
              </div>
              <div class="mb-3">
                <label for="edit_equipment_status" class="form-label">สถานะอุปกรณ์:</label>
                <select class="form-select" name="equipment_status" id="edit_equipment_status" required>
                  <option value="ใช้งานปกติ">ใช้งานปกติ</option>
                  <option value="เสีย">เสีย</option>
                  <option value="ซ่อมอยู่">ซ่อมอยู่</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="edit_warranty" class="form-label">ระยะเวลารับประกัน:</label>
                <select class="form-select" name="warranty" id="edit_warranty" required onchange="toggleWarrantyFields()">
                  <option value="มีประกัน">มีประกัน</option>
                  <option value="ไม่มีประกัน">ไม่มีประกัน</option>
                </select>
              </div>
              <div id="warrantyFields" style="display: none;">
                <div class="mb-3">
                  <label for="edit_warranty_start_date" class="form-label">วันที่เริ่มรับประกัน:</label>
                  <input type="date" class="form-control" name="warranty_start_date" id="edit_warranty_start_date">
                </div>
                <div class="mb-3">
                  <label for="edit_warranty_end_date" class="form-label">วันที่สิ้นสุดรับประกัน:</label>
                  <input type="date" class="form-control" name="warranty_end_date" id="edit_warranty_end_date">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
              <button type="submit" name="edit_equipment" class="btn btn-primary">อัปเดตอุปกรณ์</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  
  </div>
  <div class="back-button-container">
    <a href="javascript:history.back()" class="back-button">
      <i class="fas fa-arrow-left"></i> ย้อนกลับ
    </a>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function openEditModal(id, name, status, warranty, start_date, end_date) {
      document.getElementById('edit_equipment_id').value = id;
      document.getElementById('edit_equipment_name').value = name;
      document.getElementById('edit_equipment_status').value = status;
      document.getElementById('edit_warranty').value = warranty;
      
      if (warranty === "มีประกัน") {
        document.getElementById('warrantyFields').style.display = 'block';
        document.getElementById('edit_warranty_start_date').value = start_date;
        document.getElementById('edit_warranty_end_date').value = end_date;
      } else {
        document.getElementById('warrantyFields').style.display = 'none';
        document.getElementById('edit_warranty_start_date').value = '';
        document.getElementById('edit_warranty_end_date').value = '';
      }
      
      var editModal = new bootstrap.Modal(document.getElementById('editModal'));
      editModal.show();
    }
    
    function toggleWarrantyFields() {
      var warranty = document.getElementById('edit_warranty').value;
      if (warranty === 'มีประกัน') {
        document.getElementById('warrantyFields').style.display = 'block';
      } else {
        document.getElementById('warrantyFields').style.display = 'none';
      }
    }
    
  </script>
</body>
</html>
