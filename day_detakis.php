<?php 
include 'db.php';
include 'header.php'; 

$date = $_GET['date'];

// جلب تفاصيل اليوم الأساسية
$sql = "SELECT * FROM daily_settlements WHERE date = '$date'";
$settlement = $conn->query($sql)->fetch_assoc();

// جلب تفاصيل المصاريف النثرية
$expenses_sql = "SELECT * FROM daily_expenses WHERE settlement_id = " . $settlement['id'];
$expenses = $conn->query($expenses_sql);

// جلب تفاصيل اليوميات
$wages_sql = "SELECT * FROM daily_wages WHERE settlement_id = " . $settlement['id'];
$wages = $conn->query($wages_sql);
?>

<h2>تفاصيل يوم: <?php echo $date; ?></h2>

<div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
    <h3>الملخص المالي</h3>
    <p>إجمالي المبيعات: <?php echo number_format($settlement['total_sales'], 2); ?> ج.م</p>
    <p>إجمالي المصاريف: <?php echo number_format($settlement['total_expenses'], 2); ?> ج.م</p>
    <p>إجمالي اليوميات: <?php echo number_format($settlement['total_daily_wages'], 2); ?> ج.م</p>
    <p style="font-size: 18px; color: green; font-weight: bold;">الصافي المتبقي: <?php echo number_format($settlement['net_daily_cash'], 2); ?> ج.م</p>
</div>

<h3>تفاصيل المصاريف النثرية</h3>
<table>
    <tr>
        <th>النوع</th>
        <th>البيان</th>
        <th>المبلغ</th>
    </tr>
    <?php while($row = $expenses->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['category']; ?></td>
        <td><?php echo $row['description']; ?></td>
        <td><?php echo number_format($row['amount'], 2); ?> ج.م</td>
    </tr>
    <?php endwhile; ?>
</table>

<h3>تفاصيل اليوميات</h3>
<table>
    <tr>
        <th>اسم العامل</th>
        <th>المبلغ</th>
        <th>ملاحظات</th>
    </tr>
    <?php while($row = $wages->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['employee_name']; ?></td>
        <td><?php echo number_format($row['amount'], 2); ?> ج.م</td>
        <td><?php echo $row['notes']; ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<a href="monthly_report.php" class="btn" style="margin-top: 20px;">العودة للتقرير الشهري</a>

<?php include 'footer.php'; ?>