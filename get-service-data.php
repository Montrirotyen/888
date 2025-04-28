<?php
require_once('config.php');

// ตรวจสอบว่าได้ส่ง service_id มาหรือไม่
if (isset($_GET['service_id'])) {
    $service_id = $_GET['service_id'];

    // ดึงข้อมูลการบริการจากฐานข้อมูล
    $sql = "SELECT 
                service.service_id, 
                equipment.equipment_name, 
                service.issue, 
                service.service_status, 
                service.price
            FROM service
            JOIN equipment ON service.equipment_id = equipment.equipment_id
            WHERE service.service_id = $service_id";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row); // ส่งข้อมูลกลับเป็น JSON
    } else {
        echo json_encode(['error' => 'ไม่พบข้อมูล']);
    }
}
?>
