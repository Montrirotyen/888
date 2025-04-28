<?php
// เชื่อมต่อฐานข้อมูล
session_start();
require_once('config.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามีการส่ง service_id มาใน URL หรือไม่
if (isset($_GET['service_id'])) {
    $service_id = $_GET['service_id'];

    // ดึงข้อมูลการบริการจากฐานข้อมูล
    $sql = "SELECT * FROM service WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ดึงข้อมูลบริการมาเก็บในตัวแปร
        $service = $result->fetch_assoc();
    } else {
        echo "ไม่พบข้อมูลการบริการนี้";
        exit;
    }
} else {
    echo "ไม่พบ service_id";
    exit;
}

// ตรวจสอบการอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_service'])) {
    $issue = $_POST['issue'];
    $service_status = $_POST['service_status'];
    $price = $_POST['price'];
    $price_confirmed = $_POST['price_confirmed'];
    $customer_confirmed = $_POST['customer_confirmed'];
    $updated_by = 'admin'; // สามารถเปลี่ยนได้ตามความต้องการ
    $updated_at = date('Y-m-d H:i:s');

    // ตรวจสอบข้อมูลให้ครบถ้วน
    if (empty($issue) || empty($service_status)) {
        $_SESSION['flash_message'] = "<p class='text-danger'>กรุณากรอกข้อมูลให้ครบถ้วน</p>";
        header("Location: ".$_SERVER['PHP_SELF']."?service_id=".$service_id);
        exit;
    } else {
        // อัปเดตข้อมูลการบริการ
        $sql = "UPDATE service SET issue = ?, service_status = ?, price = ?, price_confirmed = ?, 
                customer_confirmed = ?, updated_by = ?, updated_at = ? WHERE service_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $issue, $service_status, $price, $price_confirmed, 
                          $customer_confirmed, $updated_by, $updated_at, $service_id);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "<p class='text-success'>อัปเดตข้อมูลการบริการเรียบร้อยแล้ว</p>";
            header("Location: success_page.php"); // เปลี่ยนไปที่หน้าผลลัพธ์
            exit;
        } else {
            $_SESSION['flash_message'] = "<p class='text-danger'>เกิดข้อผิดพลาดในการอัปเดตข้อมูลการบริการ</p>";
            header("Location: error_page.php"); // เปลี่ยนไปที่หน้าผลลัพธ์หากเกิดข้อผิดพลาด
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลการบริการ</title>
    <link rel="stylesheet" href="path/to/bootstrap.css">
</head>
<body>
    <div class="container mt-5">
        <h2>แก้ไขข้อมูลการบริการ</h2>
        
        <!-- แสดงข้อความจาก session flash message -->
        <?php
        if (isset($_SESSION['flash_message'])) {
            echo $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="issue">ปัญหาหรืออาการ:</label>
                <textarea class="form-control" id="issue" name="issue" rows="4" required><?php echo htmlspecialchars($service['issue']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="service_status">สถานะการบริการ:</label>
                <select class="form-control" id="service_status" name="service_status" required>
                    <option value="กำลังดำเนินการ" <?php echo ($service['service_status'] == 'กำลังดำเนินการ') ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                    <option value="เสร็จสิ้น" <?php echo ($service['service_status'] == 'เสร็จสิ้น') ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                    <option value="ยกเลิก" <?php echo ($service['service_status'] == 'ยกเลิก') ? 'selected' : ''; ?>>ยกเลิก</option>
                </select>
            </div>

            <div class="form-group">
                <label for="price">ราคา (เบื้องต้น):</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($service['price']); ?>">
            </div>

            <div class="form-group">
                <label for="price_confirmed">ราคา (ได้รับการยืนยันจากลูกค้า):</label>
                <input type="number" class="form-control" id="price_confirmed" name="price_confirmed" value="<?php echo htmlspecialchars($service['price_confirmed']); ?>">
            </div>

            <div class="form-group">
                <label for="customer_confirmed">ลูกค้ายืนยันการซ่อมหรือไม่:</label>
                <select class="form-control" id="customer_confirmed" name="customer_confirmed" required>
                    <option value="ใช่" <?php echo ($service['customer_confirmed'] == 'ใช่') ? 'selected' : ''; ?>>ใช่</option>
                    <option value="ไม่ใช่" <?php echo ($service['customer_confirmed'] == 'ไม่ใช่') ? 'selected' : ''; ?>>ไม่ใช่</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" name="update_service">อัปเดตข้อมูลการบริการ</button>
        </form>
    </div>
</body>
</html>
