<?php // หน้า สมัครสมาชิก (ผู้ดูแลระบบ)
session_start();
require_once('config.php'); // ใช้ config.php แทน db.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = 'admin'; // กำหนด user_type เป็น 'admin' สำหรับผู้ดูแลระบบ

    // ตรวจสอบว่ามีผู้ใช้ซ้ำหรือไม่
    $sql_check_user = "SELECT * FROM Users WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check_user);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "ชื่อผู้ใช้นี้มีแล้ว!";
    } else {
        // เพิ่มข้อมูลผู้ดูแลระบบ (ไม่มีข้อมูลลูกค้า)
        $sql_user = "INSERT INTO Users (username, password, user_type, created_at) VALUES (?, ?, ?, NOW())";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("sss", $username, $password, $user_type);
        $stmt_user->execute();

        echo "สมัครสมาชิกผู้ดูแลระบบสำเร็จ! <a href='login.php'>เข้าสู่ระบบ</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก (ผู้ดูแลระบบ)</title>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #6a5acd, #87CEEB); /* ปรับพื้นหลัง gradient ให้สวยงาม */
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95); /* พื้นหลังโปร่งใสเล็กน้อย */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            font-size: 26px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-size: 14px;
            color: #555;
            margin-bottom: 8px;
            text-align: left;
            width: 100%;
        }

        .input-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
            width: 100%;
        }

        .input-group i {
            padding: 12px;
            background-color: #4CAF50;
            border-radius: 5px 0 0 5px;
            color: white;
            font-size: 18px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
            padding-left: 10px;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(0, 128, 0, 0.5);
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .forgot-password {
            font-size: 14px;
            color: #007BFF;
            text-decoration: none;
            display: block;
            margin-top: 10px;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        p {
            font-size: 14px;
            color: #333;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>สมัครสมาชิก (ผู้ดูแลระบบ)</h2>
        <form method="POST" action="register_admin.php">
            <!-- Username field with icon -->
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" required placeholder="กรุณากรอกชื่อผู้ใช้">
            </div>

            <!-- Password field with icon -->
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" required placeholder="กรุณากรอกรหัสผ่าน">
            </div>
            
            <!-- Hidden user_type field -->
            <input type="hidden" name="user_type" value="admin">
            
            <button type="submit">สมัครสมาชิก</button>
            
        </form>
    </div>
</body>
</html>
