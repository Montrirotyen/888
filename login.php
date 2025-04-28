<?php
session_start();
require_once('config.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบผู้ใช้ในฐานข้อมูล
    $sql = "SELECT * FROM Users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            // สร้าง session สำหรับผู้ใช้
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];

            // ดึงข้อมูลลูกค้า
            if ($user['user_type'] == 'customer') {
                $sql_customer = "SELECT name FROM customer WHERE user_id = ?";
                $stmt_customer = $conn->prepare($sql_customer);
                $stmt_customer->bind_param("i", $user['user_id']);
                $stmt_customer->execute();
                $result_customer = $stmt_customer->get_result();

                if ($result_customer->num_rows > 0) {
                    $customer = $result_customer->fetch_assoc();
                    $_SESSION['customer_name'] = $customer['name'];
                }
                // ไปยังหน้าแดชบอร์ดของลูกค้า
                header("Location: user-dashboard.php");
                exit;
            }

            // ถ้าเป็น admin ให้ไปยังหน้าแดชบอร์ดของผู้ดูแลระบบ
            if ($user['user_type'] == 'admin') {
                header("Location: admin-dashboard.php");
                exit;
            }
        } else {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง!');</script>";
        }
    } else {
        echo "<script>alert('ชื่อผู้ใช้ไม่ถูกต้อง!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* พื้นหลังไล่เฉดฟ้าสู่ม่วง */
        body {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            background-size: cover;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
        }

        .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color:rgb(10, 158, 221);
        padding: 15px 30px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 1;
    }

    .navbar h1 {
        color: white;
        margin: 0;
        font-size: 24px;
        font-weight: bold;
        transition: transform 0.3s ease;
    }

    .navbar h1:hover {
        transform: scale(1.05);
    }

    .menu {
        display: flex;
        gap: 15px;
    }

    .menu a {
        color: white;
        text-decoration: none;
        font-size: 18px;
        font-weight: 500;
        padding: 10px 15px;
        border-radius: 5px;
        transition: color 0.3s, background-color 0.3s;
    }

    .menu a:hover {
        color:rgb(253, 253, 253);
        background-color: rgba(113, 244, 253, 0.2); /* เพิ่มพื้นหลังเมื่อโฮเวอร์ */
        transform: translateY(-3px); /* ลูกเล่นการเคลื่อนไหว */
    }

    .menu a:active {
        transform: translateY(1px); /* เคลื่อนไหวลงเมื่อคลิก */
        background-color: rgba(255, 221, 0, 0.4); /* สีเข้มขึ้นเมื่อคลิก */
    }

    .sidebar {
        width: 220px;
        background: linear-gradient(135deg, #80d0c7 0%,rgb(159, 198, 255) 100%);
        height: 100vh;
        padding-top: 20px;
        position: fixed;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .sidebar:hover {
        width: 250px; /* เพิ่มความกว้างเล็กน้อยเมื่อมีการโฮเวอร์ */
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar ul li {
        padding: 12px 0;
        text-align: center;
    }

    .sidebar ul li a {
        text-decoration: none;
        color: #0078AA;
        display: block;
        padding: 15px;
        border-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        transition: background 0.3s, color 0.3s, transform 0.3s;
    }

    .sidebar ul li a:hover {
        background-color: #0078AA;
        color: white;
        transform: scale(1.05); /* การเพิ่มขนาดเมื่อมีการโฮเวอร์ */
    }

    .sidebar ul li a:active {
        background-color: #005678; /* สีเมื่อกด */
        transform: scale(0.98);
    }


        /* กล่องล็อกอินกึ่งโปร่งแสง */
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.9); /* ปรับโปร่งแสงให้เนียนขึ้น */
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(15px); /* เบลอพื้นหลังให้เนียนมากขึ้น */
        }

        /* หัวข้อ */
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #6a5acd; /* เปลี่ยนเป็นสีม่วงเข้ม */
            font-weight: bold;
        }

        /* ปรับแต่งช่องกรอก */
        .form-control {
            border-radius: 25px;
            padding-left: 40px;
            border-color: #b0c4de; /* สีฟ้าอ่อนที่เข้ากับพื้นหลัง */
        }

        .form-group {
            position: relative;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #6a5acd; /* สีม่วงเข้ม */
        }

        /* ไอคอนข้างช่องกรอก */
        .form-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #6a5acd; /* เปลี่ยนเป็นสีม่วงเข้ม */
        }

        .input-group-text {
            background: none;
            border: none;
        }

        /* ปุ่มหลัก */
        .btn-primary {
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            background: linear-gradient(45deg, #6a5acd, #a0c4ff); /* ไล่สีฟ้าและม่วง */
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #5e4b8b, #6a5acd); /* ปรับสีเมื่อ Hover */
            transform: scale(1.05);
        }

        /* ปุ่มสมัครสมาชิก */
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            text-decoration: none;
            color: #6a5acd; /* สีม่วงเข้ม */
            font-weight: bold;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
<div class="navbar">
        <h1>ระบบแจ้งซ่อมคอมพิวเตอร์</h1>
        <div class="menu">
            <a href="index.php">หน้าหลัก</a>
        </div>
    </div>
    <div class="sidebar">
    <ul>
        <li><a href="index.php">🏠 เมนูหลัก</a></li>
        <li><a href="#">🔧 แจ้งซ่อม</a></li> 
        <li><a href="check-repair-status.php">🔍 ดูสถานะการซ่อม</a></li>
        <li><a href="#">🛠️ ประวัติการซ่อม</a></li> 
        <li><a href="login.php">🔐 เข้าสู่ระบบ</a></li> 
        <li><a href="register.php">📝 สมัครสมาชิก</a></li> 
        <li><a href="sms.php">📞 ติดต่อเรา</a></li> 
    </ul>

    </div>

<div class="login-container">
    <h2><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</h2>

    <form method="POST" action="login.php">
        <div class="form-group">
        <label for="username" class="form-label">
            <i class="fas fa-user" style="color: #3498db; margin-right: 8px;"></i> UserID
        </label>

            <div class="input-group">
                <span class="input-group-text form-icon"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
        </div>

        <div class="form-group mt-3">
            <label for="password" class="form-label">
                <i class="fas fa-lock" style="color: #3498db; margin-right: 8px;"></i> Password
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
        <button type="submit" class="btn btn-primary mt-4">เข้าสู่ระบบ</button>
        <div class="forgot-password mt-3">
            <p>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

