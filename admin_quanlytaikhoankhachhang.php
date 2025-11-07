<?php
// --- KHỞI ĐỘNG PHIÊN LÀM VIỆC ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- KIỂM TRA QUYỀN ADMIN ---
if (!isset($_SESSION['username']) || strtolower($_SESSION['username']) !== 'admin') {
    header("Location: dangnhap.php");
    exit;
}


// --- KẾT NỐI CSDL ---
$conn = new mysqli('localhost', 'root', '', 'webnoithat');
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// --- XỬ LÝ XÓA TÀI KHOẢN ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM taikhoan WHERE id = $id");
    header("Location: admin_quanlytaikhoankhachhang.php");
    exit;
}

// --- LẤY DANH SÁCH TÀI KHOẢN ---
$result = $conn->query("SELECT * FROM taikhoan ORDER BY id DESC");
?>

<?php include "head.php"; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản khách hàng</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fefaf0; margin: 0; padding: 0; }
        h2 { color: #7a5a00; text-align: center; margin: 20px 0; font-size: 2rem; }
        table { width: 90%; max-width: 1200px; margin: 20px auto; border-collapse: collapse;
                background: #fffdf5; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        th, td { padding: 12px; border: 1px solid #e0d6c3; text-align: center; }
        th { background: #e5c07b; color: #4b3c00; font-weight: bold; }
        tr:nth-child(even){ background:#f9f5e8; }
        tr:hover{ background:#f1ecdc; }
        a.btn { text-decoration:none; padding:6px 12px; border-radius:4px; font-weight:bold; }
        a.sua { background:#4CAF50; color:white; }
        a.xoa { background:#f44336; color:white; }
        a.them { display:inline-block; background:#e5c07b; color:#4b3c00; margin:10px auto; padding:10px 16px;
                 border-radius:6px; text-decoration:none; font-weight:bold; transition:0.3s; }
        a.them:hover { background:#d1ae66; }
    </style>
</head>
<body>
    <h2>Quản lý tài khoản khách hàng</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Tên tài khoản</th>
            <th>Mật khẩu</th>
            <th>Địa chỉ</th>
            <th>Số điện thoại</th>
            <!-- <th>Vai trò</th> -->
            <th>Hành động</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['taikhoan']) ?></td>
            <td><?= htmlspecialchars($row['matkhau']) ?></td>
            <td><?= htmlspecialchars($row['diachi']) ?></td>
            <td><?= htmlspecialchars($row['sdt']) ?></td>
            <!-- <td><?= htmlspecialchars($row['role']) ?></td> -->
            <td>
                <a class="btn xoa" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa tài khoản này?')">Xóa</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
