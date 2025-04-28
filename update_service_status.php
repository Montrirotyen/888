<?php
// เชื่อมต่อฐานข้อมูล
require_once('config.php');

// ตรวจสอบว่าได้รับ service_id จาก URL หรือไม่
if (isset($_GET['id'])) {
    $service_id = $_GET['id'];
    
    // ดึงข้อมูลปัจจุบันจากฐานข้อมูล
    $sql = "SELECT * FROM service WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $service = $result->fetch_assoc();
    } else {
        echo "ไม่พบข้อมูลบริการนี้";
        exit;
    }
    
    // อัพเดตสถานะเมื่อฟอร์มถูกส่ง
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_status = $_POST['service_status'];
        $updated_by = 'admin';  // หรือสามารถใช้ข้อมูลจากเซสชั่นหากเป็นการใช้งานของแอดมิน
        
        // อัพเดตสถานะ
        $update_sql = "UPDATE service SET service_status = ?, updated_by = ?, updated_at = NOW() WHERE service_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $new_status, $updated_by, $service_id);
        
        if ($update_stmt->execute()) {
            echo "อัพเดตสถานะสำเร็จ!";
        } else {
            echo "เกิดข้อผิดพลาดในการอัพเดตสถานะ.";
        }
    }
} else {
    echo "ไม่พบ service_id.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัพเดตสถานะบริการ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>อัพเดตสถานะบริการ</h1>

    <form method="POST" action="update_service_status.php?id=<?php echo $service_id; ?>">
        <label for="service_status">สถานะบริการ:</label>
        <select name="service_status" id="service_status" required>
            <option value="รอซ่อม" <?php echo ($service['service_status'] == 'รอซ่อม') ? 'selected' : ''; ?>>รอซ่อม</option>
            <option value="รอดำเนินการ" <?php echo ($service['service_status'] == 'รอดำเนินการ') ? 'selected' : ''; ?>>รอดำเนินการ</option>
            <option value="เสร็จสิ้น" <?php echo ($service['service_status'] == 'เสร็จสิ้น') ? 'selected' : ''; ?>>เสร็จสิ้น</option>
        </select>

        <button type="submit">อัพเดตสถานะ</button>
    </form>

    <a href="service_list.php">กลับไปที่รายการบริการ</a>
</body>
</html>
