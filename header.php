<?php
session_start();
// فحص حالة تسجيل الدخول بدون تحويل إجباري
$is_admin = isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الكافيه الاحترافي</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e293b;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --card-bg: rgba(255, 255, 255, 0.96);
            --text-color: #1e293b;
        }

        @keyframes pageFloat {
            0% { opacity: 0; transform: scale(0.98) translateY(12px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        body {
            font-family: 'Cairo', Tahoma, sans-serif;
            background: linear-gradient(rgba(15, 23, 42, 0.88), rgba(15, 23, 42, 0.88)), url('cafe_bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 25px;
            color: var(--text-color);
            min-height: 100vh;
        }

        .wrapper {
            max-width: 1100px;
            margin: auto;
            animation: pageFloat 0.4s ease-out forwards;
        }

        /* شريط التنقل العلوي */
        .navbar {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(12px);
            padding: 15px 20px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 9px 15px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background: rgba(59, 130, 246, 0.2);
            color: #fff;
            transform: translateY(-2px);
        }

        /* كارت المحتوى الرئيسي */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        h2, h3 {
            color: #0f172a;
            margin-top: 0;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            font-weight: 800;
        }

        /* تنسيق وحيد وواضح لجميع النماذج وحقول الإدخال */
        form label {
            display: block;
            margin-top: 12px;
            margin-bottom: 6px;
            font-weight: 700;
            color: #334155;
            font-size: 14px;
        }

        form input[type="text"],
        form input[type="date"],
        form input[type="number"],
        form select {
            display: block;
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 12px;
            border: 2px solid #cbd5e1;
            border-radius: 10px;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        form input:focus, form select:focus {
            border-color: var(--accent-color);
            outline: none;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        .btn {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 800;
            font-family: 'Cairo', sans-serif;
            font-size: 15px;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.4);
        }

        /* الجداول */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        th, td {
            padding: 14px 15px;
            text-align: center;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        th {
            background: #f8fafc;
            color: #1e293b;
            font-weight: 800;
        }

        tr {
            transition: background 0.2s ease;
        }

        tr:hover {
            background: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="navbar">
            <div class="nav-links">
                <a href="index.php">الرئيسية</a>
                <a href="daily.php">حركة اليوم</a>
                <a href="expenses.php">المصاريف</a>
                <a href="suppliers.php">الموردين</a>
                <a href="debts.php">ديون العملاء</a>
                <a href="inventory.php">جرد المخزن</a>
                <a href="monthly_report.php">التقرير المخصص</a>
            </div>
            <div>
                <?php if ($is_admin): ?>
                    <span style="color:#10b981; font-weight:bold; font-size:13px; margin-left:10px;">[حساب الإدارة]</span>
                    <a href="logout.php" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight:bold;">خروج</a>
                <?php else: ?>
                    <a href="login.php" style="background: rgba(59, 130, 246, 0.3); color: #93c5fd; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight:bold;">دخول الإدارة</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">