<?php
// C:\xampp\htdocs\School_Portal\student\payments.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_login();
if ($_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_user_id = $_SESSION['user_id'];

// Fetch student info
$student_sql = "SELECT s.*, CONCAT(s.firstname, ' ', s.lastname) AS fullname 
                FROM students s 
                WHERE s.user_id = '$student_user_id'";
$student_res = $conn->query($student_sql);
$student = $student_res->fetch_assoc();
$student_id = $student['id'];
$total_fee = floatval($student['total_fee']);

// Calculate total paid from completed transactions
$paid_sql = "SELECT COALESCE(SUM(amount), 0) AS total_paid 
             FROM payment_transactions 
             WHERE student_id = '$student_id' AND status = 'completed'";
$paid_res = $conn->query($paid_sql);
$paid_row = $paid_res->fetch_assoc();
$total_paid = floatval($paid_row['total_paid']);

// Calculate balance
$balance = $total_fee - $total_paid;

// Fetch all payment transactions
$transactions_sql = "SELECT pt.*, 
                            CONCAT(u.username) AS recorded_by_name
                     FROM payment_transactions pt
                     LEFT JOIN users u ON pt.created_by = u.id
                     WHERE pt.student_id = '$student_id'
                     ORDER BY pt.payment_date DESC, pt.created_at DESC";
$transactions_res = $conn->query($transactions_sql);

function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'completed':
            return 'status-completed';
        case 'pending':
            return 'status-pending';
        case 'failed':
            return 'status-failed';
        default:
            return 'status-pending';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payments - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
        }

        .page-title {
            color: #4169E1;
            font-size: 1.5rem;
            margin: 0 0 20px 0;
            font-weight: 600;
        }

        /* Account Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-left: 5px solid;
            transition: transform 0.2s;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .summary-card.total-fee {
            border-left-color: #3498db;
        }

        .summary-card.total-paid {
            border-left-color: #2ecc71;
        }

        .summary-card.balance {
            border-left-color: #e74c3c;
        }

        .summary-label {
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .summary-amount {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .summary-card.total-fee .summary-amount {
            color: #3498db;
        }

        .summary-card.total-paid .summary-amount {
            color: #2ecc71;
        }

        .summary-card.balance .summary-amount {
            color: #e74c3c;
        }

        /* Payment Instructions */
        .payment-notice {
            background: #4169E1;
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .payment-notice h3 {
            margin: 0 0 15px 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .payment-notice p {
            margin: 0 0 10px 0;
            line-height: 1.6;
            opacity: 0.95;
        }

        .payment-notice ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }

        .payment-notice li {
            margin-bottom: 8px;
            opacity: 0.95;
        }

        /* Transaction History */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-header {
            background: #4169E1;
            color: white;
            padding: 20px 25px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .data-table th {
            text-align: left;
            padding: 16px;
            background: #f8fafc;
            color: #333;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #666;
            font-weight: 600;
        }

        .empty-state p {
            margin: 0;
            color: #999;
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            .page-title {
                font-size: 1.3rem;
                margin-bottom: 16px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .summary-card {
                padding: 20px;
            }

            .summary-amount {
                font-size: 1.6rem;
            }

            .payment-notice {
                padding: 20px;
            }

            .data-table th,
            .data-table td {
                padding: 12px 10px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <?php include '../includes/student_sidebar.php'; ?>

        <div class="main-content">
            <h2 class="page-title">My Payments</h2>

            <!-- Account Summary -->
            <div class="summary-grid">
                <div class="summary-card total-fee">
                    <div class="summary-label">Total Fees</div>
                    <div class="summary-amount">₱
                        <?php echo number_format($total_fee, 2); ?>
                    </div>
                </div>
                <div class="summary-card total-paid">
                    <div class="summary-label">Total Paid</div>
                    <div class="summary-amount">₱
                        <?php echo number_format($total_paid, 2); ?>
                    </div>
                </div>
                <div class="summary-card balance">
                    <div class="summary-label">Outstanding Balance</div>
                    <div class="summary-amount">₱
                        <?php echo number_format($balance, 2); ?>
                    </div>
                </div>
            </div>

            <!-- Payment Notice -->
            <div class="payment-notice">
                <h3>💵 Payment Information</h3>
                <p><strong>All payments are CASH ONLY and must be made at the Registrar's Office.</strong></p>
            </div>

            <!-- Transaction History -->
            <div class="card">
                <div class="card-header">Payment Transaction History</div>

                <?php
                $transactions_data = [];
                if ($transactions_res && $transactions_res->num_rows > 0) {
                    while ($row = $transactions_res->fetch_assoc()) {
                        $transactions_data[] = $row;
                    }
                }

                if (!empty($transactions_data)):
                    ?>
                    <!-- Desktop Table View -->
                    <div class="table-container desktop-only">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Payment Type</th>
                                    <th>Amount</th>
                                    <th>Reference #</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions_data as $transaction): ?>
                                    <tr>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($transaction['payment_date'])); ?>
                                        </td>
                                        <td><strong>
                                                <?php echo htmlspecialchars($transaction['payment_type']); ?>
                                            </strong></td>
                                        <td style="font-weight: 600; color: #2ecc71;">₱
                                            <?php echo number_format($transaction['amount'], 2); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($transaction['reference_number'] ?: '-'); ?>
                                        </td>
                                        <td>
                                            <span
                                                class="status-badge <?php echo getStatusBadgeClass($transaction['status']); ?>">
                                                <?php echo ucfirst($transaction['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($transaction['notes'] ?: '-'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="mobile-only">
                        <?php foreach ($transactions_data as $transaction): ?>
                            <div class="mobile-card">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                    <div style="font-weight: 600; color: #4169E1;">
                                        <?php echo htmlspecialchars($transaction['payment_type']); ?>
                                    </div>
                                    <span class="status-badge <?php echo getStatusBadgeClass($transaction['status']); ?>"
                                        style="padding: 4px 8px; font-size: 0.75rem;">
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                                    <div style="font-size: 0.85rem; color: #666;">
                                        <div style="margin-bottom: 4px;">📅
                                            <?php echo date('M d, Y', strtotime($transaction['payment_date'])); ?></div>
                                        <?php if ($transaction['reference_number']): ?>
                                            <div style="font-family: monospace; font-size: 0.8rem;">Ref:
                                                <?php echo htmlspecialchars($transaction['reference_number']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 1.1rem; font-weight: 700; color: #2ecc71;">
                                        ₱<?php echo number_format($transaction['amount'], 2); ?>
                                    </div>
                                </div>
                                <?php if ($transaction['notes']): ?>
                                    <div
                                        style="margin-top: 8px; font-size: 0.85rem; color: #888; border-top: 1px dashed #eee; padding-top: 5px;">
                                        📝 <?php echo htmlspecialchars($transaction['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <h3>No Payment Transactions Yet</h3>
                        <p>Your payment history will appear here once you make your first payment at the Registrar's Office.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>
