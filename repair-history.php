<?php
// เริ่ม session
session_start();

// เชื่อมต่อฐานข้อมูล
require_once('config.php');

// ตรวจสอบว่าแอดมินล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ตรวจสอบการค้นหา
$search_query = '';

/*
 * แก้ไข SQL Query:
 * 1. SELECT คอลัมน์ที่ต้องการจาก repairs (r)
 * 2. SELECT c.name AS customer_name จาก customer (c)
 * 3. ใช้ LEFT JOIN เพื่อเชื่อม repairs (r) กับ users (u) โดยใช้ r.username = u.username
 * 4. ใช้ LEFT JOIN ต่อเพื่อเชื่อม users (u) กับ customer (c) โดยใช้ u.user_id = c.user_id
*/
$sql_select = "SELECT
                    r.id,
                    r.username,
                    r.issue_title,
                    r.issue_description,
                    r.reported_date,
                    r.completed_date,
                    r.status,
                    r.price,
                    c.name AS customer_name
                FROM
                    repairs r
                LEFT JOIN
                    users u ON r.username = u.username
                LEFT JOIN
                    customer c ON u.user_id = c.user_id";

$sql_where = "";
$params = [];
$types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    // ค้นหาจาก id, ชื่อลูกค้า (c.name), หัวข้อปัญหา, สถานะ
    $sql_where = " WHERE r.id LIKE ? OR c.name LIKE ? OR r.issue_title LIKE ? OR r.status LIKE ?";
    $search_param = '%' . $search_query . '%';
    $params = [$search_param, $search_param, $search_param, $search_param];
    $types = "ssss";
}

// รวม SQL query
$sql = $sql_select . $sql_where;

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    // ตรวจสอบข้อผิดพลาดในการ prepare SQL
    // ใน Production ควร log error แทนการ die()
    error_log("Error preparing statement: " . $conn->error . " | SQL: " . $sql); // Log a more detailed error
    die("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ");
}

// Bind parameters ถ้ามีการค้นหา
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
if($stmt->errno){
     // ใน Production ควร log error แทนการ die()
     error_log("Error executing statement: " . $stmt->error);
     die("เกิดข้อผิดพลาดในการประมวลผลคำสั่ง SQL กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ");
}

$result = $stmt->get_result();
if ($result === false) {
     // ใน Production ควร log error แทนการ die()
    error_log("Error getting result set: " . $stmt->error);
    die("เกิดข้อผิดพลาดในการดึงข้อมูล กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ");
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ประวัติการซ่อม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet"> <style>
    body {
        /* font-family: 'Poppins', sans-serif; */
        font-family: 'Sarabun', sans-serif; /* เปลี่ยน Font */
        background: #f4f7f6; /* เปลี่ยนพื้นหลังเป็นสีอ่อน */
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-bottom: 30px;
    }

    header {
        padding: 20px 30px; /* ลด Padding */
        text-align: center;
        color: white;
        background-color: #4a69bd; /* เปลี่ยนสี Header */
        width: 100%;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* เพิ่มเงา */
    }

    header h1 {
        font-size: 28px; /* ลดขนาด Font */
        font-weight: 700;
        margin: 0;
    }
     header h1 i {
         margin-right: 10px;
     }

    .table-container {
        width: 95%; /* เพิ่มความกว้าง */
        max-width: 1400px; /* เพิ่ม Max Width */
        background: #ffffff; /* พื้นหลังสีขาว */
        padding: 30px;
        border-radius: 8px; /* ลดความมน */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); /* ปรับเงา */
        border: 1px solid #e0e0e0; /* เพิ่มเส้นขอบ */
    }

    .action-bar { /* สร้าง container สำหรับปุ่มกลับและค้นหา */
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap; /* ให้ขึ้นบรรทัดใหม่ในจอเล็ก */
        gap: 15px; /* ระยะห่างระหว่าง item */
    }


    .search-bar {
        text-align: right; /* จัดชิดขวา */
        flex-grow: 1; /* ให้กินพื้นที่ที่เหลือ */
        min-width: 300px; /* กำหนดความกว้างขั้นต่ำ */
    }

    .search-bar form {
        display: flex;
        justify-content: flex-end; /* จัดชิดขวา */
        gap: 5px;
    }

    .search-bar input {
        width: auto; /* ปรับความกว้างอัตโนมัติ */
        max-width: 400px; /* กำหนดความกว้างสูงสุด */
        padding: 8px 12px;
        font-size: 15px; /* ปรับ font size */
        border-radius: 5px;
        border: 1px solid #ccc;
        display: inline-block;
        vertical-align: middle;
        flex-grow: 1; /* ให้ input ยืดได้ */
    }

    .search-bar button {
        background-color: #5d8bf4; /* เปลี่ยนสีปุ่ม */
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        transition: background-color 0.3s;
        vertical-align: middle;
        cursor: pointer;
        font-size: 15px; /* ปรับ font size */
        white-space: nowrap; /* ไม่ให้ปุ่มขึ้นบรรทัดใหม่ */
    }

    .search-bar button:hover {
        background-color: #4a6fcc; /* สีเข้มขึ้น */
    }

    .btn-back {
        background-color: #6c757d; /* สีเทา */
        color: white;
        padding: 9px 18px; /* ปรับขนาด */
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, box-shadow 0.3s;
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
        border: none;
        white-space: nowrap; /* ไม่ให้ปุ่มขึ้นบรรทัดใหม่ */
    }

    .btn-back:hover {
        background-color: #5a6268;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-back i {
        margin-right: 5px;
    }


    table thead {
        background-color: #e9ecef; /* สีเทาอ่อน */
        color: #495057; /* สีเทาเข้ม */
        font-size: 16px; /* ปรับขนาด font */
        text-align: center;
        font-weight: 500; /* ตัวหนาปานกลาง */
        border-bottom: 2px solid #dee2e6;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px; /* ลดระยะห่าง */
    }

    th, td {
        padding: 10px 12px; /* ปรับ padding */
        text-align: left;
        border-bottom: 1px solid #dee2e6; /* สีเส้นอ่อนลง */
        vertical-align: middle;
        font-size: 15px; /* ปรับขนาด font */
    }

    td {
        background-color: #fff;
        color: #333;
    }

    td:first-child { text-align: center; } /* รหัสกลาง */
    th:first-child { text-align: center; }
    td:nth-last-child(1), th:nth-last-child(1) { text-align: center; } /* พิมพ์ กลาง */
    td:nth-last-child(2), th:nth-last-child(2) { text-align: right; } /* ราคา ขวา */
    td:nth-last-child(3), th:nth-last-child(3) { text-align: center; } /* สถานะ กลาง */
    td:nth-last-child(4), th:nth-last-child(4) { text-align: center; } /* วันที่เสร็จ กลาง */
    td:nth-last-child(5), th:nth-last-child(5) { text-align: center; } /* วันที่แจ้ง กลาง */


    .btn-print {
        padding: 5px 12px; /* ลดขนาดปุ่ม */
        border-radius: 5px;
        text-decoration: none;
        font-weight: normal; /* ลดความหนา */
        transition: 0.3s;
        border: none;
        color: white;
        display: inline-block;
        text-align: center;
        font-size: 14px; /* ลดขนาด font */
        background-color: #28a745; /* สีเขียว */
        line-height: 1.5;
    }

    .btn-print:hover {
        background-color: #218838; /* สีเขียวเข้ม */
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .btn-print i {
        margin-right: 4px;
    }


    tbody tr:hover td {
        background-color: #f8f9fa; /* สีเทาอ่อนมากเมื่อ hover */
    }

    .no-data {
        text-align: center;
        color: #6c757d; /* สีเทา */
        font-size: 16px; /* ปรับขนาด */
        padding: 25px;
        background-color: #fff;
    }
    .no-data i {
        margin-right: 8px;
        color: #ffc107; /* สีเหลือง */
    }


    .table-responsive {
         border: 1px solid #dee2e6;
         border-radius: 5px;
         overflow-x: auto; /* ทำให้เลื่อนแนวนอนได้ */
    }
     .table {
         margin-bottom: 0; /* เอา margin-bottom ออกจาก table */
     }

     .status-badge { /* สำหรับตกแต่ง status */
         padding: 4px 8px;
         border-radius: 10px;
         font-size: 13px;
         font-weight: 500;
         color: #fff;
     }
     .status-เสร็จสิ้น { background-color: #28a745; } /* เขียว */
     .status-กำลังดำเนินการ { background-color: #007bff; } /* ฟ้า */
     .status-รอดำเนินการ { background-color: #ffc107; color: #333; } /* เหลือง */
     .status-ยกเลิก { background-color: #dc3545; } /* แดง */
     .status-รออะไหล่ { background-color: #fd7e14; } /* ส้ม */
     .status-รอการยืนยันราคา { background-color: #6f42c1; } /* ม่วง */


  </style>
</head>
<body>
  <header>
    <h1><i class="fas fa-history"></i> ประวัติการซ่อมทั้งหมด</h1>
  </header>

  <section class="table-container">
    <div class="action-bar">
        <a href="admin-dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> กลับหน้าหลัก</a>
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="ค้นหา: รหัสซ่อม, ชื่อลูกค้า, ปัญหา, สถานะ...">
                <button type="submit"><i class="fas fa-search"></i> ค้นหา</button>
            </form>
        </div>
    </div>


    <div class="table-responsive">
        <table class="table table-hover"> <thead class="thead-light">
            <tr>
              <th><i class="fas fa-hashtag"></i> รหัสการซ่อม</th>
              <th><i class="fas fa-user"></i> ชื่อลูกค้า</th>
              <th><i class="fas fa-tag"></i></th> <th><i class="fas fa-comment-dots"></i> รายละเอียดปัญหา</th>
              <th><i class="fas fa-calendar-alt"></i> วันที่แจ้ง</th>
              <th><i class="fas fa-calendar-check"></i> วันที่เสร็จ</th>
              <th><i class="fas fa-tasks"></i> สถานะ</th>
              <th><i class="fas fa-coins"></i> ราคา (บาท)</th>
              <th><i class="fas fa-print"></i> พิมพ์</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                // สร้าง class สำหรับ status badge
                $status_class = 'status-' . str_replace(' ', '-', htmlspecialchars($row['status']));
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                // แสดงชื่อลูกค้า ถ้าไม่มีให้แสดง username แทน หรือ 'N/A'
                echo "<td>" . htmlspecialchars($row['customer_name'] ?? ($row['username'] ?? 'N/A')) . "</td>";
                echo "<td>" . htmlspecialchars($row['issue_title']) . "</td>"; // แสดง issue_title
                echo "<td>" . nl2br(htmlspecialchars($row['issue_description'])) . "</td>"; // ใช้ nl2br เผื่อมีขึ้นบรรทัดใหม่
                echo "<td>" . (!empty($row['reported_date']) ? htmlspecialchars(date('d/m/Y', strtotime($row['reported_date']))) : '-') . "</td>"; // Format วันที่สั้นๆ
                echo "<td>" . (!empty($row['completed_date']) ? htmlspecialchars(date('d/m/Y', strtotime($row['completed_date']))) : '-') . "</td>"; // Format วันที่สั้นๆ
                // แสดง status พร้อม badge
                echo "<td><span class='status-badge " . $status_class . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                echo "<td>" . (isset($row['price']) && is_numeric($row['price']) ? htmlspecialchars(number_format((float)$row['price'], 2)) : '-') . "</td>";
                echo "<td class='action-cell'><a href='receipt.php?service_id=" . $row['id'] . "' class='btn-print' target='_blank' title='พิมพ์ใบเสร็จ'><i class='fas fa-print'></i></a></td>"; // ใส่ title และเอาคำว่าพิมพ์ออก
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='9' class='text-center no-data'><i class='fas fa-info-circle'></i> ไม่มีข้อมูลการซ่อมที่ตรงกับเงื่อนไข</td></tr>"; // เพิ่ม colspan เป็น 9
            }
            // ปิด statement
            $stmt->close();
            // ปิดการเชื่อมต่อฐานข้อมูล
            $conn->close();
            ?>

          </tbody>
        </table>
    </div> </section>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>