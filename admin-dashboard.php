<?php  // หน้าผู้ดูแลระบบ
// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";  // ชื่อผู้ใช้ MySQL
$password = "";      // รหัสผ่าน MySQL
$dbname = "repair_system";  // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงจำนวนผู้ใช้งานทั้งหมด
$user_count_sql = "SELECT COUNT(*) as total_users FROM users";
$user_count_result = $conn->query($user_count_sql);
$user_count = $user_count_result->fetch_assoc()['total_users'];

// ดึงจำนวนการแจ้งซ่อมที่ยังไม่เสร็จสิ้นและยังไม่ได้ถูกยกเลิก และไม่รวมสถานะ 'รอการยืนยันราคา'
$service_count_sql = "SELECT COUNT(*) as total_services 
                      FROM service 
                      WHERE service_status != 'เสร็จสิ้น' 
                      AND service_status != 'ยกเลิก' 
                      AND service_status != 'รอการยืนยันราคา'";

$service_count_result = $conn->query($service_count_sql);
$service_count = $service_count_result->fetch_assoc()['total_services'];

// ดึงจำนวนการซ่อมทั้งหมด
$repair_count_sql = "SELECT COUNT(*) as total_repairs FROM repairs";
$repair_result = $conn->query($repair_count_sql);

// ดึงจำนวนการแจ้งซ่อมที่มีสถานะ "รอดำเนินการ"
$pending_service_sql = "SELECT COUNT(*) as total_pending_service FROM service WHERE service_status = 'รอดำเนินการ'";
$pending_service_result = $conn->query($pending_service_sql);

// ตรวจสอบผลลัพธ์และกำหนดค่าให้กับตัวแปร
$pending_service_count = ($pending_service_result->num_rows > 0) ? $pending_service_result->fetch_assoc()['total_pending_service'] : 0;
// ตรวจสอบผลลัพธ์การดึงข้อมูลการซ่อม
if ($repair_result) {
    if ($repair_result->num_rows > 0) {
        $repair_count = $repair_result->fetch_assoc()['total_repairs'];
    } else {
        $repair_count = 0; // กำหนดค่าเริ่มต้นเมื่อไม่มีข้อมูล
    }
} else {
    $repair_count = 0; // กำหนดค่าเริ่มต้นเมื่อเกิดข้อผิดพลาดในการ query
}

// ดึงจำนวนการซ่อมที่เสร็จสิ้น
$completed_repair_sql = "SELECT COUNT(*) as total_completed_repairs FROM repairs WHERE status = 'เสร็จสิ้น'";
$completed_repair_result = $conn->query($completed_repair_sql);
$completed_repair_count = $completed_repair_result->fetch_assoc()['total_completed_repairs'];

// ดึงจำนวนการแจ้งซ่อมที่ "รอการยืนยันราคา"
$waiting_for_price_sql = "SELECT COUNT(*) as total_waiting_for_price FROM service WHERE service_status = 'รอการยืนยันราคา'";
$waiting_for_price_result = $conn->query($waiting_for_price_sql);
$waiting_for_price_count = $waiting_for_price_result->fetch_assoc()['total_waiting_for_price'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดผู้ดูแลระบบ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ทั่วไป */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background: linear-gradient(to bottom, #87CEEB, #6a5acd);
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, #2d3e50, #1a252f); /* สีเข้มแบบ Gradient */
            color: white;
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .sidebar:hover {
            width: 280px;
        }

        .sidebar h2 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 50px;
            color: #ecf0f1;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Navigation */
        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 12px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        /* เอฟเฟกต์ Hover */
        .sidebar ul li a:hover {
            background: linear-gradient(135deg, #1abc9c, #16a085);
            box-shadow: 0 0 15px rgba(26, 188, 156, 0.5);
            transform: translateX(5px);
        }

        .sidebar ul li a i {
            margin-right: 15px;
            font-size: 20px;
        }

        /* เพิ่มเอฟเฟกต์ไอคอน */
        .sidebar ul li a:hover i {
            transform: scale(1.2);
            transition: transform 0.3s ease;
        }
        .sidebar ul li.logout a {
            background-color: #e74c3c; /* สีแดง */
            color: white;
            font-weight: bold;
        }

        .sidebar ul li.logout a:hover {
            background-color: #c0392b; /* สีแดงเข้มขึ้นเมื่อ hover */
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 50px;
            width: 100%;
            min-height: 100vh;
            background: linear-gradient(to bottom, #87CEEB, #6a5acd);
            transition: margin-left 0.3s ease;
            color: #fff;
        }

        .main-content h2 {
            font-size: 36px;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Card Styles */
        .card {
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            text-align: center;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .card h3 {
            color: #34495e;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .card p {
            font-size: 20px;
            color:rgb(247, 101, 101);
            margin-bottom: 20px;
        }

        /* ปรับสีปุ่มให้เข้ากับ Sidebar */
        .card button {
            background: linear-gradient(135deg, #16a085, #1abc9c);
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .card button a {
            color: white;
            text-decoration: none;
        }

        .card button:hover {
            background: linear-gradient(135deg, #1abc9c, #16a085);
            box-shadow: 0 0 10px rgba(26, 188, 156, 0.5);
        }

        /* Grid Layout for Cards */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
            }

            .sidebar h2 {
                font-size: 24px;
            }

            .sidebar ul li a {
                font-size: 16px;
            }
        }

    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>ผู้ดูแลระบบ</h2>
        <ul>
            <li><a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> แดชบอร์ด</a></li>
            <li><a href="user_list.php"><i class="fas fa-users"></i> จัดการผู้ใช้งาน</a></li>
            <li><a href="add_equipment.php"><i class="fas fa-plus-circle"></i> เพิ่มอุปกรณ์</a></li>
            <li><a href="admin_manage_equipment.php"><i class="fas fa-tools"></i> จัดการอุปกรณ์</a></li>
            <li><a href="change-status.php"><i class="fas fa-sync-alt"></i> อัพเดดแจ้งซ่อม</a></li>
            <li><a href="search-repairs.php"><i class="fas fa-hammer"></i> แก้ไขการแจ้งซ่อม</a></li>
            <li><a href="report_service_admin.php"><i class="fas fa-user-plus"></i> ยืนยันการแจ้งซ่อม</a></li>
            <li><a href="spare_parts_inventory.php"><i class="fas fa-boxes"></i> คลังอะไหล่</a></li>
            <li class="logout"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>ผู้ดูแลระบบ</h2>

        <!-- ข้อมูลสรุป -->
        <div class="card-container">
            <div class="card">
                <h3><i class="fas fa-user"></i> จำนวนผู้ใช้งาน</h3>
                <p><?php echo $user_count; ?> รายการ</p>
                <button><a href="user_list.php">ดูข้อมูลผู้ใช้งาน</a></button>
            </div>
            <div class="card">
                <h3><i class="fas fa-bell"></i> จำนวนการแจ้งซ่อม</h3>
                <p><?php echo $service_count; ?> รายการ</p>
                <button><a href="change-status.php">เปลี่ยนสถานะการซ่อม</a></button>
            </div>
            <div class="card">
                <h3><i class="fas fa-history"></i> ประวัติการซ่อม</h3>
                <p><?php echo $repair_count; ?> รายการ</p>
                <button><a href="repair-history.php">ดูประวัติการซ่อม</a></button>
            </div>
            <div class="card">
                <h3><i class="fas fa-clock"></i> การแจ้งซ่อมรอการยืนยันราคา</h3>
                <p><?php echo $waiting_for_price_count; ?> รายการ</p>
                <button><a href="change-status.php">ยืนยันราคา</a></button>
            </div>
        </div>
    </div>

</body>
</html>
