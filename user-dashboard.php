<?php
session_start();

// ตรวจสอบว่าผู้ใช้งานเข้าสู่ระบบหรือยัง
if (!isset($_SESSION['customer_name'])) {
    header("Location: login.php");
    exit;
}

$customer_name = $_SESSION['customer_name']; // ดึงชื่อจาก session

// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "repair_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$search_query = '';

if (isset($_POST['search'])) {
    $search_query = htmlspecialchars($_POST['search']); // ใช้ htmlspecialchars เพื่อป้องกัน XSS
}

// --- เริ่มโค้ดเพิ่ม: ดึงสินค้าที่ลูกค้าลงทะเบียนซื้อ ---
$equip_stmt = $conn->prepare("
    SELECT equipment_name, warranty_start_date, warranty_end_date
    FROM equipment
    WHERE user_id = ?
");
$equip_stmt->bind_param("i", $user_id);
$equip_stmt->execute();
$equipResult = $equip_stmt->get_result();
// --- จบโค้ดเพิ่ม ---

// เตรียมคำสั่ง SQL โดยใช้ prepared statements เพื่อป้องกัน SQL Injection
$sql = "SELECT service.issue, service.service_status, service.service_date_in, equipment.equipment_name 
        FROM service 
        JOIN equipment ON service.equipment_id = equipment.equipment_id 
        WHERE service.user_id = ?";

if ($search_query != '') {
    $sql .= " AND (service.issue LIKE ? OR service.service_id LIKE ?)";
}

$stmt = $conn->prepare($sql);

if ($search_query != '') {
    $search_query_param = "%" . $search_query . "%"; // ใช้ % เพื่อค้นหาเหมือน
    $stmt->bind_param("iss", $user_id, $search_query_param, $search_query_param);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดลูกค้า</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ใช้ฟอนต์ 'Prompt' และสีพื้นหลังที่เหมือนหน้าแรก */
        body {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh; /* ทำให้ความสูงของ body เท่ากับความสูงของหน้าจอ */
            background-size: cover; /* ให้พื้นหลังขยายเต็มจอ */
            background-position: center; /* จัดตำแหน่งพื้นหลังให้กลาง */
        }

    
        /* เมนูนำทาง */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgb(10, 158, 221);
            padding: 15px 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            transition: all 0.3s ease-in-out;
        }

        .navbar h1 {
            color: white;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            transition: transform 0.3s ease;
        }

        .navbar h1:hover {
            transform: scale(1.05);
        }

        /* เมนูย่อย */
        .menu {
            display: flex;
            gap: 15px;
        }

        .menu a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 5px;
            transition: color 0.3s, background-color 0.3s, transform 0.3s ease;
        }

        .menu a:hover {
            color: rgb(253, 253, 253);
            background-color: rgba(113, 244, 253, 0.2);
            transform: translateY(-3px);
        }

        .menu a:active {
            transform: translateY(1px);
            background-color: rgba(255, 221, 0, 0.4);
        }

        /* แถบข้าง */
        .sidebar {
            width: 220px;
            background: linear-gradient(135deg, #80d0c7 0%, rgb(159, 198, 255) 100%);
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .sidebar:hover {
            width: 250px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            padding: 12px 0;
            text-align: center;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #0078AA;
            display: block;
            padding: 15px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            transition: background 0.3s, color 0.3s, transform 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #0078AA;
            color: white;
            transform: scale(1.05);
        }

        .sidebar ul li a:active {
            background-color: #005678;
            transform: scale(0.98);
        }

        /* เนื้อหาหลัก */
        .content {
            margin-left: 220px;
            padding: 20px;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: scale(1.03);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .card img {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .card:hover img {
            transform: rotate(12deg);
        }

        .card h3 {
            color: #0078AA;
            font-size: 20px;
            margin-top: 15px;
        }

        /* การออกแบบส่วน header */
        header {
            color: black; 
            text-align: center;
            padding: 30px 20px;
            position: relative;
            overflow: hidden;
        }

        .welcome-container {
            position: relative;
            z-index: 1;
            animation: fadeIn 1s ease-in-out;
        }

        .welcome-container h1 {
            font-size: 28px; 
            font-weight: bold;
            margin: 0;
            color: black;
            animation: slideInFromLeft 1s ease-out;
        }

        .welcome-container h1 .highlight {
            color:rgb(15, 15, 15);
            font-style: italic;
            transition: color 0.3s ease;
        }

        .welcome-container h1 .highlight:hover {
            color:rgb(15, 15, 15);
            text-decoration: underline;
        }

        .welcome-text {
            font-size: 16px; 
            font-weight: 300;
            color: black;
            margin-top: 8px;
            animation: slideInFromBottom 1s ease-out;
        }

        /* การเคลื่อนไหวเมื่อมีการโฮเวอร์ */
        .welcome-container:hover h1 {
            transform: scale(1.05); 
            transition: transform 0.3s ease;
        }

        /* การเคลื่อนไหวที่เนื้อความ */
        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        @keyframes slideInFromLeft {
            0% {
                transform: translateX(-50px);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInFromBottom {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

    </style>
</head>
<body>
    <div class="navbar">
        <h1>ระบบแจ้งซ่อมคอมพิวเตอร์</h1>
        <div class="menu">
            <a href="#">หน้าหลัก</a>
        </div>
    </div>
    <div class="sidebar">
    <ul>
        <li><a href="user-dashboard.php">🏠 เมนูหลัก</a></li>
        <li><a href="customer_list.php">👤 ข้อมูลส่วนตัว</a></li>
        <li><a href="report_service.php">🔧 แจ้งซ่อม</a></li>
        <li><a href="check-status.php">🔍 ดูสถานะการซ่อม</a></li>
        <li><a href="repair-customer.php">🛠️ ประวัติการซ่อม</a></li>
        <li><a href="sms.php">📞 ติดต่อเรา</a></li>
        <li><a href="logout.php">🔒 ออกจากระบบ</a></li>
        <!-- เพิ่มเมนูนี้ -->
        <li><a href="customer-products.php">📦 สินค้าที่คุณได้ลงทะเบียนซื้อ</a></li>
    </ul>
</div>

    <div class="main-content">
        <header>
            <div class="welcome-container">
                <h1>ยินดีต้อนรับ <span class="highlight"><?= htmlspecialchars($customer_name) ?></span>!</h1>
                <p class="welcome-text">คุณสามารถตรวจสอบสถานะการซ่อมของคุณที่นี่</p>
            </div>
        </header>
        <div class="content">
            <h2>ยินดีต้อนรับสู่ระบบแจ้งซ่อมคอมพิวเตอร์ของเรา</h2>
            <div class="card-container">
                <div class="card"><a href="report_service.php">
                    <img src="https://cdn-icons-png.flaticon.com/512/1055/1055687.png" alt="แจ้งซ่อม">
                    <h3>แจ้งซ่อม</h3>
                </a></div>
                <div class="card">
                    <a href="check-status.php">
                        <img src="https://cdn-icons-png.flaticon.com/512/149/149852.png" alt="ตรวจสอบสถานะ" width="50" height="50">
                        <h3>ตรวจสอบสถานะ</h3>
                </a></div>
                <div class="card"><a href="repair-customer.php">
                    <img src="https://cdn-icons-png.flaticon.com/512/1828/1828640.png" alt="ประวัติการซ่อม">
                    <h3>ประวัติการซ่อม</h3>
                </a></div>
                <div class="card">
                    <a href="customer_status.php">
                    <img src="https://cdn-icons-png.flaticon.com/512/3233/3233017.png" alt="เครื่องมือซ่อม" width="50" height="50">
                    <h3>ยืนยันการซ่อม</h3>
                </a></div>
            </div>

           

        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$equip_stmt->close();
$conn->close();
?>
