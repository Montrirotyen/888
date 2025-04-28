<?php // หน้ารายชื่อผู้ใช้ (ผู้ดูแลระบบ)
// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "repair_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบการลบข้อมูล
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // ลบข้อมูลในตาราง service ก่อน
    $delete_service_sql = "DELETE FROM service WHERE user_id = ?";
    $stmt_service = $conn->prepare($delete_service_sql);
    $stmt_service->bind_param("i", $delete_id);
    $stmt_service->execute();
    $stmt_service->close();

    // ลบข้อมูลในตาราง customer
    $delete_customer_sql = "DELETE FROM customer WHERE user_id = ?";
    $stmt_customer = $conn->prepare($delete_customer_sql);
    $stmt_customer->bind_param("i", $delete_id);
    $stmt_customer->execute();
    $stmt_customer->close();

    // ลบผู้ใช้ในตาราง users
    $delete_user_sql = "DELETE FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($delete_user_sql);
    $stmt_user->bind_param("i", $delete_id);

    if ($stmt_user->execute()) {
        echo "<script>alert('ลบผู้ใช้สำเร็จ!'); window.location.href='user_list.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบผู้ใช้!');</script>";
    }
    $stmt_user->close();
}

// ตรวจสอบการค้นหา
$search_query = "";
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
    $sql = "SELECT user_id, username, user_type FROM users WHERE username LIKE '%$search_query%'";
} else {
    $sql = "SELECT user_id, username, user_type FROM users";
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>รายชื่อผู้ใช้</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    /* ทั่วไป */
    body {
        background: linear-gradient(to bottom, #87CEEB, #6a5acd);
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0;
         padding: 0;
        height: 100vh;           
        overflow: hidden;
    }

    /* Header */
    header {
        padding: 20px;
        text-align: center;
        color: white;
    }

    header h1 {
        font-size: 32px;
        margin: 0;
    }

    /* ช่องค้นหา */
    .search-container {
        text-align: center;
        margin: 10px;
        width: 100%;
    }

    .search-input {
        width: 80%;
        padding: 10px;
        font-size: 16px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    /* คอลัมน์ใหญ่ที่ครอบคลุมทุกการ์ด */
    .user-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 90%;
        max-width: 1000px;
        margin-top: 20px;
        background: rgba(255, 255, 255, 0.9);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    /* รายการผู้ใช้ */
    .user-card {
        background: white;
        padding: 15px;
        width: 80%;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: left;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between; /* ทำให้ข้อมูลแสดงในแถว */
        align-items: center;
    }

    .user-card p {
        margin: 5px 0;
        font-size: 16px;
        color: #34495e;
    }

    .user-actions {
        display: flex;
        gap: 10px;
    }

    .user-actions a {
        padding: 8px 12px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }

    .btn-edit {
        background-color: #3498db;
        color: white;
    }

    .btn-edit:hover {
        background-color: #2980b9;
    }

    .btn-delete {
        background-color: #e74c3c;
        color: white;
    }

    .btn-delete:hover {
        background-color: #c0392b;
    }

    .back-container {
        width: 100%;
        display: flex;
        justify-content: flex-start; /* จัดให้ปุ่มไปชิดซ้าย */
        padding-left: 170px; /* เพิ่มระยะห่างจากขอบซ้าย */
        margin-bottom: 20px; /* เพิ่มระยะห่างด้านล่าง */
    }


    .back-button {
        background-color: #16a085;
        color: white;
        padding: 12px 20px;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s;
        width: fit-content;
    }

    .back-button:hover {
        background-color: #1abc9c;
    }

</style>
</head>
<body>
    

    <!-- Header -->
    <header>
        <h1><i class="fas fa-users"></i> ระบบจัดการผู้ใช้</h1>
    </header>

    <!-- คอลัมน์ใหญ่ครอบคลุมรายการผู้ใช้ -->
    <div class="user-container" id="userList">
        <!-- ช่องค้นหา -->
        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="🔍 ค้นหาผู้ใช้..." onkeyup="searchUsers()">
        </div>

        <!-- ปุ่มย้อนกลับ -->
        <div class="back-container">
            <a href="admin-dashboard.php">
                <button class="back-button"><i class="fas fa-arrow-left"></i> ย้อนกลับ</button>
            </a>
        </div>
        
        <?php
        // เชื่อมต่อฐานข้อมูล
        $conn = new mysqli("localhost", "root", "", "repair_system");
        if ($conn->connect_error) {
            die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
        }

        // ดึงข้อมูลผู้ใช้
        $sql = "SELECT user_id, username, user_type FROM Users";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
        ?>
                <div class="user-card">
                    <p><i class="fas fa-id-card"></i> <strong>User ID:</strong> <?php echo $row['user_id']; ?></p>
                    <p><i class="fas fa-user"></i> <strong>ชื่อผู้ใช้:</strong> <?php echo $row['username']; ?></p>
                    <p><i class="fas fa-users-cog"></i> <strong>ประเภทผู้ใช้:</strong> <?php echo $row['user_type']; ?></p>
                    <div class="user-actions">
                        <a href="edit_user.php?user_id=<?php echo $row['user_id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> แก้ไข</a>
                        <a href="user_list.php?delete_id=<?php echo $row['user_id']; ?>" class="btn-delete" onclick="return confirm('คุณต้องการลบผู้ใช้นี้ใช่หรือไม่?');"><i class="fas fa-trash"></i> ลบ</a>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<p style='color: #34495e; text-align: center;'>ไม่มีผู้ใช้ในระบบ</p>";
        }

        // ปิดการเชื่อมต่อ
        $conn->close();
        ?>
    </div>

<script>
    // ฟังก์ชันค้นหาผู้ใช้
    function searchUsers() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let userCards = document.getElementsByClassName("user-card");

        for (let i = 0; i < userCards.length; i++) {
            let userName = userCards[i].getElementsByTagName("p")[1].innerText.toLowerCase();
            if (userName.includes(input)) {
                userCards[i].style.display = "flex"; /* แสดงแบบแถว */
            } else {
                userCards[i].style.display = "none";
            }
        }
    }
</script>

</body>
</html>


