<?php 
include 'db.php';
include 'header.php';
$msg = "";

// حفظ التوريد/الخصم للمدير فقط
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $item = trim($_POST['item_name']);
    $q_in = intval($_POST['quantity_in']);
    $q_out = intval($_POST['quantity_out']);
    $notes = $_POST['notes'];
    $date = $_POST['inv_date'];

    if (!empty($item)) {
        $sql = "INSERT INTO inventory (item_name, quantity_in, quantity_out, notes, date) VALUES ('$item', $q_in, $q_out, '$notes', '$date')";
        if ($conn->query($sql) === TRUE) {
            $msg = "<p style='color: green; font-weight: bold;'>تم تسجيل حركة المخزن بنجاح!</p>";
        } else {
            $msg = "<p style='color: red;'>خطأ: " . $conn->error . "</p>";
        }
    }
}

// جلب حركات المخزن
$inventory_list = $conn->query("SELECT * FROM inventory ORDER BY date DESC");

// إحصائيات المخزن للكروت
$totals = $conn->query("SELECT SUM(quantity_in) as total_in, SUM(quantity_out) as total_out FROM inventory")->fetch_assoc();
$total_in = $totals['total_in'] ?? 0;
$total_out = $totals['total_out'] ?? 0;
$current_stock = $total_in - $total_out;
?>

<h2>جرد حركة المخزن والكنزات</h2>
<?php echo $msg; ?>

<!-- كروت ملخص المخزن (تظهر للجميع زوار ومدير) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
    <div style="background: #f0fdf4; padding: 18px; border-radius: 12px; border: 1px solid #bbf7d0;">
        <h4 style="margin:0; color:#166534;">إجمالي الوارد (الداخل)</h4>
        <p style="font-size: 22px; font-weight: bold; margin: 8px 0 0 0; color:#14532d;"><?php echo $total_in; ?> قطعة</p>
    </div>
    <div style="background: #fef2f2; padding: 18px; border-radius: 12px; border: 1px solid #fecaca;">
        <h4 style="margin:0; color:#991b1b;">إجمالي المنصرف (الخارج)</h4>
        <p style="font-size: 22px; font-weight: bold; margin: 8px 0 0 0; color:#7f1d1d;"><?php echo $total_out; ?> قطعة</p>
    </div>
    <div style="background: #eff6ff; padding: 18px; border-radius: 12px; border: 1px solid #bfdbfe;">
        <h4 style="margin:0; color:#1e40af;">الرصيد المتبقي بالمخزن</h4>
        <p style="font-size: 22px; font-weight: bold; margin: 8px 0 0 0; color:#1e3a8a;"><?php echo $current_stock; ?> قطعة</p>
    </div>
</div>

<?php if ($is_admin): ?>
    <!-- حقول الإدخال تظهر فقط للمدير -->
    <form method="POST" action="" autocomplete="off" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #e2e8f0;">
        <h3>إضافة/تعديل حركة مخزن (الإدارة)</h3>
        <label>تاريخ الحركة:</label>
        <input type="date" name="inv_date" value="<?php echo date('Y-m-d'); ?>" required />

        <label>اسم الصنف (مثلاً: بيبسي، كنز ليمون):</label>
        <input type="text" name="item_name" placeholder="أدخل اسم الصنف" required />

        <label>الكمية الداخلة (شراء / توريد):</label>
        <input type="number" name="quantity_in" value="0" required />

        <label>الكمية الخارجة (مبيعات / استهلاك):</label>
        <input type="number" name="quantity_out" value="0" required />

        <label>ملاحظات:</label>
        <input type="text" name="notes" placeholder="ملاحظات" />

        <button type="submit" class="btn" style="margin-top: 10px;">حفظ الحركة</button>
    </form>
<?php endif; ?>

<!-- جدول سجل المخزن بدون أي input للزائر -->
<h3>سجل حركات المخزن</h3>
<table>
    <tr>
        <th>التاريخ</th>
        <th>اسم الصنف</th>
        <th>الداخل</th>
        <th>الخارج</th>
        <th>الرصيد المتبقي</th>
        <th>ملاحظات</th>
    </tr>
    <?php 
    if ($inventory_list && $inventory_list->num_rows > 0) {
        while($row = $inventory_list->fetch_assoc()) {
            $balance = $row['quantity_in'] - $row['quantity_out'];
            echo "<tr>";
            echo "<td>" . $row['date'] . "</td>";
            echo "<td><strong>" . $row['item_name'] . "</strong></td>";
            echo "<td style='color: green; font-weight:bold;'>" . $row['quantity_in'] . "</td>";
            echo "<td style='color: red; font-weight:bold;'>" . $row['quantity_out'] . "</td>";
            echo "<td style='font-weight: bold; color: #1e3a8a;'>" . $balance . "</td>";
            echo "<td>" . ($row['notes'] ? $row['notes'] : '-') . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>لا توجد حركات مخزن مسجلة بعد.</td></tr>";
    }
    ?>
</table>

<?php include 'footer.php'; ?>