<?php
// C:\xampp\htdocs\School_Portal\enroll.php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = clean_input($_POST['firstname']);
    $lastname = clean_input($_POST['lastname']);
    $email = clean_input($_POST['email']);
    $contact = clean_input($_POST['contact_number']);
    $address = clean_input($_POST['address']);
    $course_strand = clean_input($_POST['course_strand']);
    $year_level = clean_input($_POST['year_level']);
    $section = clean_input($_POST['section']);
    
    if (empty($firstname) || empty($lastname) || empty($email) || empty($course_strand) || empty($year_level) || empty($section)) {
        $error = "Please fill in all required fields.";
    } else {
        // Combine into class_year format
        $class_year = $course_strand . ' ' . $year_level . '-' . $section;
        
        $stmt = $conn->prepare("INSERT INTO enrollment_requests (firstname, lastname, email, contact_number, address, course_strand, year_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $firstname, $lastname, $email, $contact, $address, $course_strand, $class_year);
        
        if ($stmt->execute()) {
            $success = "Enrollment request submitted successfully! Please wait for admin approval.";
        } else {
            $error = "Error submitting request: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment - Westprime Horizon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .enrollment-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            border-radius: 50%;
            border: 3px solid #3498db;
        }
        .header h1 {
            color: #3498db;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="enrollment-container">
    <div class="header">
        <img src="<?php echo BASE_URL; ?>logo.jpg" alt="Westprime Horizon Logo">
        <h1>🎓 Student Enrollment</h1>
        <p>Westprime Horizon Institute</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="firstname" required>
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="lastname" required>
            </div>
        </div>

        <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" placeholder="09XX-XXX-XXXX">
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label>Course/Strand *</label>
            <select name="course_strand" required>
                <option value="">Select Course/Strand</option>
                <optgroup label="College Courses">
                    <option value="BSIT">BSIT - Bachelor of Science in Information Technology</option>
                    <option value="BAELS">BAELS - Bachelor of Arts in English Language Studies</option>
                    <option value="BTVTED">BTVTED - Bachelor of Technical-Vocational Teacher Education</option>
                </optgroup>
                <optgroup label="Senior High Strands">
                    <option value="ABM">ABM - Accountancy, Business and Management</option>
                    <option value="STEM">STEM - Science, Technology, Engineering and Mathematics</option>
                    <option value="HUMSS">HUMSS - Humanities and Social Sciences</option>
                    <option value="SMAW">SMAW (NCII) - Shielded Metal Arc Welding</option>
                    <option value="CSS">CSS (NCII) - Computer Systems Servicing</option>
                    <option value="EIM">EIM (NCII) - Electrical Installation and Maintenance</option>
                    <option value="AS">AS (NCII) - Automotive Servicing</option>
                </optgroup>
            </select>
        </div>

        <div class="form-group">
            <label>Year Level *</label>
            <select name="year_level" required>
                <option value="">Select Year Level</option>
                <optgroup label="College">
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </optgroup>
                <optgroup label="Senior High">
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </optgroup>
            </select>
        </div>

        <div class="form-group">
            <label>Section *</label>
            <select name="section" required>
                <option value="">Select Section</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
                <option value="E">E</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">Submit Enrollment</button>
    </form>

    <div class="back-link">
        <a href="index.php">← Back to Login</a>
    </div>
</div>

</body>
</html>
