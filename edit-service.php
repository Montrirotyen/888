<?php  // หน้าแก้ไขข้อมูลการซ่อม
// เริ่มต้น Session
session_start();

// ตรวจสอบว่า user_id ถูกกำหนดใน session หรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "repair_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$service_id = $_GET['service_id'];
$user_id = $_SESSION['user_id'];

// ดึงข้อมูลการซ่อมจากฐานข้อมูล
$sql = "SELECT * FROM service WHERE service_id = $service_id AND user_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $repair = $result->fetch_assoc();
} else {
    echo "ไม่พบข้อมูลการซ่อมที่คุณต้องการแก้ไข";
    exit();
}

// ถ้ามีการส่งฟอร์มเพื่ออัปเดตข้อมูล
if (isset($_POST['update'])) {
    $equipment_id = $_POST['equipment_id'];
    $issue = $_POST['issue'];
    $service_date_in = $_POST['service_date_in'];

    $update_sql = "UPDATE service SET equipment_id = '$equipment_id', issue = '$issue', service_date_in = '$service_date_in' WHERE service_id = $service_id";
    
    if ($conn->query($update_sql) === TRUE) {
        $success_message = "ข้อมูลการซ่อมได้รับการอัปเดตเรียบร้อยแล้ว!";
    } else {
        $error_message = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลการซ่อม</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to bottom, #87CEEB, #6a5acd);
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;

        }

        
        header h2 {
            margin-top: 0;
            font-size: 2rem;
        }
        .container {
            width: 80%;
            margin: 30px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .container:hover {
            transform: translateY(-10px);
        }
        h2 {
            text-align: center;
            color: #34495e;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }
        form {
            width: 70%;
            margin: 0 auto;
        }
        label {
            font-size: 1.1rem;
            margin: 10px 0;
            display: block;
            color: #34495e;
        }
        input[type="text"], input[type="date"] {
            width: 100%;
            padding: 12px 20px;
            font-size: 1.1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        input[type="text"]:focus, input[type="date"]:focus {
            border: 2px solid #009688;
            outline: none;
        }
        button {
            padding: 12px 20px;
            background-color: #009688;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s, transform 0.3s;
        }
        button:hover {
            background-color: #00796b;
            transform: scale(1.05);
        }
        .message {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        .message a {
            color: #009688;
            text-decoration: none;
        }
        .message a:hover {
            text-decoration: underline;
        }
        .back-button {
            text-align: center;
            margin-top: 30px;
        }
        .back-button a {
            padding: 14px 30px;
            background-color: #009688;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.2rem;
            transition: background-color 0.3s, transform 0.3s;
        }
        .back-button a:hover {
            background-color: #00796b;
            transform: scale(1.05);
        }
        .icon {
            margin-right: 8px;
        }

        /* Responsive design */
        @media screen and (max-width: 768px) {
            .container {
                padding: 15px;
            }
            form {
                width: 100%;
            }
            input[type="text"], input[type="date"], button {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>


<div class="container">
    <?php if (isset($success_message)): ?>
        <div class="message" style="color: green;">
            <?php echo $success_message; ?>
            <a href="user-dashboard.php" class="back-button"><i class="fas fa-arrow-left"></i> กลับไปยังแดชบอร์ด</a>
        </div>
    <?php elseif (isset($error_message)): ?>
        <div class="message" style="color: red;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <header>
    <h2><i class="fas fa-tools"></i> แก้ไขข้อมูลการซ่อม</h2>
    </header>

    <form method="post" action="">
        <label for="equipment_id">รหัสอุปกรณ์</label>
        <input type="text" name="equipment_id" value="<?php echo htmlspecialchars($repair['equipment_id']); ?>" required>

        <label for="issue">อาการชำรุด</label>
        <input type="text" name="issue" value="<?php echo htmlspecialchars($repair['issue']); ?>" required>

        <label for="service_date_in">วันที่รับซ่อม</label>
        <input type="date" name="service_date_in" value="<?php echo htmlspecialchars($repair['service_date_in']); ?>" required>

        <button type="submit" name="update"><i class="fas fa-save icon"></i> บันทึกการแก้ไข</button>
    </form>

    <div class="back-button">
        <a href="check-repair-status.php"><i class="fas fa-arrow-left"></i> ย้อนกลับ</a>
    </div>
</div>

</body>
</html>

