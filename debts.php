<?php 
include 'db.php';
include 'header.php';
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    if (isset($_POST['add_debt'])) {
        $customer = trim($_POST['customer_name']);
        $amount = floatval($_POST['total_amount']);
        $paid = floatval($_POST['paid_amount']);
        $desc = trim($_POST['description']);
        $date = $_POST['debt_date'];
        
        $remaining = $amount - $paid;
        $status = ($remaining <= 0) ? 'خالص' : (($paid > 0) ? 'سداد جزئي' : 'معلق');
        
        // تسجيل كل طلب كحركة مستقلة داخل الجدول لضمان حفظ سعره وتاريخه بدقة للتفاصيل
        $sql = "INSERT INTO customer_debts (customer_name, total_amount, paid_amount, remaining_amount, description, date, status) 
                VALUES ('$customer', $amount, $paid, $remaining, '$desc', '$date', '$status')";
        $conn->query($sql);
        $msg = "<p style='color: green; font-weight: bold;'>تم تسجيل الطلب وإضافة الحساب للعميل بنجاح!</p>";
    } 
    elseif (isset($_POST['pay_debt'])) {
        $debt_id = intval($_POST['debt_id']);
        $new_payment = floatval($_POST['payment_amount']);
        
        $debt_query = $conn->query("SELECT * FROM customer_debts WHERE id = $debt_id");
        if ($debt_query->num_rows > 0) {
            $debt = $debt_query->fetch_assoc();
            $total_paid = $debt['paid_amount'] + $new_payment;
            $new_remaining = $debt['total_amount'] - $total_paid;
            $status = ($new_remaining <= 0) ? 'خالص' : 'سداد جزئي';

            $conn->query("UPDATE customer_debts SET paid_amount = $total_paid, remaining_amount = $new_remaining, status = '$status' WHERE id = $debt_id");
            $msg = "<p style='color: green; font-weight: bold;'>تم تسجيل السداد بنجاح!</p>";
        }
    }
}

// تجميع إجمالي الحسابات لكل عميل لعرضها في الجدول الرئيسي
$debts_grouped = $conn->query("
    SELECT 
        customer_name,
        MAX(date) as latest_date,
        SUM(total_amount) as grand_total,
        SUM(paid_amount) as grand_paid,
        SUM(remaining_amount) as grand_remaining
    FROM customer_debts 
    GROUP BY customer_name 
    ORDER BY grand_remaining DESC, latest_date DESC
");

// التحقق إذا كان المدير طلب عرض تفاصيل عميل معين
$view_customer = isset($_GET['details']) ? $_GET['details'] : '';
?>

<h2>إدارة ديون وحسابات الآجل للعملاء</h2>
<?php echo $msg; ?>

<?php if ($view_customer): ?>
    <!-- شاشة تفاصيل العميل المحدد -->
    <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #cbd5e1; margin-bottom: 25px;">
        <h3>تفاصيل طلبات وحساب العميل: <span style="color:#3b82f6;"><?php echo htmlspecialchars($view_customer); ?></span></h3>
        <table>
            <tr>
                <th>التاريخ</th>
                <th>الطلبات</th>
                <th>الإجمالي</th>
                <th>المدفوع</th>
                <th>المتبقي</th>
                <th>الحالة</th>
            </tr>
            <?php 
            $cust_details = $conn->query("SELECT * FROM customer_debts WHERE customer_name = '$view_customer' ORDER BY date DESC");
            while($det = $cust_details->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $det['date'] . "</td>";
                echo "<td>" . $det['description'] . "</td>";
                echo "<td>" . number_format($det['total_amount'], 2) . " ج.م</td>";
                echo "<td style='color:green;'>" . number_format($det['paid_amount'], 2) . " ج.م</td>";
                echo "<td style='color:red; font-weight:bold;'>" . number_format($det['remaining_amount'], 2) . " ج.م</td>";
                echo "<td style='font-weight:bold; color:" . ($det['status'] == 'خالص' ? 'green' : 'red') . ";'>" . $det['status'] . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
        <br>
        <a href="debts.php" class="btn" style="background: #64748b; text-decoration: none; display: inline-block; width: auto; padding: 8px 16px;">العودة للقائمة الرئيسية</a>
    </div>
<?php endif; ?>

<?php if ($is_admin): ?>
    <form method="POST" action="" autocomplete="off" style="background: #fff; padding: 25px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #cbd5e1;">
        <h3 style="margin-top:0; color:#1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">إضافة طلبات / حساب لعميل (الإدارة)</h3>
        <input type="hidden" name="add_debt" value="1">

        <label>اسم الزبون / العميل:</label>
        <input type="text" name="customer_name" placeholder="أدخل اسم الزبون" required autocomplete="off" />

        <label>التاريخ:</label>
        <input type="date" name="debt_date" value="<?php echo date('Y-m-d'); ?>" required autocomplete="off" />

        <label>تفاصيل الطلبات (مثلاً: 2 شاي، 1 مانجا...):</label>
        <input type="text" name="description" placeholder="تفاصيل الطلب" required autocomplete="off" />

        <label>المبلغ المطلوب (جنيه):</label>
        <input type="number" step="0.01" name="total_amount" placeholder="أدخل السعر المطلوب" required autocomplete="off" />

        <label>المبلغ المدفوع (إن وجد):</label>
        <input type="number" step="0.01" name="paid_amount" value="0" autocomplete="off" />

        <button type="submit" class="btn" style="margin-top: 10px;">حفظ الحساب</button>
    </form>
<?php else: ?>
    <p style="background: #e2e8f0; padding: 12px; border-radius: 8px; color: #475569; font-weight: bold; margin-bottom: 20px;">
        ℹ️ أنت في وضع (العرض فقط). لعرض الخيارات والتعديل، يرجى تسجيل الدخول كمدير.
    </p>
<?php endif; ?>

<h3>سجل الحسابات الإجمالية للعملاء</h3>
<table>
    <tr>
        <th>أحدث تاريخ</th>
        <th>اسم العميل</th>
        <th>إجمالي الحساب</th>
        <th>إجمالي المدفوع</th>
        <th>المتبقي عليه</th>
        <th>الحالة العامة</th>
        <th>التفاصيل</th>
        <?php if ($is_admin): ?>
            <th>تسديد مبلغ</th>
        <?php endif; ?>
    </tr>
    <?php 
    if ($debts_grouped && $debts_grouped->num_rows > 0) {
        while($row = $debts_grouped->fetch_assoc()) {
            $remaining = $row['grand_remaining'];
            $status_text = ($remaining <= 0) ? 'خالص' : (($row['grand_paid'] > 0) ? 'سداد جزئي' : 'معلق');
            $status_color = ($remaining <= 0) ? 'green' : 'red';
            
            echo "<tr>";
            echo "<td>" . $row['latest_date'] . "</td>";
            echo "<td><strong>" . $row['customer_name'] . "</strong></td>";
            echo "<td>" . number_format($row['grand_total'], 2) . " ج.م</td>";
            echo "<td style='color: green;'>" . number_format($row['grand_paid'], 2) . " ج.م</td>";
            echo "<td style='color: red; font-weight: bold; font-size: 15px;'>" . number_format($remaining, 2) . " ج.م</td>";
            echo "<td style='color: $status_color; font-weight: bold;'>" . $status_text . "</td>";
            
            // زرار التفاصيل لكل عميل
            echo "<td><a href='debts.php?details=" . urlencode($row['customer_name']) . "' style='background: #8b5cf6; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold;'>التفاصيل</a></td>";
            
            if ($is_admin) {
                echo "<td>";
                if ($remaining > 0) {
                    // لجلب أول سجل غير خالص لهذا العميل لتسديده
                    $first_open = $conn->query("SELECT id FROM customer_debts WHERE customer_name = '" . $row['customer_name'] . "' AND remaining_amount > 0 ORDER BY id ASC LIMIT 1")->fetch_assoc();
                    if ($first_open) {
                        echo '<form method="POST" action="" style="display:flex; gap:5px; margin:0; justify-content:center;">';
                        echo '<input type="hidden" name="pay_debt" value="1">';
                        echo '<input type="hidden" name="debt_id" value="' . $first_open['id'] . '">';
                        echo '<input type="number" step="0.01" name="payment_amount" placeholder="المبلغ" style="width:80px; padding:6px; margin:0; display:inline-block;" required autocomplete="off">';
                        echo '<button type="submit" class="btn" style="padding:6px 12px; font-size:12px; width:auto; background:#3b82f6;">سداد</button>';
                        echo '</form>';
                    }
                } else {
                    echo '<span style="color:gray;">خالص</span>';
                }
                echo "</td>";
            }
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='" . ($is_admin ? 8 : 7) . "'>لا توجد ديون مسجلة.</td></tr>";
    }
    ?>
</table>

<?php include 'footer.php'; ?>
