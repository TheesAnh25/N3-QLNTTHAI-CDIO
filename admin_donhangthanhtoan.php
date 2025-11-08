
<?php
// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "webnoithat");
$conn->set_charset("utf8");

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$thongbao = ""; // biến để lưu loại thông báo

// Cập nhật trạng thái
if (isset($_POST['capnhat'])) {
    $madon = $_POST['madon'];
    $trangthai = $_POST['trangthai'];
    if ($conn->query("UPDATE donhangthanhtoan SET trangthai='$trangthai' WHERE madon=$madon")) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=capnhat");
        exit;
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=capnhat");
        exit;
    }
}

// Xóa đơn hàng
if (isset($_POST['xoa'])) {
    $madon = $_POST['madon'];
    if ($conn->query("DELETE FROM donhangthanhtoan WHERE madon=$madon")) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=xoa");
        exit;
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=xoa");
        exit;
    }
}

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM donhangthanhtoan ORDER BY ngaylap DESC";
$result = $conn->query($sql);
?>
<?php include "head.php"; ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fefaf0;
            margin: 0;
            padding: 0;
        }

        h2 {
            color: #7a5a00;
            text-align: center;
            margin: 20px 0;
            font-size: 2rem;
        }

        table {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fffdf5;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #e0d6c3;
            text-align: center;
        }

        th {
            background: #e5c07b;
            color: #4b3c00;
            font-weight: bold;
        }

        /* Toast thông báo */
        #toast {
            position: fixed;
            top: 80px;
            right: 20px;
            min-width: 200px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: bold;
            opacity: 0;
            pointer-events: none;
            transform: translateX(100%);
            transition: all 0.5s ease;
            z-index: 9999;
        }

        #toast.error {
            background-color: #f44336;
        }

        #toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        /* Hiệu ứng icon nháy */
        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        .blink {
            animation: blink 1s infinite;
        }

        /* Hiệu ứng chữ chạy ngang */
        .marquee {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            animation: marquee 8s linear infinite;
        }

        @keyframes marquee {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(-100%);
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 style="font-size: 30px; color: black; text-align: center; overflow: hidden; margin-top: 20px;">
            <span class="marquee">
                <b>Quản Lí Đơn Hàng Thanh Toán</b>
            </span>
            </h2>

            <table>
                <thead>
                    <tr class="text-center">
                        <th>Mã đơn</th>
                        <th>Tài khoản</th>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Tổng tiền</th>
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
                            <td class="align-middle"><?= $row['madon'] ?></td>
                            <td class="align-middle"><?= htmlspecialchars($row['tentaikhoan']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($row['tensp']) ?></td>
                            <td class="align-middle"><?= $row['soluong'] ?></td>
                            <td class="align-middle"><?= number_format($row['tongtien'], 0, ',', '.') ?> VNĐ</td>
                            <td class="align-middle"><?= htmlspecialchars($row['diachi']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($row['sdt']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($row['pttt']) ?></td>
                            <td class="align-middle"><?= $row['ngaylap'] ?></td>
                            <td class="align-middle">
                                <form method="POST" class="d-flex">
                                    <input type="hidden" name="madon" value="<?= $row['madon'] ?>">
                                    <select name="trangthai" class="form-select form-select-sm me-2">
                                        <option <?= $row['trangthai'] == 'Chờ xử lý' ? 'selected' : '' ?>>Chờ xử lý</option>
                                        <option <?= $row['trangthai'] == 'Đang giao' ? 'selected' : '' ?>>Đang giao</option>
                                        <option <?= $row['trangthai'] == 'Hoàn tất' ? 'selected' : '' ?>>Hoàn tất</option>
                                        <option <?= $row['trangthai'] == 'Đã hủy' ? 'selected' : '' ?>>Đã hủy</option>
                                    </select>
                                    <button type="submit" name="capnhat" class="btn btn-sm btn-success"
                                        onclick="return confirm('Bạn có chắc muốn cập nhật đơn hàng này không?')">Cập nhật</button>
                                </form>
                            </td>
                            <td class="align-middle">
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

    <div id="toast"></div>

    <script>
        // Khi trang load, kiểm tra tham số URL
        const params = new URLSearchParams(window.location.search);
        const toast = document.getElementById('toast');

        if (params.get('success') === 'xoa') {
            toast.textContent = 'Xóa đơn hàng thành công!';
            toast.classList.add('success', 'show');
        } else if (params.get('success') === 'capnhat') {
            toast.textContent = 'Cập nhật trạng thái thành công!';
            toast.classList.add('success', 'show');
        } else if (params.get('error') === 'capnhat') {
            toast.textContent = 'Cập nhật thất bại!';
            toast.classList.add('error', 'show');
        } else if (params.get('error') === 'xoa') {
            toast.textContent = 'Xóa đơn hàng thất bại!';
            toast.classList.add('error', 'show');
        }

        // Ẩn toast sau 2.5 giây và xóa param khỏi URL
        if (toast.classList.contains('show')) {
            setTimeout(() => {
                toast.classList.remove('show');
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 2500);
        }
    </script>
</body>

</html>