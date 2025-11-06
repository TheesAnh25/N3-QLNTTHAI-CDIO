
<?php
// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "webnoithat");
$conn->set_charset("utf8");

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Cập nhật trạng thái
if (isset($_POST['capnhat'])) {
    $madon = $_POST['madon'];
    $trangthai = $_POST['trangthai'];
    $conn->query("UPDATE donhangthanhtoan SET trangthai='$trangthai' WHERE madon=$madon");
}

// Xóa đơn hàng
if (isset($_POST['xoa'])) {
    $madon = $_POST['madon'];
    $conn->query("DELETE FROM donhangthanhtoan WHERE madon=$madon");
}

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM donhangthanhtoan ORDER BY ngaylap DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4 text-primary">QUẢN LÝ ĐƠN HÀNG THANH TOÁN</h2>

        <table class="table table-bordered table-striped">
            <thead class="table-dark text-center">
                <tr>
                    <th>Mã đơn</th>
                    <th>Tài khoản</th>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Tổng tiền</th>
                    <!-- <th>Họ tên</th> -->
                    <th>Địa chỉ</th>
                    <th>SĐT</th>
                    <th>PT thanh toán</th>
                    <th>Ngày lập</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['madon'] ?></td>
                        <td><?= htmlspecialchars($row['tentaikhoan']) ?></td>
                        <td><?= htmlspecialchars($row['tensp']) ?></td>
                        <td><?= $row['soluong'] ?></td>
                        <td><?= number_format($row['tongtien'], 0, ',', '.') ?>₫</td>
                        <!-- <td><?= htmlspecialchars($row['hoten']) ?></td> -->
                        <td><?= htmlspecialchars($row['diachi']) ?></td>
                        <td><?= htmlspecialchars($row['sdt']) ?></td>
                        <td><?= htmlspecialchars($row['pttt']) ?></td>
                        <td><?= $row['ngaylap'] ?></td>
                        <td>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="madon" value="<?= $row['madon'] ?>">
                                <select name="trangthai" class="form-select form-select-sm me-2">
                                    <option <?= $row['trangthai'] == 'Chờ xử lý' ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option <?= $row['trangthai'] == 'Đang giao' ? 'selected' : '' ?>>Đang giao</option>
                                    <option <?= $row['trangthai'] == 'Hoàn tất' ? 'selected' : '' ?>>Hoàn tất</option>
                                    <option <?= $row['trangthai'] == 'Đã hủy' ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                                <button type="submit" name="capnhat" class="btn btn-sm btn-success">Cập nhật</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="madon" value="<?= $row['madon'] ?>">
                                <button type="submit" name="xoa" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này không?')">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
            <div style="text-align:center; margin-top:20px;">
        <a href="trangchu.php" style="background:#e5c07b; color:#4b3c00; padding:10px 24px; border-radius:6px; text-decoration:none;">Quay lại Trang chủ</a>
    </div>
    </div>
</body>
</html>
