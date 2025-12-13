<?php
// C:\xampp\htdocs\School_Portal\admin\payment_history.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$student_id = isset($_GET['student_id']) ? clean_input($_GET['student_id']) : '';

if (empty($student_id)) {
    header("Location: payments.php");
    exit();
}

// Fetch student info
$student_sql = "SELECT firstname, lastname, total_fee FROM students WHERE id = '$student_id'";
$student_result = $conn->query($student_sql);

if ($student_result->num_rows == 0) {
    header("Location: payments.php");
    exit();
}

$student = $student_result->fetch_assoc();

// Fetch payment history
$payments_sql = "SELECT * FROM payment_transactions WHERE student_id = '$student_id' ORDER BY payment_date DESC";
$payments = $conn->query($payments_sql);

// Calculate totals
$total_paid_sql = "SELECT COALESCE(SUM(amount), 0) as total FROM payment_transactions WHERE student_id = '$student_id'";
$total_result = $conn->query($total_paid_sql);
$total_row = $total_result->fetch_assoc();
$total_paid = $total_row['total'];
$balance = $student['total_fee'] - $total_paid;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .summary-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .summary-item {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 6px;
        }
        .summary-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);">Payment History</h2>
            <a href="payments.php" class="btn" style="background: #6c757d; font-size: 0.9rem;">&larr; Back to Payments</a>
        </div>

        <div class="summary-card">
            <h3 style="margin: 0 0 5px 0; color: white;">
                <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>
            </h3>
            
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Fee</div>
                    <div class="summary-value">₱<?php echo number_format($student['total_fee'], 2); ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Paid</div>
                    <div class="summary-value">₱<?php echo number_format($total_paid, 2); ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Balance</div>
                    <div class="summary-value">₱<?php echo number_format($balance, 2); ?></div>
                </div>
            </div>
        </div>

        <h3 style="margin: 30px 0 15px 0; color: var(--primary-dark);">Transaction History</h3>
        
        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px; text-align: left; color: #555; font-weight: 600;">Date</th>
                        <th style="padding: 15px; text-align: left; color: #555; font-weight: 600;">Amount</th>
                        <th style="padding: 15px; text-align: left; color: #555; font-weight: 600;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payments->num_rows > 0): ?>
                        <?php while($payment = $payments->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; color: #666;">
                                    <?php echo date('M d, Y g:i A', strtotime($payment['payment_date'])); ?>
                                </td>
                                <td style="padding: 15px; color: #2ecc71; font-weight: 600; font-size: 1.1rem;">
                                    ₱<?php echo number_format($payment['amount'], 2); ?>
                                </td>
                                <td style="padding: 15px; color: #666;">
                                    <?php echo htmlspecialchars($payment['notes'] ?: '-'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="padding: 30px; text-align: center; color: #888;">No payment transactions yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
