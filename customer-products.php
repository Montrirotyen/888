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

// ดึงข้อมูลสินค้าที่ลูกค้าลงทะเบียน
$equip_stmt = $conn->prepare("SELECT equipment_name, warranty_start_date, warranty_end_date FROM equipment WHERE user_id = ?");
$equip_stmt->bind_param("i", $user_id);
$equip_stmt->execute();
$equipResult = $equip_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สินค้าที่คุณได้ลงทะเบียนซื้อ</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ใช้ฟอนต์ 'Prompt' และสีพื้นหลังที่เหมือนหน้าแรก */
        body {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            background-size: cover;
            background-position: center;
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

        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background: #96C5F7;
            color: #0E0C0C;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>สินค้าที่คุณได้ลงทะเบียนซื้อ</h1>
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
            <li><a href="customer-products.php">📦 สินค้าที่คุณได้ลงทะเบียนซื้อ</a></li> <!-- เพิ่มเมนู -->
        </ul>
    </div>

    <div class="content">
        <?php if ($equipResult->num_rows > 0): ?>
        
            <table>
                <thead>
                    <tr>
                        <th>สินค้า</th>
                        <th>วันเริ่มประกัน</th>
                        <th>วันหมดประกัน</th>
                        <th>สถานะประกัน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($eq = $equipResult->fetch_assoc()): 
                        $end = new DateTime($eq['warranty_end_date']);
                        $today = new DateTime();
                        $diff = $today->diff($end);
                        $status = ($today <= $end)
                            ? "เหลืออีก {$diff->format('%a')} วัน"
                            : "หมดประกันแล้ว";
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($eq['equipment_name']) ?></td>
                        <td><?= htmlspecialchars($eq['warranty_start_date']) ?></td>
                        <td><?= htmlspecialchars($eq['warranty_end_date']) ?></td>
                        <td><?= $status ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p>คุณยังไม่ได้ลงทะเบียนสินค้าใดๆ</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$equip_stmt->close();
$conn->close();
?>
