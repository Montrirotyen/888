<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อเรา</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            margin: 0;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }

        /* แถบข้าง */
        .sidebar {
            width: 220px;
            background: linear-gradient(135deg, #80d0c7 0%, rgb(159, 198, 255) 100%);
            height: 100vh; /* ความสูงเต็มหน้าจอ */
            padding-top: 20px;
            position: fixed; /* ให้แถบข้างติดอยู่ที่ข้างๆ */
            top: 0; /* เริ่มจากด้านบน */
            left: 0; /* เริ่มจากด้านซ้าย */
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 100; /* ทำให้แถบข้างอยู่เหนือเนื้อหาหลัก */
        }

        .sidebar:hover {
            width: 250px;
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
            transform: scale(1.05);
        }

        .sidebar ul li a:active {
            background-color: #005678;
            transform: scale(0.98);
        }

        /* การจัดวาง .main-content */
        .main-content {
            margin-left: 240px; /* ปรับให้พื้นที่ของเนื้อหาหลักถูกต้องเมื่อแถบข้างมีขนาด */
            padding: 30px;
            width: 100%;
        }

        .contact-container {
            background: white;
            width: 90%;
            max-width: 600px;
            padding: 30px;
            border-radius: 20px;
            box-shadow:  15px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin: 20px auto;
        }

        .contact-container h2 {
            color: #3498db;
            margin-bottom: 20px;
        }

        .contact-container p {
            color: #666;
            margin-bottom: 30px;
        }

        .contact-info {
            margin-top: 30px;
            text-align: left;
            color: #3498db;
        }

        .contact-info p {
            margin: 10px 0;
        }

        .contact-info i {
            margin-right: 10px;
            color: #3498db;
        }
        .contact-info i {
            color: #6ec1e4;
            margin-right: 10px;
        }

        .contact-info a {
            text-decoration: none;
            color: #6ec1e4;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }


        /* เพิ่มฟอนต์ของปุ่ม */
        button {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 25px;
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(52, 152, 219, 0.4);
        }

    </style>
</head>
<body>
    <div class="sidebar">
        <ul>
            <li><a href="user-dashboard.php">🏠 เมนูหลัก</a></li>
            <li><a href="customer_list.php">👤 ข้อมูลส่วนตัว</a></li>
            <li><a href="view-repair.php">🔧 แจ้งซ่อม</a></li> 
            <li><a href="check-repair-status.php">🔍 ดูสถานะการซ่อม</a></li>
            <li><a href="repair-history.php">🛠️ ประวัติการซ่อม</a></li> 
            <li><a href="sms.php">📞 ติดต่อเรา</a></li>
            <li><a href="logout.php">🔒 ออกจากระบบ</a></li> 
        </ul>
    </div>
    <div class="main-content">
        <div class="contact-container">
            <h2>ติดต่อเรา</h2>
            <div class="contact-info">
                <p><i class="fas fa-phone"></i> โทร: 012-345-6789</p>
                <p><i class="fas fa-envelope"></i> อีเมล: ITS2@gmail.com</p>
                <p><i class="fas fa-map-marker-alt"></i> ที่อยู่: 123 ถนนหลัก เมืองใหญ่ ประเทศไทย</p>
                <p><i class="fab fa-facebook"></i></i>: ITS 2</p>
                <p><i class="fab fa-line"></i></i>: ITS 2</p>
            </div>

        </div>
    </div>
</body>
</html>
