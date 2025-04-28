<?php
// เชื่อมต่อฐานข้อมูล
require_once('config.php');

// ดึงข้อมูลบริการทั้งหมดจากฐานข้อมูล
$sql = "SELECT * FROM service";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการบริการ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>รายการบริการ</h1>
    <table>
        <thead>
            <tr>
                <th>Service ID</th>
                <th>สถานะบริการ</th>
                <th>อัพเดตล่าสุด</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['service_id']; ?></td>
                    <td><?php echo $row['service_status']; ?></td>
                    <td><?php echo $row['updated_at']; ?></td>
                    <td>
                        <a href="update_service_status.php?id=<?php echo $row['service_id']; ?>">อัพเดตสถานะ</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
