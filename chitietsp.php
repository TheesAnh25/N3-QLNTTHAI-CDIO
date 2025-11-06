<?php include "head.php"; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$msp = $_GET['masp'] ?? '';
$conn = new mysqli('localhost', 'root', '', 'webnoithat');
$conn->set_charset("utf8");

$stmt = $conn->prepare("SELECT tensp, gia, chatlieu, mau, hinhthuc, mota, anh FROM sanpham WHERE masp = ?");
$stmt->bind_param("s", $msp);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

$tentaikhoan = $_SESSION['username'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xac_nhan_thanh_toan'])) {
    $hoten = $_POST['hoten'] ?? '';
    $diachi = $_POST['diachi'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $pttt = $_POST['pttt'] ?? '';
    $soluong = $_POST['soluong'] ?? 1;

    if (!$tentaikhoan) {
        echo "<script>alert('Bạn cần đăng nhập để thanh toán'); window.location='dangnhap.php';</script>";
        exit;
    }

    $gia = floatval($product['gia']);
    $tongtien = $gia * $soluong;

    $insert = $conn->prepare("INSERT INTO donhangthanhtoan (tentaikhoan, masp, tensp, soluong, tongtien, hoten, diachi, sdt, pttt, ngaylap)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $insert->bind_param("sssdiisss", $tentaikhoan, $msp, $product['tensp'], $soluong, $tongtien, $hoten, $diachi, $sdt, $pttt);
    $insert->execute();

    echo "<script>alert('Thanh toán thành công!'); window.location='giohang.php';</script>";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $soluong = $_POST['soluong'] ?? 1;

    if (!$tentaikhoan) {
        echo "<script>alert('Bạn cần đăng nhập để thêm vào giỏ hàng'); window.location='dangnhap.php';</script>";
        exit;
    }

    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    $check = $conn->prepare("SELECT soluong FROM giohang WHERE tentaikhoan = ? AND masp = ?");
    $check->bind_param("ss", $tentaikhoan, $msp);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Nếu đã có, cập nhật số lượng
        $row = $result->fetch_assoc();
        $newQty = $row['soluong'] + $soluong;
        $update = $conn->prepare("UPDATE giohang SET soluong = ? WHERE tentaikhoan = ? AND masp = ?");
        $update->bind_param("iss", $newQty, $tentaikhoan, $msp);
        $update->execute();
        $update->close();
    } else {
        // Nếu chưa có, thêm mới vào giỏ hàng
        $insert = $conn->prepare("INSERT INTO giohang (tentaikhoan, masp, tensp, gia, soluong, anh)
                                  VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssdis", $tentaikhoan, $msp, $product['tensp'], $product['gia'], $soluong, $product['anh']);
        $insert->execute();
        $insert->close();
    }

    $check->close();
    echo "<script>alert('Đã thêm vào giỏ hàng thành công!'); window.location='giohang.php';</script>";
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chi tiết sản phẩm</title>
<style>
            * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        :root {
            --primary-color: #7B4B37;
            --primary-color-light: #A67C68;
            --background-color: #e2ddcf;
        }

        body {
            background-color: var(--background-color);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            margin-bottom: 40px;
            padding: 20px;
            background-color: #fffaf1;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .product-container {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

        .product-image {
            flex: 1;
            aspect-ratio: 4 / 3;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            transition: opacity 0.3s ease;
            background: #fff;
        }

        .product-image .image-loading {
            position: absolute;
            display: none;
            font-size: 16px;
            color: #666;
        }

        .product-image img[loading="lazy"] {
            opacity: 0;
        }

        .product-image img.loaded {
            opacity: 1;
        }

        .product-info {
            flex: 1;
        }

        .product-title {
            font-size: 24px;
            margin-bottom: 10px;
            color: #000;
            font-weight: bold;
        }

        .product-price {
            color: #000;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .discount {
            background-color: var(--primary-color-light);
            color: white;
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .specifications {
            margin-bottom: 15px;
        }

        .specifications h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .specifications table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .specifications th,
        .specifications td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .specifications th {
            background-color: var(--primary-color-light);
            color: white;
        }

        .action-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .quantity-cart-group {
            display: flex;
            gap: 10px;
        }

        .quantity-input {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ccc;
            flex: 1;
        }

        .add-to-cart {
            background: none;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 12px;
            font-size: 16px;
            cursor: pointer;
            flex: 2;
            text-align: center;
            border-radius: 8px;
        }

        .add-to-cart:hover {
            background-color: var(--primary-color-light);
            color: white;
        }
/* Giữ nguyên style của bạn */
.buy-now {
  background-color: #7B4B37;
  border: 2px solid #7B4B37;
  color: #fff;
  padding: 12px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  width: 100%;
  text-align: center;
  border-radius: 8px;
  transition: all 0.3s ease;
}
.buy-now:hover {
  background-color: #A67C68;
  border-color: #A67C68;
  transform: scale(1.03);
}
/* Modal */
.modal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.4);
  justify-content: center; align-items: center;
}
.modal-content {
  background: #fffaf1;
  padding: 25px 30px;
  border-radius: 12px;
  width: 90%; max-width: 400px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.modal-content h2 {
  text-align: center;
  color: #7B4B37;
  margin-bottom: 15px;
}
.modal-content input, .modal-content select {
  width: 100%; padding: 10px;
  margin-top: 5px; border: 1px solid #ccc;
  border-radius: 8px;
}
.modal-actions {
  margin-top: 15px; display: flex; justify-content: space-between;
}
.modal-actions button {
  padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;
  border: none;
}
.modal-actions button[type="submit"] { background-color: #7B4B37; color: white; }
.modal-actions button[type="button"] { background-color: #ccc; }

        .description {
            margin-top: 40px;
        }

        .description h2 {
            margin-bottom: 20px;
            color: #000;
            font-weight: bold;
        }

        .description p {
            color: #000;
            font-weight: normal;
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }

        /* Responsive for mobile */
        @media (max-width: 768px) {
            .product-container {
                flex-direction: column;
            }

            .product-image {
                height: 300px;
            }
        }
</style>
</head>

<body>
<div class="container">
    <div class="product-container">
        <div class="product-image">
            <img src="<?= htmlspecialchars($product['anh'] ?? 'images/fallback.jpg') ?>" alt="Ảnh sản phẩm">
        </div>
        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['tensp'] ?? '') ?></h1>
            <div class="product-price"><?= number_format($product['gia'], 0, ',', '.') ?> VNĐ</div>
            <div class="discount">MÃ GIẢM GIÁ: 1K</div>

                    <div class="specifications">
                        <h3>THÔNG SỐ KỸ THUẬT</h3>
                        <table>
                            <tr>
                                <th>Chất liệu</th>
                                <td><?php echo htmlspecialchars($product['chatlieu'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Màu sắc</th>
                                <td><?php echo htmlspecialchars($product['mau'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Hình thức</th>
                                <td><?php echo htmlspecialchars($product['hinhthuc'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
            <form method="post" class="action-group">
                <div class="quantity-cart-group">
                    <input type="number" class="quantity-input" name="soluong" value="1" min="1">
                    <button type="submit" name="add_to_cart" class="add-to-cart">THÊM VÀO GIỎ HÀNG</button>
                </div>
                <button type="button" class="buy-now">MUA NGAY</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Thanh Toán -->
<div id="checkoutModal" class="modal">
  <div class="modal-content">
    <h2>Xác nhận thanh toán</h2>
    <div id="modalProductInfo" style="margin-bottom:15px;"></div>
    <form method="post">
      <input type="hidden" name="soluong" id="hiddenSoluong">
      <label>Họ tên:</label>
      <input type="text" name="hoten" required>

      <label>Địa chỉ:</label>
      <input type="text" name="diachi" required>

      <label>Số điện thoại:</label>
      <input type="text" name="sdt" required pattern="[0-9]{10,11}">

      <label>Phương thức thanh toán:</label>
      <select name="pttt" required>
        <option value="Tiền mặt khi nhận hàng">Thanh toán khi nhận hàng</option>
        <option value="Chuyển khoản ngân hàng">Chuyển khoản ngân hàng</option>
        <option value="Ví điện tử">Ví điện tử</option>
      </select>

      <div class="modal-actions">
        <button type="submit" name="xac_nhan_thanh_toan">Xác nhận</button>
        <button type="button" id="closeModal">Hủy</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('checkoutModal');
  const buyNowBtn = document.querySelector('.buy-now');
  const closeBtn = document.getElementById('closeModal');
  const quantityInput = document.querySelector('.quantity-input');
  const productInfoDiv = document.getElementById('modalProductInfo');
  const hiddenSoluong = document.getElementById('hiddenSoluong');

  buyNowBtn.addEventListener('click', () => {
    const name = document.querySelector('.product-title').textContent;
    const price = document.querySelector('.product-price').textContent;
    const img = document.querySelector('.product-image img').src;
    const soluong = quantityInput.value;

    hiddenSoluong.value = soluong;

    productInfoDiv.innerHTML = `
      <div style="display:flex;align-items:center;gap:10px;">
        <img src="${img}" style="width:60px;height:60px;border-radius:8px;object-fit:cover;">
        <div>
          <strong>${name}</strong><br>
          <span>Giá: ${price}</span><br>
          <span>Số lượng: ${soluong}</span>
        </div>
      </div>`;
    modal.style.display = 'flex';
  });

  closeBtn.addEventListener('click', () => modal.style.display = 'none');
  window.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });
});
</script>
</body>
</html>
