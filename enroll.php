<?php
// C:\xampp\htdocs\School_Portal\enroll.php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

$success = '';
$error = '';

// Fetch College Courses
$courses = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");

// Fetch Senior High Strands
$strands = $conn->query("SELECT * FROM strands ORDER BY description, strand_code ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = clean_input($_POST['firstname']);
    $lastname = clean_input($_POST['lastname']);
    $email = clean_input($_POST['email']);
    $contact = clean_input($_POST['contact_number']);
    $address = clean_input($_POST['address']);
    $program = clean_input($_POST['program']); // Format: "college|ID" or "shs|ID"
    $year_level = clean_input($_POST['year_level']);
    $block = clean_input($_POST['block']);
    
    if (empty($firstname) || empty($lastname) || empty($email) || empty($program) || empty($year_level) || empty($block)) {
        $error = "Please fill in all required fields.";
    } else {
        list($type, $id) = explode('|', $program);
        
        $course_id = null;
        $strand_id = null;
        $code = '';
        
        if ($type == 'college') {
            $course_id = $id;
            $info = $conn->query("SELECT course_code FROM courses WHERE id = '$id'")->fetch_assoc();
            $code = $info['course_code'];
            $grade_level_str = $year_level . getOrdinalSuffix($year_level) . " Year - " . $code . " Block " . $block;
        } else {
            $strand_id = $id;
            $info = $conn->query("SELECT strand_code FROM strands WHERE id = '$id'")->fetch_assoc();
            $code = $info['strand_code'];
            $grade_level_str = "Grade " . $year_level . " - " . $code . " Block " . $block;
        }
        
        $stmt = $conn->prepare("INSERT INTO enrollment_requests (firstname, lastname, email, contact_number, address, grade_level, course_id, strand_id, year_level, block) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssiiss", $firstname, $lastname, $email, $contact, $address, $grade_level_str, $course_id, $strand_id, $year_level, $block);
        
        if ($stmt->execute()) {
            $success = "Your enrollment request has been submitted successfully! Please wait for the admin approval. Check your email for updates regarding your enrollment status.";
        } else {
            $error = "Error submitting request: " . $conn->error;
        }
        $stmt->close();
    }
}

function getOrdinalSuffix($num) {
    if ($num == 1) return 'st';
    if ($num == 2) return 'nd';
    if ($num == 3) return 'rd';
    return 'th';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment - Westprime Horizon</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Enrollment Submitted!',
                    text: '<?php echo $success; ?>',
                    icon: 'success',
                    confirmButtonText: 'Okay',
                    confirmButtonColor: '#3498db'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'index.php';
                    }
                });
            });
        </script>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="firstname" required placeholder="Enter first name">
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="lastname" required placeholder="Enter lastname">
            </div>
        </div>

        <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" required placeholder="Enter email">
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" placeholder="09XX-XXX-XXXX" maxlength="11" pattern="[0-9]{11}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)" title="Please enter exactly 11 digits" required>
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label>Program (Course/Strand) *</label>
            <select name="program" id="programSelect" required onchange="updateYearLevels()">
                <option value="">Select Program</option>
                <?php if ($courses->num_rows > 0): ?>
                    <optgroup label="College Courses">
                        <?php while($c = $courses->fetch_assoc()): ?>
                            <option value="college|<?php echo $c['id']; ?>">
                                <?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </optgroup>
                <?php endif; ?>
                
                <?php 
                $academic_strands = [];
                $tvl_strands = [];
                
                if ($strands->num_rows > 0) {
                    while($s = $strands->fetch_assoc()) {
                        if ($s['description'] == 'TVL') {
                            $tvl_strands[] = $s;
                        } else {
                            $academic_strands[] = $s;
                        }
                    }
                }
                ?>
                
                <?php if (count($academic_strands) > 0): ?>
                    <optgroup label="Senior High (Academic Track)">
                        <?php foreach($academic_strands as $s): ?>
                            <option value="shs|<?php echo $s['id']; ?>">
                                <?php echo htmlspecialchars($s['strand_code'] . ' - ' . $s['strand_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>

                <?php if (count($tvl_strands) > 0): ?>
                    <optgroup label="Senior High (TVL Track)">
                        <?php foreach($tvl_strands as $s): ?>
                            <option value="shs|<?php echo $s['id']; ?>">
                                <?php echo htmlspecialchars($s['strand_code'] . ' - ' . $s['strand_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Year Level *</label>
            <select name="year_level" id="yearSelect" required>
                <option value="">Select Program First</option>
            </select>
        </div>

        <script>
        function updateYearLevels() {
            const program = document.getElementById('programSelect').value;
            const yearSelect = document.getElementById('yearSelect');
            yearSelect.innerHTML = '';
            
            if (!program) {
                const opt = document.createElement('option');
                opt.value = "";
                opt.text = "Select Program First";
                yearSelect.add(opt);
                return;
            }
            
            const type = program.split('|')[0];
            
            if (type === 'college') {
                const years = [
                    {val: '1', text: '1st Year'},
                    {val: '2', text: '2nd Year'},
                    {val: '3', text: '3rd Year'},
                    {val: '4', text: '4th Year'}
                ];
                years.forEach(y => {
                    const opt = document.createElement('option');
                    opt.value = y.val;
                    opt.text = y.text;
                    yearSelect.add(opt);
                });
            } else {
                const grades = [
                    {val: '11', text: 'Grade 11'},
                    {val: '12', text: 'Grade 12'}
                ];
                grades.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g.val;
                    opt.text = g.text;
                    yearSelect.add(opt);
                });
            }
        }
        </script>

        <div class="form-group">
            <label>Block *</label>
            <select name="block" required>
                <option value="">Select Block</option>
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
