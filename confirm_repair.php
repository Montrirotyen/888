<?php
session_start();
require_once('config.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าเป็นผู้ดูแลระบบ (admin) เท่านั้นที่สามารถเข้าถึงหน้านี้ได้
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit;
}

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์ม
if (isset($_POST['service_id']) && isset($_POST['price'])) {
    $service_id = $_POST['service_id'];
    $price = $_POST['price'];

    // อัพเดตราคาในฐานข้อมูล
    $sql = "UPDATE service SET price = ?, price_confirmed = FALSE, service_status = 'รอการยืนยันราคา' WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $price, $service_id);  // ใช้การ bind parameter

    if ($stmt->execute()) {
        // ส่งอีเมล์ไปยังลูกค้าให้กดยืนยันราคา
        $sql_user = "SELECT customer.email FROM service
                     JOIN customer ON service.user_id = customer.user_id
                     WHERE service.service_id = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("i", $service_id);
        $stmt_user->execute();
        $stmt_user->bind_result($email);
        $stmt_user->fetch();

        if ($email) {
            // ส่งอีเมล์ไปยังลูกค้า
            $subject = "การเสนอราคาการซ่อม";
            $message = "เรียน ลูกค้า,\n\nทางเราได้เสนอราคาในการซ่อมอุปกรณ์ของท่านที่ราคาส่วนตัว: " . number_format($price, 2) . " บาท\nกรุณากดที่ลิงก์เพื่อยืนยันราคา: \n\n[ลิงก์ยืนยัน]\n\nขอบคุณครับ";
            $headers = "From: admin@repair_system.com";
            mail($email, $subject, $message, $headers);

            echo "<p>เสนอราคาและส่งข้อมูลไปยังลูกค้าสำเร็จ</p>";
        } else {
            echo "<p>ไม่พบอีเมล์ของลูกค้า</p>";
        }
    } else {
        echo "<p>เกิดข้อผิดพลาดในการเสนอราคา</p>";
    }

    // ปิดการเชื่อมต่อฐานข้อมูล
    $stmt->close();
} else {
    echo "<p>ข้อมูลไม่ครบถ้วน กรุณาลองใหม่</p>";
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เสนอราคา</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h2>เสนอราคาเรียบร้อย</h2>

    <a href="user-dashboard.php">กลับไปยังหน้าการแจ้งซ่อม</a>

</body>
</html>
