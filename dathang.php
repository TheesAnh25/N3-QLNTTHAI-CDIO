<?php include "head.php"; ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu chưa đăng nhập thì quay lại đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: dangnhap.php");
    exit;
}

$tentaikhoan = $_SESSION['username'];
$conn = new mysqli('localhost', 'root', '', 'webnoithat');
$conn->set_charset("utf8");

// Khi người dùng thanh toán (có id sản phẩm truyền vào)
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $sp = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();

    if ($sp && $sp['quantity'] > 0) {
        // Giả sử thông tin người nhận tạm thời là của tài khoản (bạn có thể cho nhập thêm ở trang thanh toán)
        $hoten = "Người dùng " . $tentaikhoan;
        $diachi = "Chưa cập nhật";
        $sdt = "Chưa cập nhật";
        $pttt = "Thanh toán khi nhận hàng"; // bạn có thể đổi sang giá trị khác nếu cần

        // Tính tổng tiền = giá * số lượng
        $tongtien = $sp['price'];

        // Lưu vào bảng donhangthanhtoan
        $stmt = $conn->prepare("INSERT INTO donhangthanhtoan (tentaikhoan, masp, tensp, soluong, tongtien, hoten, diachi, sdt, pttt, ngaylap, trangthai)
                                VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, NOW(), 'Chờ xử lý')");
        $stmt->bind_param("sisdssss", $tentaikhoan, $sp['id'], $sp['name'], $tongtien, $hoten, $diachi, $sdt, $pttt);
        $stmt->execute();

        // Giảm số lượng sản phẩm trong kho
        $conn->query("UPDATE products SET quantity = quantity - 1 WHERE id = $product_id");
    }

    header("Location: dathang.php");
    exit;
}

// Lấy danh sách đơn hàng thanh toán của tài khoản
$result = null;
$sql = "SELECT * FROM donhangthanhtoan WHERE tentaikhoan = ? ORDER BY ngaylap DESC";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $tentaikhoan);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của bạn</title>
    <style>
        body {
            background-color: #fefaf0;
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }
        h2 {
            color: #7a5a00;
            text-align: center;
            margin-top: 20px;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fffdf5;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #e0d6c3;
        }
        th {
            background-color: #e5c07b;
            color: #4b3c00;
        }
        tr:nth-child(even) { background-color: #f9f5e8; }
        tr:hover { background-color: #f1ecdc; }
    </style>
</head>
<body>
    <h2>Đơn hàng của bạn</h2>
    <table>
        <tr>
            <th>Mã đơn</th>
            <th>Tài khoản</th>
            <th>Tên SP</th>
            <th>Số lượng</th>
            <th>Tổng tiền</th>
            <!-- <th>Họ tên</th> -->
            <th>Địa chỉ</th>
            <th>SĐT</th>
            <th>PT Thanh toán</th>
            <th>Ngày đặt</th>
            <th>Trạng thái</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['madon'] ?></td>
            <td><?= htmlspecialchars($row['tentaikhoan']) ?></td>
            <td><?= htmlspecialchars($row['tensp']) ?></td>
            <td><?= $row['soluong'] ?></td>
            <td><?= number_format($row['tongtien'], 0, ',', '.') ?> VNĐ</td>
            <!-- <td><?= htmlspecialchars($row['hoten']) ?></td> -->
            <td><?= htmlspecialchars($row['diachi']) ?></td>
            <td><?= htmlspecialchars($row['sdt']) ?></td>
            <td><?= htmlspecialchars($row['pttt']) ?></td>
            <td><?= $row['ngaylap'] ?></td>
            <td><?= htmlspecialchars($row['trangthai']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <div style="text-align:center; margin-top:20px;">
        <a href="trangchu.php" style="background:#e5c07b; color:#4b3c00; padding:10px 24px; border-radius:6px; text-decoration:none;">Quay lại Trang chủ</a>
    </div>
</body>
</html>
