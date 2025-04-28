<?php
require_once('config.php');

// รับข้อมูลที่ส่งมาจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $issue = $_POST['issue'];
    $service_status = $_POST['service_status'];
    $price = $_POST['price'];

    // อัปเดตข้อมูลการบริการในฐานข้อมูล
    $sql = "UPDATE service SET issue = ?, service_status = ?, price = ? WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdi", $issue, $service_status, $price, $service_id);

    if ($stmt->execute()) {
        // ถ้าอัปเดตสำเร็จให้เด้งกลับไปที่หน้า search-repairs.php พร้อมกับพารามิเตอร์ success=1
        header("Location: search-repairs.php?success=1");
        exit(); // หยุดการทำงานของสคริปต์
    } else {
        // ถ้ามีข้อผิดพลาดในการอัปเดตให้กลับไปที่หน้า search-repairs.php พร้อมกับพารามิเตอร์ success=0
        header("Location: search-repairs.php?success=0");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
