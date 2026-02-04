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
        :root {
            --royal-blue: #002366;
            --royal-blue-light: #003399;
            --royal-blue-dark: #001a4d;
            --pure-white: #ffffff;
            --soft-white: #f8f9fa;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-premium: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--royal-blue-dark), var(--royal-blue));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            overflow-x: hidden;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .enrollment-container {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--shadow-premium);
            max-width: 700px;
            width: 100%;
            padding: 50px 40px;
            animation: fadeInUp 0.8s ease-out;
            transition: transform 0.3s ease;
        }

        .enrollment-container:hover {
            transform: translateY(-5px);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header img {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            border-radius: 50%;
            border: 4px solid var(--royal-blue);
            padding: 5px;
            background: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            animation: float 4s ease-in-out infinite;
        }

        .header h1 {
            color: var(--royal-blue);
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
            font-weight: 300;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .form-group:focus-within label {
            color: var(--royal-blue);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #fff;
        }

        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            border-color: var(--royal-blue-light);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--royal-blue);
            box-shadow: 0 0 0 4px rgba(0, 35, 102, 0.1);
            transform: scale(1.01);
        }

        .btn-submit {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--royal-blue), var(--royal-blue-light));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 35, 102, 0.2);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 35, 102, 0.3);
            background: linear-gradient(135deg, var(--royal-blue-light), var(--royal-blue));
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
        }

        .back-link a {
            color: var(--royal-blue);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            position: relative;
            transition: all 0.3s ease;
        }

        .back-link a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--royal-blue);
            transition: width 0.3s ease;
        }

        .back-link a:hover::after {
            width: 100%;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 2rem;
            }
            .enrollment-container {
                padding: 30px 20px;
            }
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
