<?php
// C:\xampp\htdocs\School_Portal\admin\payments.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$success = '';
$error = '';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_fee'])) {
        $student_id = clean_input($_POST['student_id']);
        $total_fee = clean_input($_POST['total_fee']);
        
        $stmt = $conn->prepare("UPDATE students SET total_fee = ? WHERE id = ?");
        $stmt->bind_param("di", $total_fee, $student_id);
        
        if ($stmt->execute()) {
            $success = "Total fee updated successfully.";
        } else {
            $error = "Error updating fee: " . $conn->error;
        }
        $stmt->close();

    }
    
    if (isset($_POST['toggle_scholar'])) {
        $student_id = clean_input($_POST['student_id']);
        
        // If currently scholar (1), becoming non-scholar (0) -> fee 9000
        // If currently non-scholar (0), becoming scholar (1) -> fee 0
        $stmt = $conn->prepare("UPDATE students SET total_fee = CASE WHEN is_scholar = 1 THEN 9000.00 ELSE 0.00 END, is_scholar = CASE WHEN is_scholar = 1 THEN 0 ELSE 1 END WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        
        if ($stmt->execute()) {
            $success = "Scholar status and fee updated successfully.";
        } else {
            $error = "Error updating status: " . $conn->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['add_payment'])) {
        $student_id = clean_input($_POST['student_id']);
        $amount = clean_input($_POST['amount']);
        $notes = clean_input($_POST['notes']);
        
        if ($amount > 0) {
            $payment_date = date('Y-m-d');
            $payment_type = 'Payment';
            $payment_method = 'Cash';
            $status = 'completed';
            $created_by = $_SESSION['user_id'];
            
            $stmt = $conn->prepare("INSERT INTO payment_transactions (student_id, amount, payment_type, payment_method, payment_date, status, created_by, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("idssssis", $student_id, $amount, $payment_type, $payment_method, $payment_date, $status, $created_by, $notes);
            
            if ($stmt->execute()) {
                $success = "Payment recorded successfully.";
            } else {
                $error = "Error recording payment: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "Payment amount must be greater than 0.";
        }
    }
}

// Fetch Students with payment calculations
$sql = "
    SELECT 
        s.id, 
        s.firstname, 
        s.lastname, 
        s.total_fee,
        s.is_scholar,
        COALESCE(SUM(pt.amount), 0) as total_paid
    FROM students s
    LEFT JOIN payment_transactions pt ON s.id = pt.student_id
    GROUP BY s.id
    ORDER BY s.lastname ASC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .payment-table td { vertical-align: middle; }
        .input-fee {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 120px;
            background: white;
            transition: border 0.3s;
        }
        .input-fee:focus { border-color: var(--primary-color); outline: none; }
        .input-payment {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100px;
            background: white;
        }
        .btn-small {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background 0.3s;
        }
        .btn-small:hover { background-color: #2980b9; }
        .btn-view {
            background-color: #95a5a6;
            color: white;
            border: none;
            padding: 4px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view:hover { background-color: #7f8c8d; }
        .balance-positive { color: #e74c3c; font-weight: bold; }
        .balance-zero { color: #2ecc71; font-weight: bold; }
        .scholar-badge {
            background: #f39c12;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin: 0; color: var(--primary-color);">Student Payments & Balance</h2>
        </div>

        <?php if ($success): ?>
            <div class="alert success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card" style="padding: 0; overflow: hidden;">
            <table class="payment-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #0056b3; border-bottom: 2px solid #004494;">
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Student Name</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Total Fee</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Amount Paid</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Balance</th>
                        <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Add Payment</th>
                        <th style="padding: 15px; text-align: center; color: white; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php 
                                $total_fee = $row['total_fee'];
                                $total_paid = $row['total_paid'];
                                $balance = $total_fee - $total_paid;
                                $is_scholar = $row['is_scholar'] == 1;
                            ?>
                            <tr style="border-bottom: 1px solid #eee; transition: background 0.1s;" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px; color: #333; font-weight: 500;">
                                    <?php echo htmlspecialchars($row['lastname'] . ', ' . $row['firstname']); ?>
                                    <?php if ($is_scholar): ?>
                                        <span class="scholar-badge">SCHOLAR</span>
                                    <?php endif; ?>
                                    <div style="margin-top: 5px;">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="toggle_scholar" style="background: none; border: none; color: #3498db; cursor: pointer; font-size: 0.75rem; text-decoration: underline; padding: 0;">
                                                <?php echo $is_scholar ? 'Remove Scholar Status' : 'Mark as Scholar'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td style="padding: 15px;">
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                        <input type="number" name="total_fee" class="input-fee" value="<?php echo $total_fee; ?>" step="0.01" min="0">
                                        <button type="submit" name="update_fee" class="btn-small" style="margin-left: 5px;">Update</button>
                                    </form>
                                </td>
                                <td style="padding: 15px; color: #2ecc71; font-weight: 600;">
                                    ₱<?php echo number_format($total_paid, 2); ?>
                                </td>
                                <td style="padding: 15px;">
                                    <span class="<?php echo $balance > 0 ? 'balance-positive' : 'balance-zero'; ?>">
                                        ₱<?php echo number_format($balance, 2); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <form method="POST" action="" style="display: flex; gap: 5px; align-items: center;">
                                        <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                        <input type="number" name="amount" class="input-payment" placeholder="Amount" step="0.01" min="0" required>
                                        <input type="text" name="notes" class="input-payment" placeholder="Notes" style="width: 120px;">
                                        <button type="submit" name="add_payment" class="btn-small">Add</button>
                                    </form>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <a href="payment_history.php?student_id=<?php echo $row['id']; ?>" class="btn-view">View History</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: #888;">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
