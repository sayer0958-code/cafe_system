<?php 
include 'db.php';
include 'header.php';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    if (isset($_POST['add_invoice'])) {
        $supplier = $_POST['supplier_name'];
        $inv_num = $_POST['invoice_number'];
        $total = floatval($_POST['total_amount']);
        $paid = floatval($_POST['paid_amount']);
        $remaining = $total - $paid;
        $date = $_POST['invoice_date'];

        $conn->query("INSERT INTO supplier_invoices (supplier_name, invoice_number, total_amount, paid_amount, remaining_amount, invoice_date) VALUES ('$supplier', '$inv_num', $total, $paid, $remaining, '$date')");
        $msg = "<p style='color: green; font-weight: bold;'>تم تسجيل الفاتورة بنجاح!</p>";
    }
    elseif (isset($_POST['pay_supplier'])) {
        $id = intval($_POST['inv_id']);
        $payment = floatval($_POST['payment_amount']);
        $inv = $conn->query("SELECT * FROM supplier_invoices WHERE id = $id")->fetch_assoc();
        $new_paid = $inv['paid_amount'] + $payment;
        $new_rem = $inv['total_amount'] - $new_paid;
        $conn->query("UPDATE supplier_invoices SET paid_amount = $new_paid, remaining_amount = $new_rem WHERE id = $id");
        $msg = "<p style='color: green; font-weight: bold;'>تم السداد بنجاح!</p>";
    }
}

$invoices = $conn->query("SELECT * FROM supplier_invoices ORDER BY invoice_date DESC");
?>

<h2>إدارة فواتير الموردين</h2>
<?php echo $msg; ?>

<?php if ($is_admin): ?>
    <form method="POST" action="" autocomplete="off" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 25px;">
        <h3>إضافة فاتورة جديدة</h3>
        <input type="hidden" name="add_invoice" value="1">
        <label>اسم المورد:</label>
        <input type="text" name="supplier_name" required />
        <label>رقم الفاتورة:</label>
        <input type="text" name="invoice_number" required />
        <label>التاريخ:</label>
        <input type="date" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required />
        <label>الإجمالي:</label>
        <input type="number" step="0.01" name="total_amount" required />
        <label>المدفوع:</label>
        <input type="number" step="0.01" name="paid_amount" value="0" required />
        <button type="submit" class="btn" style="margin-top: 10px;">حفظ الفاتورة</button>
    </form>
<?php else: ?>
    <p style="background: #e2e8f0; padding: 12px; border-radius: 8px; color: #475569; font-weight: bold; margin-bottom: 25px;">
        ℹ️ صفحة عرض بيانات الموردين فقط. لتسجيل فاتورة أو سداد يرجى <a href="login.php">تسجيل الدخول كمدير</a>.
    </p>
<?php endif; ?>

<h3>سجل الفواتير</h3>
<table>
    <tr>
        <th>التاريخ</th>
        <th>المورد</th>
        <th>رقم الفاتورة</th>
        <th>الإجمالي</th>
        <th>المدفوع</th>
        <th>المتبقي</th>
        <?php if ($is_admin): ?><th>سداد</th><?php endif; ?>
    </tr>
    <?php 
    if ($invoices && $invoices->num_rows > 0) {
        while($row = $invoices->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['invoice_date'] . "</td>";
            echo "<td><strong>" . $row['supplier_name'] . "</strong></td>";
            echo "<td>" . $row['invoice_number'] . "</td>";
            echo "<td>" . number_format($row['total_amount'], 2) . " ج.م</td>";
            echo "<td style='color: green;'>" . number_format($row['paid_amount'], 2) . " ج.م</td>";
            echo "<td style='color: red; font-weight: bold;'>" . number_format($row['remaining_amount'], 2) . " ج.م</td>";
            
            if ($is_admin) {
                echo "<td>";
                if ($row['remaining_amount'] > 0) {
                    echo '<form method="POST" action="" style="display:flex; gap:5px; margin:0;">';
                    echo '<input type="hidden" name="pay_supplier" value="1">';
                    echo '<input type="hidden" name="inv_id" value="' . $row['id'] . '">';
                    echo '<input type="number" step="0.01" name="payment_amount" placeholder="مبلغ" style="width:70px; padding:6px; margin:0;" required>';
                    echo '<button type="submit" class="btn" style="padding:6px 10px; font-size:12px; width:auto; background:#3b82f6;">سداد</button>';
                    echo '</form>';
                } else {
                    echo '<span style="color:gray;">خالص</span>';
                }
                echo "</td>";
            }
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>لا توجد فواتير.</td></tr>";
    }
    ?>
</table>

<?php include 'footer.php'; ?>