<?php
// C:\xampp\htdocs\School_Portal\admin\enrollments.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

check_admin();

$success = '';
$error = '';
$credentials = null;

// Handle Approval/Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $request_id = clean_input($_POST['request_id']);

        // Fetch enrollment request
        $req_sql = "SELECT * FROM enrollment_requests WHERE id = '$request_id' AND status = 'pending'";
        $req_result = $conn->query($req_sql);

        if ($req_result->num_rows > 0) {
            $request = $req_result->fetch_assoc();

            // Generate unique username
            $base_username = 'westprime@' . strtolower(str_replace(' ', '_', $request['firstname']));
            $username = $base_username;
            $counter = 2;

            while ($conn->query("SELECT id FROM users WHERE username = '$username'")->num_rows > 0) {
                $username = $base_username . $counter;
                $counter++;
            }

            // Generate random password (8 characters)
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Create user account
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'student')");
            $stmt->bind_param("sss", $username, $hashed_password, $request['email']);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;

                // Generate LRN (simple: year + random 6 digits)
                $lrn = date('Y') . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

                // Use grade_level from enrollment request for display
                $grade_level = $request['grade_level'];

                // Find or Create Section based on enrollment request details
                $req_year = $request['year_level'];
                $req_block = $request['block'];
                $req_course = $request['course_id'];
                $req_strand = $request['strand_id'];
                $section_id = null;

                if ($req_year && $req_block) {
                    // Check if section exists
                    $check_sec_sql = "SELECT id FROM sections WHERE year_level = '$req_year' AND block = '$req_block' ";
                    if ($req_course) {
                        $check_sec_sql .= "AND course_id = '$req_course'";
                    } elseif ($req_strand) {
                        $check_sec_sql .= "AND strand_id = '$req_strand'";
                    }

                    $sec_res = $conn->query($check_sec_sql);
                    if ($sec_res->num_rows > 0) {
                        $section_id = $sec_res->fetch_assoc()['id'];
                    } else {
                        // Create Section if not exists
                        // Generate Section Name, e.g. "BSIT 1-A" or "STEM 11-A"
                        $sec_name = "";
                        $gl_display = "";

                        if ($req_course) {
                            $c_info = $conn->query("SELECT course_code FROM courses WHERE id='$req_course'")->fetch_assoc();
                            $sec_name = $c_info['course_code'] . " " . $req_year . "-" . $req_block;
                            $gl_display = $req_year . getOrdinalSuffix($req_year) . " Year"; // Need function or simple logic
                        } else {
                            $s_info = $conn->query("SELECT strand_code FROM strands WHERE id='$req_strand'")->fetch_assoc();
                            $sec_name = $s_info['strand_code'] . " " . $req_year . "-" . $req_block;
                            $gl_display = "Grade " . $req_year;
                        }

                        // Insert new section
                        // Careful with helper function availability, let's duplicate simple ordinal logic if needed or just use text
                        // We need getOrdinalSuffix but it might not be included in functions.php. Let's assume standard formatting.

                        // Hardcode ordinal logic for safety since function might be in enroll.php only
                        $suffix = 'th';
                        if ($req_year == 1)
                            $suffix = 'st';
                        elseif ($req_year == 2)
                            $suffix = 'nd';
                        elseif ($req_year == 3)
                            $suffix = 'rd';

                        if (!$req_course)
                            $gl_display = "Grade " . $req_year; // SHS
                        else
                            $gl_display = $req_year . $suffix . " Year"; // College

                        $stmt_sec = $conn->prepare("INSERT INTO sections (section_name, grade_level, year_level, block, course_id, strand_id) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt_sec->bind_param("ssssii", $sec_name, $gl_display, $req_year, $req_block, $req_course, $req_strand);
                        if ($stmt_sec->execute()) {
                            $section_id = $conn->insert_id;
                        }
                    }
                }

                // Determine fee and scholar status
                $is_scholar = isset($_POST['is_scholar']) ? 1 : 0;
                $total_fee = $is_scholar ? 0.00 : 9000.00;

                // Create student record with SECTION ID
                $stmt2 = $conn->prepare("INSERT INTO students (user_id, firstname, lastname, lrn, address, contact_number, is_scholar, total_fee, section_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("isssssidi", $user_id, $request['firstname'], $request['lastname'], $lrn, $request['address'], $request['contact_number'], $is_scholar, $total_fee, $section_id);

                if ($stmt2->execute()) {
                    // Update enrollment request
                    $update_stmt = $conn->prepare("UPDATE enrollment_requests SET status = 'approved', processed_at = NOW() WHERE id = ?");
                    $update_stmt->bind_param("i", $request_id);
                    $update_stmt->execute();

                    // Send email with credentials
                    // Send email with credentials
                    // Check for PHPMailer in admin/vendor or root vendor
                    if (file_exists('vendor/PHPMailer/src/Exception.php')) {
                        require 'vendor/PHPMailer/src/Exception.php';
                        require 'vendor/PHPMailer/src/PHPMailer.php';
                        require 'vendor/PHPMailer/src/SMTP.php';
                    } elseif (file_exists('../vendor/PHPMailer/src/Exception.php')) {
                        require '../vendor/PHPMailer/src/Exception.php';
                        require '../vendor/PHPMailer/src/PHPMailer.php';
                        require '../vendor/PHPMailer/src/SMTP.php';
                    } else {
                        // Fallback/Error if not found
                        error_log("PHPMailer not found in admin/vendor or root vendor.");
                    }

                    $mail = new PHPMailer(true);

                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'sample@gmail.com'; // Replace with your Gmail
                        $mail->Password = 'password123';     // Replace with your Gmail App Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Disable SSL verification for local development (XAMPP usually needs this)
                        $mail->SMTPOptions = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            )
                        );

                        // Recipients
                        $mail->setFrom('youremail@gmail.com', 'Westprime Horizon Institute');
                        $mail->addAddress($request['email'], $request['firstname'] . ' ' . $request['lastname']);

                        // Format class for email (remove Block info)
                        $class_display = explode(' Block', $grade_level)[0];

                        // Embed logo
                        $logo_path = '../logo.jpg';
                        $mail->addEmbeddedImage($logo_path, 'school_logo');

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Enrollment Approved - Westprime Horizon Institute';
                        $mail->Body = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                                <div style='text-align: center; padding: 20px; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);'>
                                    <img src='cid:school_logo' alt='Westprime Horizon Logo' style='width: 100px; height: 100px; border-radius: 50%; border: 3px solid white;'>
                                    <h1 style='color: white; margin: 15px 0 5px 0;'>Westprime Horizon Institute</h1>
                                    <p style='color: white; margin: 0; opacity: 0.9;'>Enrollment Confirmation</p>
                                </div>
                                
                                <div style='padding: 30px; background: white;'>
                                    <h2 style='color: #3498db; margin-top: 0;'>🎉 Congratulations! Your Enrollment is Approved</h2>
                                    <p>Dear {$request['firstname']} {$request['lastname']},</p>
                                    <p>We are pleased to inform you that your enrollment request has been approved. Welcome to Westprime Horizon Institute!</p>
                                    
                                    <div style='background: #f0f8ff; padding: 20px; border-left: 4px solid #3498db; margin: 20px 0; border-radius: 4px;'>
                                        <h3 style='color: #2980b9; margin-top: 0;'>Your Login Credentials</h3>
                                        <p style='margin: 10px 0;'><strong>Username:</strong> <code style='background: #e3f2fd; padding: 4px 8px; border-radius: 3px; color: #1976d2;'>{$username}</code></p>
                                        <p style='margin: 10px 0;'><strong>Password:</strong> <code style='background: #e3f2fd; padding: 4px 8px; border-radius: 3px; color: #1976d2;'>{$password}</code></p>
                                        <p style='margin: 10px 0;'><strong>Class:</strong> <code style='background: #e3f2fd; padding: 4px 8px; border-radius: 3px; color: #1976d2;'>{$class_display}</code></p>
                                    </div>
                                    
                                    <p>You can now access the student portal using the link below:</p>
                                    <div style='text-align: center; margin: 25px 0;'>
                                        <a href='" . BASE_URL . "login.php' style='background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Login to Student Portal</a>
                                    </div>
                                    
                                    <div style='background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin: 20px 0;'>
                                        <p style='margin: 0; color: #856404;'><strong>⚠️ Important:</strong> Please change your password after your first login for security purposes.</p>
                                    </div>
                                    
                                    <p>If you have any questions or need assistance, please don't hesitate to contact the administration office.</p>
                                    
                                    <p style='margin-top: 30px;'>Best regards,<br><strong>Westprime Horizon Institute</strong></p>
                                </div>
                                
                                <div style='text-align: center; padding: 15px; background: #f5f5f5; color: #666; font-size: 12px;'>
                                    <p style='margin: 0;'>This is an automated message. Please do not reply to this email.</p>
                                </div>
                            </div>
                        ";

                        $mail->send();
                        $success = "Enrollment approved successfully! Credentials sent to student's email.";
                    } catch (Exception $e) {
                        $success = "Enrollment approved successfully! (Email could not be sent: {$mail->ErrorInfo})";
                    }

                    // Credentials display removed as per request
                } else {
                    $error = "Error creating student record.";
                }
            } else {
                $error = "Error creating user account.";
            }
        }
    }

    if (isset($_POST['reject'])) {
        $request_id = clean_input($_POST['request_id']);
        $conn->query("UPDATE enrollment_requests SET status = 'rejected', processed_at = NOW() WHERE id = '$request_id'");
        $success = "Enrollment request rejected.";
    }
}

// Fetch all enrollment requests
$requests_sql = "SELECT * FROM enrollment_requests ORDER BY 
                 CASE WHEN status = 'pending' THEN 1 ELSE 2 END, 
                 created_at DESC";
$requests = $conn->query($requests_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Requests - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
        }

        .page-title {
            color: #0056b3;
            font-size: 1.5rem;
            margin: 0 0 25px 0;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .data-table tr:hover td {
            background: #f9f9f9;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-approve {
            background: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-right: 4px;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .credentials-box {
            background: #e3f2fd;
            border: 2px solid #2196f3;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .credentials-box h3 {
            color: #1976d2;
            margin: 0 0 15px 0;
        }

        .cred-item {
            margin: 10px 0;
            font-size: 1rem;
        }

        .cred-label {
            font-weight: 600;
            color: #555;
        }

        .cred-value {
            color: #1976d2;
            font-weight: 700;
            font-family: monospace;
        }

        /* Mobile Responsive */
        @media screen and (max-width: 768px) {
            .page-title {
                font-size: 1.3rem;
            }

            .credentials-box {
                padding: 16px;
            }

            .cred-item {
                font-size: 0.9rem;
            }

            .data-table {
                min-width: 600px;
            }

            .data-table th,
            .data-table td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }

            .btn-approve,
            .btn-reject {
                padding: 5px 8px;
                font-size: 0.75rem;
            }

            .status-badge {
                padding: 3px 8px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="main-content">
            <h2 style="margin-bottom: 30px; color: var(--primary-color);">Enrollment Requests</h2>

            <?php if ($success): ?>
                <div class="alert success"
                    style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert error"
                    style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>



            <div class="card" style="padding: 0; overflow: hidden;">
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 700px;">
                        <thead>
                            <tr style="background: #0056b3; border-bottom: 2px solid #004494;">
                                <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Name</th>
                                <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Email</th>
                                <th style="padding: 15px; text-align: left; color: white; font-weight: 600;">Grade Level
                                    / Course</th>
                                <th style="padding: 15px; text-align: center; color: white; font-weight: 600;">Status
                                </th>
                                <th style="padding: 15px; text-align: center; color: white; font-weight: 600;">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests->num_rows > 0): ?>
                                <?php while ($req = $requests->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 15px; font-weight: 500;">
                                            <?php echo htmlspecialchars($req['lastname'] . ', ' . $req['firstname']); ?>
                                        </td>
                                        <td style="padding: 15px; color: #666;">
                                            <?php echo htmlspecialchars($req['email']); ?>
                                        </td>
                                        <td style="padding: 15px; color: #666;">
                                            <?php echo htmlspecialchars($req['grade_level']); ?>
                                        </td>
                                        <td style="padding: 15px; text-align: center;">
                                            <span class="status-badge status-<?php echo $req['status']; ?>">
                                                <?php echo strtoupper($req['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 15px; text-align: center;">
                                            <?php if ($req['status'] == 'pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                    <div style="margin-bottom: 5px;">
                                                        <label style="font-size: 0.85rem; cursor: pointer;">
                                                            <input type="checkbox" name="is_scholar" value="1"> Scholar (Free
                                                            Tuition)
                                                        </label>
                                                    </div>
                                                    <button type="submit" name="approve" class="btn-approve">Approve</button>
                                                    <button type="submit" name="reject" class="btn-reject">Reject</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: #999; font-size: 0.85rem;">
                                                    <?php echo date('M d, Y', strtotime($req['processed_at'])); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="padding: 30px; text-align: center; color: #888;">No enrollment
                                        requests found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>