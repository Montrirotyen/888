<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include('config.php');

$user_id = $_SESSION['user_id']; // user_id ของผู้ใช้ที่ล็อกอิน

// เช็คว่าได้รับค่าจากการแก้ไขข้อมูลหรือไม่
if (isset($_GET['edit'])) {
    $customer_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT customer.*, users.username 
                            FROM Customer 
                            INNER JOIN Users ON Customer.user_id = Users.user_id 
                            WHERE Customer.customer_id = ? AND Users.user_id = ?");
    $stmt->bind_param("ii", $customer_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
    } else {
        echo "<script>alert('คุณไม่มีสิทธิ์แก้ไขข้อมูลนี้!'); window.location='customer_list.php';</script>";
        exit;
    }
    $stmt->close();
}

// อัพเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $tel = $_POST['tel'];

    $stmt = $conn->prepare("UPDATE Customer SET name = ?, email = ?, address = ?, tel = ? 
                            WHERE customer_id = ? AND user_id = ?");
    $stmt->bind_param("ssssii", $name, $email, $address, $tel, $customer_id, $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('ข้อมูลได้ถูกอัปเดต'); window.location='customer_list.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// ดึงข้อมูลของผู้ใช้ที่ล็อกอิน
$sql = "SELECT Users.username, Customer.customer_id, Customer.name, Customer.email, Customer.address, Customer.tel 
        FROM Customer 
        INNER JOIN Users ON Customer.user_id = Users.user_id 
        WHERE Users.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลโปรไฟล์</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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


        .sidebar {
                width: 220px;
                background: linear-gradient(135deg, #80d0c7 0%, rgb(159, 198, 255) 100%);
                height: 100vh; /* ความสูงเต็มหน้าจอ */
                padding-top: 20px;
                position: fixed; /* ให้แถบข้างติดอยู่ที่ข้างๆ */
                top: 0; /* เริ่มจากด้านบน */
                left: 0; /* เริ่มจากด้านซ้าย */
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                z-index: 100; /* ทำให้แถบข้างอยู่เหนือเนื้อหาหลัก */
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

            /* กล่องโปรไฟล์กึ่งโปร่งแสง */
            .profile-container {
                max-width: 500px;
                margin: 100px auto;
                padding: 30px;
                background: rgba(255, 255, 255, 0.9); /* ปรับโปร่งแสงให้เนียนขึ้น */
                border-radius: 15px;
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
                backdrop-filter: blur(15px); /* เบลอพื้นหลังให้เนียนมากขึ้น */
            }

            /* หัวข้อ */
            .profile-container h2 {
                text-align: center;
                margin-bottom: 20px;
                color: #6a5acd; /* เปลี่ยนเป็นสีม่วงเข้ม */
                font-weight: bold;
            }

            /* ปรับแต่งช่องกรอก */
            .form-control {
                border-radius: 25px;
                padding-left: 40px;
                border-color: #b0c4de; /* สีฟ้าอ่อนที่เข้ากับพื้นหลัง */
            }

            .form-group {
                position: relative;
                margin-bottom: 20px;
            }

            .form-control:focus {
                box-shadow: none;
                border-color: #6a5acd; /* สีม่วงเข้ม */
            }

            /* ไอคอนข้างช่องกรอก */
            .form-icon {
                position: absolute;
                top: 50%;
                left: 15px;
                transform: translateY(-50%);
                color: #6a5acd; /* สีม่วงเข้ม */
            }

            /* ปุ่มหลัก */
            .btn-primary {
                width: 100%;
                padding: 12px;
                border-radius: 25px;
                background: linear-gradient(45deg, #6a5acd, #a0c4ff); /* ไล่สีฟ้าและม่วง */
                border: none;
                font-weight: bold;
                transition: 0.3s;
            }

            .btn-primary:hover {
                background: linear-gradient(45deg, #5e4b8b, #6a5acd); /* ปรับสีเมื่อ Hover */
                transform: scale(1.05);
            }

            .back-button {
                text-align: center;
                margin-top: 20px;
            }

            .back-button a {
                text-decoration: none;
                color: #6a5acd; /* สีม่วงเข้ม */
                font-weight: bold;
                padding: 10px 20px;
                border: 2px solid #6a5acd;
                border-radius: 25px;
                transition: 0.3s;
            }

            .back-button a:hover {
                background-color: #6a5acd;
                color: white;
            }
    </style>
</head>
<body>
<div class="navbar">
    </div>
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

<div class="profile-container">
    <h2><i class="fas fa-edit"></i> แก้ไขข้อมูลโปรไฟล์</h2>

    <div class="profile-columns">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='profile-column'>";
                echo "<h3>ข้อมูลผู้ใช้</h3>";
                echo "<p><i class='fas fa-user'></i><strong> ชื่อผู้ใช้:</strong> {$row['username']}</p>";
                echo "<p><i class='fas fa-user-circle'></i><strong> ชื่อ:</strong> {$row['name']}</p>";
                echo "<p><i class='fas fa-envelope'></i><strong> อีเมล์:</strong> {$row['email']}</p>";
                echo "<p><i class='fas fa-map-marker-alt'></i><strong> ที่อยู่:</strong> {$row['address']}</p>";
                echo "<p><i class='fas fa-phone'></i><strong> เบอร์โทรศัพท์:</strong> {$row['tel']}</p>";
                echo "<p><a href='?edit={$row['customer_id']}'><i class='fas fa-edit'></i> แก้ไขข้อมูล</a></p>";
                echo "</div>";
            }
        } else {
            echo "<div class='profile-column'><p>ไม่พบข้อมูล</p></div>";
        }
        ?>
    </div>

    <?php if (isset($customer)): ?>
        <form method="post">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> ชื่อ:</label>
                <input type="text" name="name" value="<?= $customer['name'] ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> อีเมล์:</label>
                <input type="email" name="email" value="<?= $customer['email'] ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="address"><i class="fas fa-map-marker-alt"></i> ที่อยู่:</label>
                <textarea name="address" rows="4" class="form-control" required><?= $customer['address'] ?></textarea>
            </div>

            <div class="form-group">
                <label for="tel"><i class="fas fa-phone"></i> เบอร์โทรศัพท์:</label>
                <input type="text" name="tel" value="<?= $customer['tel'] ?>" class="form-control" required>
            </div>

            <input type="hidden" name="customer_id" value="<?= $customer['customer_id'] ?>">

            <button type="submit" class="btn-primary">บันทึกการเปลี่ยนแปลง</button>
        </form>
    <?php endif; ?>

</div>

</body>
</html>

