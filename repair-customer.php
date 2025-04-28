<?php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// ดึง username ของผู้ใช้ปัจจุบันจาก session
$username = $_SESSION['username'];

// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "repair_system";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// รับค่าค้นหา (ถ้ามี)
$search_query = isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '';

// สร้างคำสั่ง SQL
$sql = "SELECT id, issue_title, issue_description, reported_date, status, completed_date 
        FROM repairs 
        WHERE username = ?";

if (!empty($search_query)) {
    $sql .= " AND (issue_title LIKE ? OR issue_description LIKE ?)";
}

$sql .= " ORDER BY reported_date DESC";

$stmt = $conn->prepare($sql);

if (!empty($search_query)) {
    $search_query_param = "%" . $search_query . "%";
    $stmt->bind_param("sss", $username, $search_query_param, $search_query_param);
} else {
    $stmt->bind_param("s", $username);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการซ่อม</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            margin: 0;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background: linear-gradient(135deg, #80d0c7, #9fc6ff);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            padding-top: 20px;
            z-index: 100;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            text-align: center;
            margin: 15px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #0078AA;
            display: block;
            padding: 12px;
            font-size: 18px;
            border-radius: 10px;
            transition: background 0.3s, transform 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #0078AA;
            color: white;
            transform: scale(1.05);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
        }

        h2 {
            color: #2C3E50;
        }

        /* Search Box */
        .search-box {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-box input {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            background-color: #f1f1f1;
            font-size: 16px;
        }

        .search-box button {
            margin-left: 10px;
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            background-color: #3498db;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-box button:hover {
            background-color: #2980b9;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        table tr:hover {
            background-color: #f5f5f5;
        }

        /* Status Styles */
        .status-pending {
            background-color: #f39c12;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .status-completed {
            background-color: #2ecc71;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .status-cancelled {
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
        }

        /* Back Button */
        .back-button {
            margin-top: 20px;
        }

        .back-button button {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            background-color: #555;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }

        .back-button button:hover {
            background-color: #333;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li><a href="user-dashboard.php">🏠 เมนูหลัก</a></li>
            <li><a href="customer_list.php">👤 ข้อมูลส่วนตัว</a></li>
            <li><a href="view-repair.php">🔧 แจ้งซ่อม</a></li>
            <li><a href="check-repair-status.php">🔍 ดูสถานะการซ่อม</a></li>
            <li><a href="repair-history.php">🛠️ ประวัติการซ่อม</a></li>
            <li><a href="sms.php">📞 ติดต่อเรา</a></li>
            <li><a href="logout.php">🔒 ออกจากระบบ</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2><i class="fa fa-history"></i> ประวัติการซ่อม</h2>

        <!-- Search Box -->
        <form class="search-box" action="" method="post">
            <input type="text" name="search" placeholder="ค้นหาประวัติการซ่อม..." value="<?php echo $search_query; ?>">
            <button type="submit"><i class="fa fa-search"></i> ค้นหา</button>
        </form>

        <!-- Repair History Table -->
        <table>
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>อุปกรณ์</th>
                    <th>รายละเอียด</th>
                    <th>วันที่แจ้ง</th>
                    <th>สถานะ</th>
                    <th>วันที่เสร็จสิ้น</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_description']); ?></td>
                            <td><?php echo htmlspecialchars($row['reported_date']); ?></td>
                            <td><span class="status-<?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['completed_date'] ?: '-'); ?></td>
                        </tr>
                <?php } } else { ?>
                    <tr><td colspan="6" style="text-align: center;">ไม่พบข้อมูล</td></tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Back Button -->
        <div class="back-button">
            <button onclick="window.history.back();">⬅️ ย้อนกลับ</button>
        </div>
    </div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
