// หน้าเพิ่มอะไหล่เข้าคลัง

<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['spare_name'];
    $stock = $_POST['spare_stock'];
    $price = $_POST['spare_price'];

    $stmt = $conn->prepare("INSERT INTO spare_parts (spare_name, spare_stock, spare_price) VALUES (?, ?, ?)");
    $stmt->bind_param("sid", $name, $stock, $price);

    if ($stmt->execute()) {
        echo "เพิ่มอะไหล่สำเร็จ!";
    } else {
        echo "เกิดข้อผิดพลาด: " . $conn->error;
    }
}
?>

<form method="post">
    ชื่ออะไหล่: <input type="text" name="spare_name" required><br>
    จำนวนในสต๊อก: <input type="number" name="spare_stock" required><br>
    ราคาต่อหน่วย: <input type="number" step="0.01" name="spare_price" required><br>
    <button type="submit">เพิ่มอะไหล่</button>
</form>
