<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการบริการซ่อม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(to bottom, #87CEEB, #6a5acd); margin: 0; padding: 0; height: 100vh; }
        .table-container { margin: 30px auto; max-width: 90%; background: #ffffff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        h2 { color: #007bff; text-align: center; font-weight: bold; margin-bottom: 20px; }
        .table th { background-color: rgb(150, 197, 247); color: rgb(14, 12, 12); text-align: center; font-weight: bold; padding: 12px; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: #f9f9f9; }
        .table-hover tbody tr:hover { background-color: rgb(82, 223, 248); transform: scale(1.02); transition: all 0.2s ease; }
        .btn-update { background-color: #28a745; color: white; border: none; border-radius: 20px; padding: 6px 12px; transition: all 0.3s ease; }
        .btn-update:hover { background-color: #218838; transform: scale(1.05); }
        .status-success { background-color: #28a745; color: white; padding: 5px 10px; border-radius: 5px; }
        .status-pending { background-color: #FFA500; color: white; padding: 5px 10px; border-radius: 5px; }
        .status-cancelled { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; }
        .form-select-sm { border-radius: 20px; }
        .form-select { padding: 10px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container table-container">
        <h2><i class="fa fa-tools icon-style"></i> ระบบจัดการบริการซ่อม</h2>

        <form method="GET" action="">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="ค้นหาชื่อผู้ใช้ หรือ รหัสบริการ" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fa fa-search"></i> ค้นหา</button>
            </div>
        </form>

        <a href="admin-dashboard.php" class="btn btn-secondary mb-3"><i class="fa fa-arrow-left"></i> กลับสู่หน้าหลัก</a>

        <?php
        $conn = new mysqli("localhost", "root", "", "repair_system");
        if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['service_id'], $_POST['service_status'])) {
            $service_id = $_POST['service_id'];
            $service_status = $_POST['service_status'];
            $price = $_POST['price'] ?? '';

            if ($conn->query("UPDATE service SET service_status = '$service_status', price = '$price' WHERE service_id = $service_id") === TRUE) {
                echo "<script>showToast('อัพเดทสถานะสำเร็จ', 'success');</script>";
            } else {
                echo "<script>showToast('เกิดข้อผิดพลาดในการอัพเดท', 'error');</script>";
            }

            if (in_array($service_status, ['เสร็จสิ้น', 'ยกเลิก'])) {
                $stmt = $conn->prepare("SELECT user_id, equipment_id, issue, service_date_in, price FROM service WHERE service_id = ?");
                $stmt->bind_param("i", $service_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                $user_id = $row['user_id'];
                $equipment_id = $row['equipment_id'];
                $issue = $row['issue'];
                $service_date_in = $row['service_date_in'];
                $price = $row['price'];
                $completed_date = date('Y-m-d H:i:s');

                $stmt2 = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
                $stmt2->bind_param("i", $user_id);
                $stmt2->execute();
                $username = $stmt2->get_result()->fetch_assoc()['username'];

                $stmt3 = $conn->prepare("INSERT INTO repairs (username, issue_title, issue_description, reported_date, status, completed_date, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt3->bind_param("ssssssi", $username, $equipment_id, $issue, $service_date_in, $service_status, $completed_date, $price);
                $stmt3->execute();
            }
        }

        $search_query = $_GET['search'] ?? '';
        $sql = "SELECT 
                    s.service_id, s.issue AS service_issue, s.service_date_in,
                    s.price, s.service_status,
                    u.username, e.equipment_name, e.warranty
                FROM service s
                INNER JOIN users u ON s.user_id = u.user_id
                LEFT JOIN equipment e ON s.equipment_id = e.equipment_id
                WHERE s.service_status NOT IN ('เสร็จสิ้น', 'ยกเลิก')";

        if ($search_query) {
            $sql .= " AND (u.username LIKE '%$search_query%' OR s.service_id LIKE '%$search_query%')";
        }

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table class='table table-striped table-hover'>
                    <thead>
                        <tr>
                            <th>รหัสบริการ</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>อุปกรณ์</th>
                            <th>ปัญหา</th>
                            <th>วันที่แจ้ง</th>
                            <th>สถานะ</th>
                            <th>ราคา</th>
                            <th>ประกัน</th>
                            <th>อะไหล่ที่ใช้</th>
                            <th>ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>";
            while ($row = $result->fetch_assoc()) {
                $warranty = ($row['warranty'] == 'มีการรับประกัน') ? 'อยู่ในประกัน' : 'ไม่มีการรับประกัน';

                // Get spare parts used in the service
                $parts_stmt = $conn->prepare("
                  SELECT sp.spare_name, rsp.quantity_used, sp.spare_price
                  FROM repair_spare_parts rsp
                  JOIN spare_parts sp ON rsp.spare_id = sp.spare_id
                  WHERE rsp.service_id = ?");
                $parts_stmt->bind_param("i", $row['service_id']);
                $parts_stmt->execute();
                $parts_res = $parts_stmt->get_result();
                $parts_list = [];
                $total_parts_price = 0;
                while ($pr = $parts_res->fetch_assoc()) {
                    $parts_list[] = "{$pr['spare_name']} ({$pr['quantity_used']})";
                    $total_parts_price += $pr['spare_price'] * $pr['quantity_used']; // Calculate total price of spare parts used
                }
                $parts_text = count($parts_list) ? implode(', ', $parts_list) : '-';

                echo "<tr>
                        <td>{$row['service_id']}</td>
                        <td>{$row['username']}</td>
                        <td>{$row['equipment_name']}</td>
                        <td>{$row['service_issue']}</td>
                        <td>{$row['service_date_in']}</td>
                        <td class='" . ($row['service_status'] == 'กำลังซ่อม' ? 'status-pending' : '') . "'>{$row['service_status']}</td>
                        <td>
                            <input type='text' name='price' class='form-control' id='price-{$row['service_id']}' value='" . ($row['price'] + $total_parts_price) . "' placeholder='ราคา' required readonly>
                        </td>
                        <td>$warranty</td>
                        <td>
                          $parts_text<br>
                          <a href='assign_spare.php?service_id={$row['service_id']}' class='btn btn-sm btn-outline-success mt-1' onclick='return confirm(\"คุณต้องการเบิกอะไหล่ใช่ไหม?\")'>
                            เลือกอะไหล่
                          </a>
                        </td>
                        <td>
                            <form method='POST' onsubmit='return confirm(\"คุณต้องการอัพเดทสถานะนี้หรือไม่?\")'>
                                <input type='hidden' name='service_id' value='{$row['service_id']}'>
                                <select class='form-select-sm' name='service_status'>
                                    <option value='กำลังซ่อม' " . ($row['service_status'] == 'กำลังซ่อม' ? 'selected' : '') . ">กำลังซ่อม</option>
                                    <option value='รออะไหล่'>รออะไหล่</option>                               
                                    <option value='เสร็จสิ้น'>เสร็จสิ้น</option>
                                    <option value='ยกเลิก'>ยกเลิก</option>
                                </select>
                                <button type='submit' class='btn-update mt-2'>อัพเดท</button>
                            </form>
                        </td>
                    </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p class='text-center'>ไม่มีข้อมูลการบริการ</p>";
        }

        $conn->close();
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showToast(message, type) {
        const toastEl = document.getElementById('toastMessage');
        const toastBody = toastEl.querySelector('.toast-body');
        toastBody.textContent = message;
        if (type === 'success') {
            toastEl.classList.remove('bg-danger');
            toastEl.classList.add('bg-success');
        } else {
            toastEl.classList.remove('bg-success');
            toastEl.classList.add('bg-danger');
        }
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
    </script>
</body>
</html>
