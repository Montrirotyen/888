<?php
session_start();
require_once('config.php'); // เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่าเป็นผู้ดูแลระบบ (admin) เท่านั้นที่สามารถเข้าถึงหน้านี้ได้
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit;
}

// ดึงข้อมูลผู้ใช้ทั้งหมดมาแสดงใน Select
$sql = "SELECT user_id, username FROM users WHERE user_type = 'customer'";
$result = $conn->query($sql);

// ตรวจสอบการ submit ฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $equipment_name = $_POST['equipment_name'];
    $warranty = $_POST['warranty'];
    $user_id = $_POST['user_id']; // ดึง user_id จากฟอร์ม

    // ถ้ามีการรับประกัน ต้องกรอกวันที่เริ่มต้นและสิ้นสุด
    if ($warranty == 'มีการรับประกัน') {
        $warranty_start_date = $_POST['warranty_start_date'];
        $warranty_end_date = $_POST['warranty_end_date'];
    } else {
        // ถ้าไม่มีการรับประกัน ไม่ต้องกรอกวันที่
        $warranty_start_date = null;
        $warranty_end_date = null;
    }

    // ตรวจสอบว่ามีชื่ออุปกรณ์หรือไม่
    if (empty($equipment_name)) {
        $error_message = "กรุณากรอกชื่ออุปกรณ์";
    } else {
        // บันทึกข้อมูลอุปกรณ์ลงฐานข้อมูล (ลบ price ออก)
        $sql = "INSERT INTO equipment (equipment_name, warranty, warranty_start_date, warranty_end_date, user_id) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssd", $equipment_name, $warranty, $warranty_start_date, $warranty_end_date, $user_id);

        if ($stmt->execute()) {
            $success_message = "บันทึกอุปกรณ์เรียบร้อยแล้ว"; // แสดงข้อความที่ต้องการ
        } else {
            $error_message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มอุปกรณ์</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        /* เรายังคงใช้สไตล์เดิม */
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #3498db;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        h2 i { 
            margin-right: 8px; 
        }

        .form-group { 
            margin-bottom: 15px; 
        }

        label {
            font-size: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        label i {
            margin-right: 8px;
            color: #3498db;
        }

        input, select {
            width: 95%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: 0.3s;
        }

        input:focus, select:focus {
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.7);
            outline: none;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
        }

        .save-btn, .cancel-btn {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .save-btn { 
            background-color: #3498db; 
            color: white; 
            margin-right: 5px; 
        }

        .save-btn:hover { 
            background-color: #2980b9; 
        }

        .cancel-btn { 
            background-color: #e74c3c; 
            color: white; 
        }

        .cancel-btn:hover { 
            background-color: #c0392b; 
        }

        .buttons i { 
            margin-right: 5px; 
        }

        /* การตอบสนองสำหรับหน้าจอขนาดเล็ก */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
                width: 90%;
            }

            .buttons {
                flex-direction: column;
            }

            .save-btn, .cancel-btn {
                margin-bottom: 10px;
                margin-right: 0;
            }
        }

        /* Style for the modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
            animation: fadeIn 0.5s ease-in-out;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            animation: zoomIn 0.4s ease-in-out;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.8);
            }
            to {
                transform: scale(1);
            }
        }

        /* Success Message */
        .modal-content p {
            font-size: 18px;
            color: #2ecc71;
            font-weight: 600;
        }

        /* Error Message */
        .modal-content p.error {
            font-size: 18px;
            color: #e74c3c;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fa-solid fa-laptop"></i> เพิ่มอุปกรณ์</h2>
        <form method="POST" action="add_equipment.php">
            <div class="form-group">
                <label for="user_id"><i class="fa-solid fa-user"></i> ผู้ใช้งาน</label>
                <select id="user_id" name="user_id">
                    <option value="">เลือกผู้ใช้</option>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <option value="<?php echo $row['user_id']; ?>"><?php echo $row['username']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="equipment_name"><i class="fa-solid fa-tag"></i> ชื่ออุปกรณ์</label>
                <input type="text" id="equipment_name" name="equipment_name" placeholder="เช่น คอมพิวเตอร์, ปริ้นเตอร์" required>
            </div>
            <div class="form-group">
                <label for="warranty"><i class="fa-solid fa-shield-alt"></i> การรับประกัน</label>
                <select id="warranty" name="warranty" required>
                    <option value="ไม่มีการรับประกัน">ไม่มีการรับประกัน</option>
                    <option value="มีการรับประกัน">มีการรับประกัน</option>
                </select>
            </div>
            <div id="warranty_dates">
                <div class="form-group">
                    <label for="warranty_start_date"><i class="fa-solid fa-calendar"></i> วันที่เริ่มรับประกัน</label>
                    <input type="date" id="warranty_start_date" name="warranty_start_date">
                </div>
                <div class="form-group">
                    <label for="warranty_end_date"><i class="fa-solid fa-calendar"></i> วันที่สิ้นสุดรับประกัน</label>
                    <input type="date" id="warranty_end_date" name="warranty_end_date">
                </div>
            </div>
            <div class="buttons">
                <button type="submit" class="save-btn"><i class="fa-solid fa-check"></i> บันทึกข้อมูล</button>
                <button type="reset" class="cancel-btn"><i class="fa-solid fa-times"></i> ยกเลิก</button>
            </div>
        </form>
    </div>

    <!-- Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p class="<?php echo isset($error_message) ? 'error' : ''; ?>">
                <?php 
                if (isset($success_message)) {
                    echo $success_message; // แสดงข้อความ "บันทึกอุปกรณ์เรียบร้อยแล้ว"
                } elseif (isset($error_message)) {
                    echo $error_message;
                }
                ?>
            </p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#user_id').select2({
                placeholder: "เลือกผู้ใช้",
                allowClear: true
            });
        });

        document.getElementById('warranty').addEventListener('change', function() {
            var warrantyDates = document.getElementById('warranty_dates');
            if (this.value === 'มีการรับประกัน') {
                warrantyDates.style.display = 'block';
            } else {
                warrantyDates.style.display = 'none';
            }
        });

        // ปรับให้ modal แสดงขึ้นทันที
        <?php if (isset($success_message)): ?>
            document.getElementById('successModal').style.display = "block";
        <?php endif; ?>

        // ปิด modal
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('successModal').style.display = "none";
        });

        // ปิด modal ถ้าผู้ใช้คลิกนอก modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('successModal')) {
                document.getElementById('successModal').style.display = "none";
            }
        };
    </script>
</body>
</html>
