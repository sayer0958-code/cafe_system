<?php 
include 'db.php';
include 'header.php'; // يحتوي على متغير $is_admin
$msg = "";

// حفظ التقفيل فقط لو الموّظف/الزائر مش هو اللي بيحاول، بل المدير
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    $date = $_POST['date'];
    $sales = floatval($_POST['total_sales']);
    $notes = $_POST['notes'];

    $total_expenses = 0;
    if (isset($_POST['expense_amount']) && is_array($_POST['expense_amount'])) {
        foreach ($_POST['expense_amount'] as $amt) { $total_expenses += floatval($amt); }
    }

    $total_wages = 0;
    if (isset($_POST['wage_amount']) && is_array($_POST['wage_amount'])) {
        foreach ($_POST['wage_amount'] as $w) { $total_wages += floatval($w); }
    }

    $net_cash = $sales - ($total_expenses + $total_wages);

    $sql = "INSERT INTO daily_settlements (date, total_sales, total_expenses, total_daily_wages, net_daily_cash, notes) 
            VALUES ('$date', $sales, $total_expenses, $total_wages, $net_cash, '$notes')";

    if ($conn->query($sql) === TRUE) {
        $settlement_id = $conn->insert_id;

        if (isset($_POST['expense_category'])) {
            for ($i = 0; $i < count($_POST['expense_category']); $i++) {
                $cat = $_POST['expense_category'][$i];
                $desc = $_POST['expense_desc'][$i];
                $amt = floatval($_POST['expense_amount'][$i]);
                if ($amt > 0) {
                    $conn->query("INSERT INTO daily_expenses (settlement_id, category, description, amount) VALUES ($settlement_id, '$cat', '$desc', $amt)");
                }
            }
        }

        if (isset($_POST['emp_name'])) {
            for ($i = 0; $i < count($_POST['emp_name']); $i++) {
                $emp = $_POST['emp_name'][$i];
                $amt = floatval($_POST['wage_amount'][$i]);
                if ($amt > 0) {
                    $conn->query("INSERT INTO daily_wages (settlement_id, employee_name, amount) VALUES ($settlement_id, '$emp', $amt)");
                }
            }
        }
        $msg = "<p style='color: green; font-weight: bold;'>تم تقفيل اليومية بنجاح!</p>";
    }
}

// جلب البيانات لعرضها للجميع
$settlements_list = $conn->query("SELECT * FROM daily_settlements ORDER BY date DESC");
?>

<h2>حركة اليوم وتقفيل الخزنة</h2>
<?php echo $msg; ?>

<?php if ($is_admin): ?>
    <!-- نموذج الإدخال يظهر للمدير فقط -->
    <form method="POST" action="" autocomplete="off" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 25px;">
        <h3>تسجيل شيفت جديد</h3>
        <label>تاريخ اليوم:</label>
        <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required />

        <label>إجمالي مبيعات اليوم (الدرج):</label>
        <input type="number" step="0.01" name="total_sales" placeholder="أدخل المبيعات الإجمالية" required />

        <h3>المصاريف النثرية</h3>
        <div id="expenses_wrapper">
            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                <input type="text" name="expense_category[]" placeholder="النوع" />
                <input type="text" name="expense_desc[]" placeholder="البيان" />
                <input type="number" step="0.01" name="expense_amount[]" placeholder="المبلغ" />
            </div>
        </div>
        <button type="button" onclick="addExpenseRow()" style="background:#64748b; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; margin-bottom:15px;">+ مصروف آخر</button>

        <h3>يوميات وسلف العمال</h3>
        <div id="wages_wrapper">
            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                <input type="text" name="emp_name[]" placeholder="اسم العامل" />
                <input type="number" step="0.01" name="wage_amount[]" placeholder="المبلغ" />
            </div>
        </div>
        <button type="button" onclick="addWageRow()" style="background:#64748b; color:#fff; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; margin-bottom:15px;">+ عامل آخر</button>

        <label>ملاحظات:</label>
        <input type="text" name="notes" placeholder="ملاحظات طارئة" />

        <button type="submit" class="btn" style="margin-top: 15px;">حفظ وتقفيل الخزنة</button>
    </form>

    <script>
    function addExpenseRow() {
        var div = document.createElement('div');
        div.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px;';
        div.innerHTML = '<input type="text" name="expense_category[]" placeholder="النوع" /><input type="text" name="expense_desc[]" placeholder="البيان" /><input type="number" step="0.01" name="expense_amount[]" placeholder="المبلغ" />';
        document.getElementById('expenses_wrapper').appendChild(div);
    }
    function addWageRow() {
        var div = document.createElement('div');
        div.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px;';
        div.innerHTML = '<input type="text" name="emp_name[]" placeholder="اسم العامل" /><input type="number" step="0.01" name="wage_amount[]" placeholder="المبلغ" />';
        document.getElementById('wages_wrapper').appendChild(div);
    }
    </script>
<?php else: ?>
    <p style="background: #e2e8f0; padding: 12px; border-radius: 8px; color: #475569; font-weight: bold; margin-bottom: 25px;">
        ℹ️ وضع المشاهدة فقط: يمكنك الاطلاع على حركة الخزنة أدناه. للإضافة أو التعديل يرجى <a href="login.php">تسجيل الدخول كمدير</a>.
    </p>
<?php endif; ?>

<h3>سجل الحركات السابقة</h3>
<table>
    <tr>
        <th>التاريخ</th>
        <th>المبيعات</th>
        <th>المصاريف</th>
        <th>الرواتب</th>
        <th>الصافي</th>
        <th>ملاحظات</th>
    </tr>
    <?php 
    if ($settlements_list && $settlements_list->num_rows > 0) {
        while($row = $settlements_list->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['date'] . "</td>";
            echo "<td style='color:green; font-weight:bold;'>" . number_format($row['total_sales'], 2) . " ج.م</td>";
            echo "<td style='color:red;'>" . number_format($row['total_expenses'], 2) . " ج.م</td>";
            echo "<td style='color:orange;'>" . number_format($row['total_daily_wages'], 2) . " ج.م</td>";
            echo "<td style='font-weight:bold;'>" . number_format($row['net_daily_cash'], 2) . " ج.م</td>";
            echo "<td>" . $row['notes'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>لا توجد حركات مسجلة.</td></tr>";
    }
    ?>
</table>

<?php include 'footer.php'; ?>