<?php
session_start();
require_once('config.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าเป็นผู้ใช้ที่มีสิทธิ์ (customer) หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header('Location: login.php');
    exit;
}

// ตรวจสอบว่า service_id ถูกส่งมาหรือไม่
if (isset($_GET['service_id'])) {
    $service_id = $_GET['service_id'];
    $user_id = $_SESSION['user_id'];  // user_id จาก session ของผู้ใช้งาน

    // เช็คข้อมูลการซ่อมที่เกี่ยวข้องกับ service_id และ user_id
    $sql = "SELECT service_id, price, service_status 
            FROM service 
            WHERE service_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $service_id, $user_id);  // ใช้การ bind parameter
    $stmt->execute();
    $stmt->store_result();

    // ตรวจสอบว่าเจอข้อมูลหรือไม่
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($service_id, $price, $service_status);
        $stmt->fetch();
    } else {
        echo "<p class='text-danger'>ไม่พบข้อมูลการซ่อมนี้ หรือคุณไม่สามารถเข้าถึงข้อมูลนี้ได้</p>";
        exit;
    }
} else {
    echo "<p class='text-danger'>ไม่พบข้อมูลการซ่อม</p>";
    exit;
}

// ตรวจสอบการยืนยันราคา
if (isset($_POST['confirm_price'])) {
    $confirmed_price = $_POST['confirmed_price'];

    // อัพเดตสถานะและราคาในฐานข้อมูล
    $sql_update = "UPDATE service SET price_confirmed = TRUE, service_status = 'รอซ่อม', price = ? WHERE service_id = ? AND user_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("dii", $confirmed_price, $service_id, $user_id);

    if ($stmt_update->execute()) {
        echo "<p>คุณได้ยืนยันราคาเรียบร้อยแล้ว</p>";
    } else {
        echo "<p class='text-danger'>เกิดข้อผิดพลาดในการยืนยันราคา</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันราคา</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>ยืนยันราคา</h2>

<!-- แสดงข้อมูลการซ่อมที่ลูกค้าต้องยืนยัน -->
<p>รหัสบริการ: <?php echo $service_id; ?></p>
<p>ราคาที่เสนอ: <?php echo number_format($price, 2); ?> บาท</p>
<p>สถานะการบริการ: <?php echo $service_status; ?></p>

<!-- ฟอร์มยืนยันราคา -->
<?php if ($service_status == 'รอการยืนยันราคา') { ?>
    <form method="POST" action="">
        <label for="confirmed_price">กรุณากดยืนยันราคา:</label>
        <input type="number" name="confirmed_price" step="0.01" value="<?php echo number_format($price, 2); ?>" required>
        <button type="submit" name="confirm_price">ยืนยันราคา</button>
    </form>
<?php } else { ?>
    <p>สถานะการซ่อมไม่สามารถยืนยันราคาได้ ณ ขณะนี้</p>
<?php } ?>

<!-- ปุ่มกลับไปยังหน้ารายการซ่อมของลูกค้า -->
<a href="user-dashboard.php">กลับไปยังหน้าหลักของคุณ</a>

</body>
</html>
