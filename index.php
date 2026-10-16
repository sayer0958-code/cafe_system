<?php 
include 'db.php';
include 'header.php';

// جلب إحصائيات سريعة للرئيسية
$today = date('Y-m-d');
$today_settlement = $conn->query("SELECT * FROM daily_settlements WHERE date = '$today'")->fetch_assoc();
$total_debts = $conn->query("SELECT SUM(remaining_amount) as total FROM customer_debts WHERE status != 'خالص'")->fetch_assoc();
?>

<h2>لوحة التحكم الرئيسية</h2>

<!-- رسالة توضيحية تتغير حسب حالة الدخول -->
<?php if ($is_admin): ?>
    <p style="background: #dcfce7; padding: 12px; border-radius: 8px; color: #15803d; font-weight: bold; border: 1px solid #bbf7d0;">
        ⚡ مرحباً بك يا مدير! أنت الآن في وضع (الإدارة والتعديل). يمكنك إضافة وحفظ البيانات في جميع الأقسام.
    </p>
<?php else: ?>
    <p style="background: #e2e8f0; padding: 12px; border-radius: 8px; color: #475569; font-weight: bold;">
        ℹ️ مرحباً بك! أنت الآن في وضع (العرض فقط). يمكنك تصفح كروت البيانات والتقارير بأمان.
    </p>
<?php endif; ?>

<!-- كروت إحصائيات البيانات للعرض فقط -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: #eff6ff; padding: 20px; border-radius: 12px; border: 1px solid #bfdbfe;">
        <h4 style="margin:0; color:#1e40af;">مبيعات اليوم</h4>
        <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color:#1e3a8a;">
            <?php echo number_format($today_settlement['total_sales'] ?? 0, 2); ?> ج.م
        </p>
    </div>

    <div style="background: #fef2f2; padding: 20px; border-radius: 12px; border: 1px solid #fecaca;">
        <h4 style="margin:0; color:#991b1b;">مصاريف اليوم</h4>
        <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color:#7f1d1d;">
            <?php echo number_format($today_settlement['total_expenses'] ?? 0, 2); ?> ج.م
        </p>
    </div>

    <div style="background: #f0fdf4; padding: 20px; border-radius: 12px; border: 1px solid #bbf7d0;">
        <h4 style="margin:0; color:#166534;">صافي الخزنة اليوم</h4>
        <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color:#14532d;">
            <?php echo number_format($today_settlement['net_daily_cash'] ?? 0, 2); ?> ج.م
        </p>
    </div>

    <div style="background: #fffbe2; padding: 20px; border-radius: 12px; border: 1px solid #fef08a;">
        <h4 style="margin:0; color:#854d0e;">إجمالي الديون الخارجية</h4>
        <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color:#713f12;">
            <?php echo number_format($total_debts['total'] ?? 0, 2); ?> ج.م
        </p>
    </div>
</div>

<h3>استعراض أقسام النظام</h3>
<div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
    <a href="daily.php" class="btn" style="text-decoration:none;">عرض حركة اليوم</a>
    <a href="debts.php" class="btn" style="text-decoration:none; background:#3b82f6;">عرض ديون العملاء</a>
    <a href="inventory.php" class="btn" style="text-decoration:none; background:#8b5cf6;">عرض جرد المخزن</a>
</div>

<?php if ($is_admin): ?>
    <!-- أي خيارات سريعة إضافية للمدير فقط توضع هنا -->
<?php endif; ?>

<?php include 'footer.php'; ?>