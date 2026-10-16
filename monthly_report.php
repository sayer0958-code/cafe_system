<?php 
include 'db.php';
include 'header.php';
?>

<h2>التقرير المالي المخصص للفترات</h2>

<form method="GET" action="" style="background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 25px; display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; border: 1px solid #e2e8f0;">
    <div style="flex: 1; min-width: 200px;">
        <label>من تاريخ:</label>
        <input type="date" name="start_date" value="<?php echo $_GET['start_date'] ?? date('Y-m-01'); ?>" required style="margin:0;">
    </div>
    <div style="flex: 1; min-width: 200px;">
        <label>إلى تاريخ:</label>
        <input type="date" name="end_date" value="<?php echo $_GET['end_date'] ?? date('Y-m-d'); ?>" required style="margin:0;">
    </div>
    <div>
        <button type="submit" class="btn" style="padding: 12px 25px; width: auto; margin: 0;">عرض التقرير</button>
    </div>
</form>

<?php
// استقبال التواريخ والبحث في قاعدة البيانات
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start = $_GET['start_date'];
    $end = $_GET['end_date'];
    
    $query = $conn->query("SELECT SUM(total_sales) as sales, SUM(total_expenses) as expenses, SUM(total_daily_wages) as wages, SUM(net_daily_cash) as net FROM daily_settlements WHERE date BETWEEN '$start' AND '$end'");
    $report = $query->fetch_assoc();
    
    echo "<div style='background:#f8fafc; padding:25px; border-radius:12px; border: 1px solid #e2e8f0;'>";
    echo "<h3>ملخص الفترة من ($start) إلى ($end)</h3>";
    echo "<p style='font-size: 16px;'>إجمالي المبيعات: <strong>" . number_format($report['sales'] ?? 0, 2) . " ج.م</strong></p>";
    echo "<p style='font-size: 16px;'>إجمالي المصاريف: <strong style='color:red;'>" . number_format($report['expenses'] ?? 0, 2) . " ج.م</strong></p>";
    echo "<p style='font-size: 16px;'>إجمالي الرواتب: <strong style='color:orange;'>" . number_format($report['wages'] ?? 0, 2) . " ج.م</strong></p>";
    echo "<hr style='border:0; border-top:1px solid #cbd5e1; margin: 15px 0;'>";
    echo "<p style='font-size: 18px;'>الصافي العام للخزنة: <strong style='color:green; font-size: 20px;'>" . number_format($report['net'] ?? 0, 2) . " ج.م</strong></p>";
    echo "</div>";
}
?>

<?php include 'footer.php'; ?>