<?php
require_once 'config.php';

// รับ service_id จาก URL
$service_id = $_GET['service_id'] ?? 0;

// ตรวจสอบว่า service_id มีอยู่ในตาราง service จริงไหม
$stmt = $conn->prepare("SELECT service_id FROM service WHERE service_id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("ไม่พบใบงานซ่อมนี้ในระบบ");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ลูปเพื่อบันทึกข้อมูลสำหรับแต่ละอะไหล่ที่เลือก
    foreach ($_POST['spare_id'] as $key => $spare_id) {
        $quantity_used = $_POST['quantity_used'][$key];

        // 1. ลดสต๊อกอะไหล่
        $update_stock = $conn->prepare("UPDATE spare_parts SET spare_stock = spare_stock - ? WHERE spare_id = ?");
        $update_stock->bind_param("ii", $quantity_used, $spare_id);
        $update_stock->execute();

        // 2. บันทึกการใช้อะไหล่กับใบซ่อม (เปลี่ยน repair_id เป็น service_id)
        $assign = $conn->prepare("INSERT INTO repair_spare_parts (service_id, spare_id, quantity_used) VALUES (?, ?, ?)");
        $assign->bind_param("iii", $service_id, $spare_id, $quantity_used);
        $assign->execute();
    }

    echo "<div class='alert alert-success'>บันทึกการใช้อะไหล่เรียบร้อย!</div>";
}

// ดึงรายการอะไหล่ทั้งหมด
$spares = $conn->query("SELECT * FROM spare_parts");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เบิกอะไหล่ให้ใบงานซ่อม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #87CEEB, #6a5acd);
            margin: 0;
            padding: 0;
            height: 100vh;
        }
        .container {
            padding: 50px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .spare-item {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .spare-item select, .spare-item input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-radius: 20px;
            padding: 6px 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: scale(1.05);
        }
        .btn-success {
            background-color: #28a745;
            border-radius: 20px;
            padding: 10px 20px;
            width: 100%;
            font-weight: bold;
        }
        .btn-success:hover {
            background-color: #218838;
            transform: scale(1.05);
        }
        .alert {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <h2>🚀 เบิกอะไหล่สำหรับใบซ่อมเลขที่ <?= htmlspecialchars($service_id) ?></h2>

    <form method="post" class="card p-4 shadow">
        <div class="mb-3" id="spare-fields">
            <label for="spare_id" class="form-label">เลือกอะไหล่</label>
            <div class="spare-item">
                <select name="spare_id[]" class="form-select" required>
                    <?php while($row = $spares->fetch_assoc()): ?>
                        <option value="<?= $row['spare_id'] ?>">
                            <?= $row['spare_name'] ?> (คงเหลือ: <?= $row['spare_stock'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="number" name="quantity_used[]" class="form-control" required min="1" placeholder="จำนวนที่ใช้">
            </div>
        </div>

        <div class="mb-3 text-center">
            <button type="button" class="btn btn-secondary" id="add-spare-item">+ เพิ่มอะไหล่</button>
        </div>

        <button type="submit" class="btn btn-success">💾 เบิกอะไหล่</button>
    </form>

    <div class="mt-4 text-center">
        <a href="change-status.php" class="btn btn-secondary">🔙 กลับไปเลือกใบซ่อม</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// JavaScript สำหรับการเพิ่มรายการอะไหล่
document.getElementById('add-spare-item').addEventListener('click', function() {
    const spareFieldsContainer = document.getElementById('spare-fields');
    const newSpareItem = document.createElement('div');
    newSpareItem.classList.add('spare-item');
    
    // สร้าง select และ input ใหม่
    newSpareItem.innerHTML = `
        <select name="spare_id[]" class="form-select" required>
            <?php
                $spares = $conn->query("SELECT * FROM spare_parts");
                while($row = $spares->fetch_assoc()): 
            ?>
                <option value="<?= $row['spare_id'] ?>">
                    <?= $row['spare_name'] ?> (คงเหลือ: <?= $row['spare_stock'] ?>)
                </option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="quantity_used[]" class="form-control" required min="1" placeholder="จำนวนที่ใช้">
    `;
    
    spareFieldsContainer.appendChild(newSpareItem);
});
</script>

</body>
</html>
