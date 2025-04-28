<?php
session_start();
require_once('config.php'); // ใช้ config.php แทน db.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = 'customer'; // กำหนด user_type เป็น 'customer' สำหรับลูกค้า
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $tel = $_POST['tel'];

    // ตรวจสอบว่าอีเมลที่กรอกมาเป็นอีเมลที่ถูกต้อง
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "อีเมลไม่ถูกต้อง!";
        exit;
    }

    // ตรวจสอบว่ามีผู้ใช้ซ้ำหรือไม่
    $sql_check_user = "SELECT * FROM Users WHERE username = ?";
    $stmt_check_user = $conn->prepare($sql_check_user);
    $stmt_check_user->bind_param("s", $username);
    $stmt_check_user->execute();
    $result_check_user = $stmt_check_user->get_result();

    // ตรวจสอบว่า email ซ้ำหรือไม่
    $sql_check_email = "SELECT * FROM customer WHERE email = ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_check_email = $stmt_check_email->get_result();

    if ($result_check_user->num_rows > 0) {
        echo "ชื่อผู้ใช้นี้มีแล้ว!";
    } elseif ($result_check_email->num_rows > 0) {
        echo "อีเมลนี้มีอยู่ในระบบแล้ว!";
    } else {
        // เพิ่มข้อมูลผู้ใช้ (เฉพาะลูกค้า)
        $sql_user = "INSERT INTO Users (username, password, user_type, created_at) VALUES (?, ?, ?, NOW())";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("sss", $username, $password, $user_type);
        $stmt_user->execute();
        $user_id = $stmt_user->insert_id;

        // เพิ่มข้อมูลลูกค้า
        $sql_customer = "INSERT INTO customer (user_id, name, email, address, tel) VALUES (?, ?, ?, ?, ?)";
        $stmt_customer = $conn->prepare($sql_customer);
        $stmt_customer->bind_param("issss", $user_id, $name, $email, $address, $tel);
        $stmt_customer->execute();

        // เพิ่ม script เพื่อแสดง popup
        echo "<script>alert('สมัครสมาชิกสำเร็จ!'); window.location.href='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก (ลูกค้า)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            font-family: 'Arial', sans-serif;
        }
        .register-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
            font-weight: bold;
        }
        .form-label {
            font-weight: bold;
        }
        .form-control {
            border-radius: 25px;
            padding-left: 40px;
        }
        .form-group {
            position: relative;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #007bff;
        }
        .form-icon {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #007bff;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            background: linear-gradient(45deg, #007bff, #00c3ff);
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #0090ff);
            transform: scale(1.05);
        }
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2><i class="fas fa-user-plus"></i> สมัครสมาชิก (ลูกค้า)</h2>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="username" class="form-label">UserID</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="username" name="username" required>
                    <span class="input-group-text form-icon"><i class="fas fa-user"></i></span>
                </div>
            </div>

            <div class="form-group mt-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock" style="color: #3498db; margin-right: 8px;"></i> รหัสผ่าน
                </label>
                <div class="input-group">
                    <span class="input-group-text form-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button type="button" class="btn btn-outline-secondary" id="toggle-password" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye" id="eye-icon" style="color: #3498db;"></i>
                    </button>
                </div>
            </div>

            <script>
                function togglePasswordVisibility() {
                    var passwordField = document.getElementById('password');
                    var eyeIcon = document.getElementById('eye-icon');
                    
                    if (passwordField.type === "password") {
                        passwordField.type = "text";
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        passwordField.type = "password";
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                }
            </script>

            <div class="form-group mt-3">
                <label for="name" class="form-label">ชื่อ-นามสกุล</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="name" name="name" required>
                    <span class="input-group-text form-icon"><i class="fas fa-id-card"></i></span>
                </div>
            </div>

            <div class="form-group mt-3">
                <label for="email" class="form-label">อีเมลล์</label>
                <div class="input-group">
                    <input type="email" class="form-control" id="email" name="email" required>
                    <span class="input-group-text form-icon"><i class="fas fa-envelope"></i></span>
                </div>
            </div>

            <div class="form-group mt-3">
                <label for="address" class="form-label">ที่อยู่</label>
                <div class="input-group">
                    <textarea class="form-control" id="address" name="address" required></textarea>
                    <span class="input-group-text form-icon"><i class="fas fa-home"></i></span>
                </div>
            </div>

            <div class="form-group mt-3">
                <label for="tel" class="form-label">เบอร์โทรศัพท์</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="tel" name="tel" required>
                    <span class="input-group-text form-icon"><i class="fas fa-phone-alt"></i></span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4">สมัครสมาชิก</button>

            <div class="forgot-password mt-3">
                <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
