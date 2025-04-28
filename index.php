<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบแจ้งซ่อมคอมพิวเตอร์</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #0a9edd;
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
            color: rgb(253, 253, 253);
            background-color: rgba(113, 244, 253, 0.2);
            transform: translateY(-3px);
        }

        .menu a:active {
            transform: translateY(1px);
            background-color: rgba(255, 221, 0, 0.4);
        }

        .sidebar {
            width: 220px;
            background: linear-gradient(135deg, #80d0c7 0%, rgb(159, 198, 255) 100%);
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
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

        .content {
            margin-left: 220px;
            padding: 20px;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .card img {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .card:hover img {
            transform: rotate(15deg);
        }

        .card h3 {
            color: #0078AA;
            font-size: 20px;
            margin-top: 15px;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card img {
            width: 50px;
            margin-bottom: 10px;
        }

        .card h3 {
            color: rgb(4, 154, 218);
            font-size: 20px;
            margin: 15px 0 0 0;
        }

        @media screen and (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: none;
            }

            .content {
                margin-left: 0;
                padding-top: 20px;
            }

            .card-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media screen and (max-width: 480px) {
            .menu {
                flex-direction: column;
                gap: 10px;
            }

            .card-container {
                grid-template-columns: 1fr;
            }

            .navbar h1 {
                font-size: 20px;
            }
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
            <li><a href="">🔍 ดูสถานะการซ่อม</a></li>
            <li><a href="#">🛠️ ประวัติการซ่อม</a></li>
            <li><a href="login.php">🔐 เข้าสู่ระบบ</a></li>
            <li><a href="register.php">📝 สมัครสมาชิก</a></li>
            <li><a href="">📞 ติดต่อเรา</a></li>
        </ul>
    </div>
    <div class="content">
        <h2>ยินดีต้อนรับสู่ระบบแจ้งซ่อมคอมพิวเตอร์ของเรา</h2>
        <div class="card-container">
            <div class="card">
                <img src="https://cdn-icons-png.flaticon.com/512/1055/1055687.png" alt="แจ้งซ่อม">
                <h3>แจ้งซ่อม</h3>
            </div>
            <div class="card">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149852.png" alt="ตรวจสอบสถานะ">
                <h3>ตรวจสอบสถานะ</h3>
            </div>
            <div class="card">
                <img src="https://cdn-icons-png.flaticon.com/512/1828/1828640.png" alt="ประวัติการซ่อม">
                <h3>ประวัติการซ่อม</h3>
            </div>
            <div class="card">
                <img src="https://cdn-icons-png.flaticon.com/512/609/609803.png" alt="ติดต่อเรา">
                <h3>ติดต่อเรา</h3>
            </div>
        </div>
    </div>
</body>
</html>
