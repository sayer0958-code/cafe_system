<?php 
include 'db.php';
include 'header.php'; // الكود يحتوي على $is_admin وزرار الدخول في الهيدر
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $date = $_POST['expense_date'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $amount = floatval($_POST['amount']);
    
    $check_date = $conn->query("SELECT id FROM daily_settlements WHERE date = '$date'");
    if ($check_date && $check_date->num_rows > 0) {
        $row = $check_date->fetch_assoc();
        $settlement_id = $row['id'];
        
        $conn->query("INSERT INTO daily_expenses (settlement_id, category, description, amount) VALUES ($settlement_id, '$category', '$description', $amount)");
        $conn->query("UPDATE daily_settlements SET total_expenses = total_expenses + $amount, net_daily_cash = total_sales - (total_expenses + $amount) - total_daily_wages WHERE id = $settlement_id");
        $msg = "<p style='color: green; font-weight: bold;'>تم إضافة المصروف بنجاح!</p>";
    } else {
        $msg = "<p style='color: red;'>تنبيه: التاريخ ده مش معمول له تقفيل اليوم الأول في (حركة اليوم).</p>";
    }
}

$expenses_list = $conn->query("SELECT de.*, ds.date FROM daily_expenses de JOIN daily_settlements ds ON de.settlement_id = ds.id ORDER BY ds.date DESC");
?>

<h2>سجل المصاريف النثرية</h2>
<?php echo $msg; ?>

<?php if ($is_admin): ?>
    <!-- نموذج إضافة المصروف يظهر للمدير فقط بعد الدخول من زر الإعدادات -->
    <form method="POST" action="" autocomplete="off" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 25px;">
        <h3>إضافة مصروف جديد (الإدارة)</h3>
        <label>التاريخ:</label>
        <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required />

        <label>النوع:</label>
        <input type="text" name="category" placeholder="مثال: صيانة، أدوات نظافة" required />

        <label>البيان والتفاصيل:</label>
        <input type="text" name="description" placeholder="تفاصيل المصروف" required />

        <label>المبلغ (جنيه):</label>
        <input type="number" step="0.01" name="amount" placeholder="أدخل المبلغ" required />

        <button type="submit" class="btn" style="margin-top: 10px;">حفظ المصروف</button>
    </form>
<?php endif; ?>

<!-- الجدول يظهر للجميع زوار ومدير -->
<h3>أرشيف المصاريف المسجلة</h3>
<table>
    <tr>
        <th>التاريخ</th>
        <th>النوع</th>
        <th>البيان</th>
        <th>المبلغ</th>
    </tr>
    <?php 
    if ($expenses_list && $expenses_list->num_rows > 0) {
        while($row = $expenses_list->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['date'] . "</td>";
            echo "<td>" . $row['category'] . "</td>";
            echo "<td>" . $row['description'] . "</td>";
            echo "<td style='color: red; font-weight: bold;'>" . number_format($row['amount'], 2) . " ج.م</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>لا توجد مصاريف مسجلة.</td></tr>";
    }
    ?>
</table>

<?php include 'footer.php'; ?>