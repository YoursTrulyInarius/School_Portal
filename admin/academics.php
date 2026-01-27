<?php
// C:\xampp\htdocs\School_Portal\admin\academics.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

check_admin();

$success = '';
$error = '';

// Predefined course mappings (College)
$college_courses = [
    'ACT' => 'Associate of Computer Technology',
    'BSIT' => 'Bachelor of Science in Information Technology',
    'BSCS' => 'Bachelor of Science in Computer Science',
    'BSIS' => 'Bachelor of Science in Information Systems',
    'BSBA' => 'Bachelor of Science in Business Administration',
    'BSED' => 'Bachelor of Secondary Education',
    'BEED' => 'Bachelor of Elementary Education',
    'BAELS' => 'Bachelor of Arts in English Language Studies',
    'BTVTED' => 'Bachelor of Technical-Vocational Teacher Education',
    'BSA' => 'Bachelor of Science in Accountancy',
    'BSOA' => 'Bachelor of Science in Office Administration',
    'BSCRIM' => 'Bachelor of Science in Criminology',
    'BSHRM' => 'Bachelor of Science in Hotel and Restaurant Management',
    'BSHM' => 'Bachelor of Science in Hospitality Management',
    'BSTM' => 'Bachelor of Science in Tourism Management',
    'BSN' => 'Bachelor of Science in Nursing',
    'BSPSYCH' => 'Bachelor of Science in Psychology'
];

// Predefined strand mappings (Senior High)
$shs_strands = [
    'ABM' => 'Accountancy, Business and Management',
    'STEM' => 'Science, Technology, Engineering and Mathematics',
    'HUMSS' => 'Humanities and Social Sciences',
    'GAS' => 'General Academic Strand',
    'TVL-ICT' => 'Technical-Vocational-Livelihood - ICT',
    'TVL-HE' => 'Technical-Vocational-Livelihood - Home Economics',
    'TVL-IA' => 'Technical-Vocational-Livelihood - Industrial Arts',
    'ADT' => 'Arts and Design Track',
    'SMAW' => 'Shielded Metal Arc Welding (NCII)',
    'CSS' => 'Computer Systems Servicing (NCII)',
    'EIM' => 'Electrical Installation and Maintenance (NCII)',
    'AS' => 'Automotive Servicing (NCII)',
    'COOKERY' => 'Cookery (NCII)',
    'BREAD' => 'Bread and Pastry Production (NCII)'
];

// Handle Course/Strand Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_course'])) {
        $course_code = strtoupper(clean_input($_POST['course_code']));
        $course_name = clean_input($_POST['course_name']);

        // Check if already exists
        $check = $conn->query("SELECT id FROM courses WHERE course_code = '$course_code'");
        if ($check->num_rows > 0) {
            $error = "Course code already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, description) VALUES (?, ?, 'College')");
            $stmt->bind_param("ss", $course_name, $course_code);

            if ($stmt->execute()) {
                $success = "College Course added successfully!";
            } else {
                $error = "Error saving course.";
            }
        }
    }

    if (isset($_POST['save_strand'])) {
        $strand_code = strtoupper(clean_input($_POST['strand_code']));
        $strand_name = clean_input($_POST['strand_name']);
        $strand_type = clean_input($_POST['strand_type']); // 'Academic' or 'TVL'

        // Check if already exists
        $check = $conn->query("SELECT id FROM strands WHERE strand_code = '$strand_code'");
        if ($check->num_rows > 0) {
            $error = "Strand code already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO strands (strand_name, strand_code, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $strand_name, $strand_code, $strand_type);

            if ($stmt->execute()) {
                $success = "Strand added successfully!";
            } else {
                $error = "Error saving strand.";
            }
        }
    }
}

// Handle Delete Actions
if (isset($_GET['delete_course'])) {
    $course_id = clean_input($_GET['delete_course']);
    $conn->query("DELETE FROM courses WHERE id = '$course_id'");
    $success = "Course deleted successfully!";
}
if (isset($_GET['delete_strand'])) {
    $strand_id = clean_input($_GET['delete_strand']);
    $conn->query("DELETE FROM strands WHERE id = '$strand_id'");
    $success = "Strand deleted successfully!";
}

// Handle Section Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_section'])) {
    // Validating required fields presence
    if (!isset($_POST['category']) || !isset($_POST['item_id'])) {
        $error = "Please select a Category and Program.";
    } else {
        $category = clean_input($_POST['category']); // 'college' or 'shs'
        $item_id = clean_input($_POST['item_id']); // course_id or strand_id
        $year_level = clean_input($_POST['year_level']);
        $block = clean_input($_POST['block']);

        $course_id = null;
        $strand_id = null;
        $code_prefix = '';

        if ($category == 'college') {
            $course_id = $item_id;
            $info = $conn->query("SELECT course_code FROM courses WHERE id = '$course_id'")->fetch_assoc();
            if ($info) {
                $code_prefix = $info['course_code'];
                $grade_level = $year_level . getOrdinalSuffix($year_level) . " Year";
            }
        } else {
            $strand_id = $item_id;
            $info = $conn->query("SELECT strand_code FROM strands WHERE id = '$strand_id'")->fetch_assoc();
            if ($info) {
                $code_prefix = $info['strand_code'];
                $grade_level = "Grade " . $year_level;
            }
        }

        if ($code_prefix) {
            // Generate Section Name: "{CODE} {YEAR}-{BLOCK}"
            $section_name = $code_prefix . ' ' . $year_level . '-' . $block;

            // Check if section already exists
            $check = $conn->query("SELECT id FROM sections WHERE section_name = '$section_name'");
            if ($check->num_rows > 0) {
                $error = "Section '$section_name' already exists!";
            } else {
                // Using dynamic bind logic is messy, simpler to just run two different queries
                // or ensure NULL is handled. 
                // Issue: bind_param('i', $val) where $val is NULL sends 0 in some PHP versions/configs.
                // Safest fix: execute different queries based on type.

                if ($course_id) {
                    $stmt = $conn->prepare("INSERT INTO sections (section_name, course_id, year_level, block, grade_level) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sisss", $section_name, $course_id, $year_level, $block, $grade_level);
                } else {
                    $stmt = $conn->prepare("INSERT INTO sections (section_name, strand_id, year_level, block, grade_level) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sisss", $section_name, $strand_id, $year_level, $block, $grade_level);
                }

                if ($stmt->execute()) {
                    $success = "Section '$section_name' created successfully!";
                } else {
                    $error = "Error creating section: " . $conn->error;
                }
            }
        } else {
            $error = "Invalid Program Selection.";
        }
    }
}

// Handle Section Delete
if (isset($_GET['delete_section'])) {
    $section_id = clean_input($_GET['delete_section']);
    $conn->query("DELETE FROM sections WHERE id = '$section_id'");
    $success = "Section deleted successfully!";
}

// Fetch Courses
$courses = $conn->query("SELECT * FROM courses ORDER BY course_code ASC");

// Fetch Strands
$strands = $conn->query("SELECT * FROM strands ORDER BY description, strand_code ASC");

// Fetch Sections with Info
$sql_sections = "SELECT s.*, 
                 COALESCE(c.course_code, st.strand_code) as code,
                 COALESCE(c.course_name, st.strand_name) as name,
                 CASE WHEN c.id IS NOT NULL THEN 'College' ELSE 'Senior High' END as type
                 FROM sections s 
                 LEFT JOIN courses c ON s.course_id = c.id
                 LEFT JOIN strands st ON s.strand_id = st.id
                 ORDER BY type, s.year_level, s.block ASC";
$result_sections = $conn->query($sql_sections);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academics Management - Westprime Horizon</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 30px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            padding: 12px;
            text-align: left;
            background: #0056b3;
            color: white;
            font-weight: 600;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .data-table tr:hover td {
            background: #f9f9f9;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #0056b3;
            color: white;
        }

        .btn-success {
            background: #2ecc71;
            color: white;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        #courseNamePreview {
            color: #2ecc71;
            font-weight: 600;
            margin-top: 5px;
            font-size: 0.9rem;
        }

        .section-type-toggle {
            display: none;
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="main-content">
            <h2 style="color: var(--primary-color); margin-bottom: 25px;">📚 Academics Management</h2>

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

            <!-- ADD COURSE FORM -->
            <div class="card">
                <h3 style="margin: 0 0 20px 0;">➕ Add College Course</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Course Code *</label>
                            <input type="text" name="course_code" id="addCourseCode" class="form-control"
                                placeholder="e.g., BSIT" required style="text-transform: uppercase;">
                        </div>
                        <div class="form-group">
                            <label>Course Name *</label>
                            <input type="text" name="course_name" id="addCourseName" class="form-control"
                                placeholder="Bachelor of Science in..." required>
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <button type="submit" name="save_course" class="btn btn-primary" style="height: 42px;">Add
                                Course</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ADD STRAND FORM -->
            <div class="card">
                <h3 style="margin: 0 0 20px 0;">➕ Add Senior High / TVL Strand</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Track Type *</label>
                            <select name="strand_type" class="form-control" required>
                                <option value="Academic">Academic Track</option>
                                <option value="TVL">TVL Track</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Strand Code *</label>
                            <input type="text" name="strand_code" id="addStrandCode" class="form-control"
                                placeholder="e.g., STEM, CSS" required style="text-transform: uppercase;">
                        </div>
                        <div class="form-group">
                            <label>Strand Name *</label>
                            <input type="text" name="strand_name" id="addStrandName" class="form-control"
                                placeholder="Science, Technology..." required>
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <button type="submit" name="save_strand" class="btn btn-success" style="height: 42px;">Add
                                Strand</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- LISTS -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- COLLEGE COURSES LIST -->
                <div class="card">
                    <h3 style="margin: 0 0 20px 0; color: #0056b3;">🎓 College Courses</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($courses->num_rows > 0): ?>
                                <?php while ($c = $courses->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $c['course_code']; ?></strong></td>
                                        <td><?php echo $c['course_name']; ?></td>
                                        <td><a href="?delete_course=<?php echo $c['id']; ?>" class="btn btn-danger"
                                                style="padding: 4px 8px; font-size: 0.75rem;"
                                                onclick="return confirm('Delete?');">Delete</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #888;">No courses found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- SHS/TVL STRANDS LIST -->
                <div class="card">
                    <h3 style="margin: 0 0 20px 0; color: #27ae60;">🏫 SHS & TVL Strands</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($strands->num_rows > 0): ?>
                                <?php while ($s = $strands->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $s['strand_code']; ?></strong></td>
                                        <td><?php echo $s['strand_name']; ?></td>
                                        <td>
                                            <?php if ($s['description'] == 'TVL'): ?>
                                                <span
                                                    style="background: #e67e22; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">TVL</span>
                                            <?php else: ?>
                                                <span
                                                    style="background: #27ae60; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">Academic</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><a href="?delete_strand=<?php echo $s['id']; ?>" class="btn btn-danger"
                                                style="padding: 4px 8px; font-size: 0.75rem;"
                                                onclick="return confirm('Delete?');">Delete</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #888;">No strands found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ADD SECTION FORM -->
            <div class="card">
                <h3 style="margin: 0 0 20px 0;">➕ Add Section</h3>
                <form method="POST" id="sectionForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category" id="categorySelect" class="form-control" required
                                onchange="toggleItems()">
                                <option value="">Select Category</option>
                                <option value="college">College Course</option>
                                <option value="shs">Senior High Strand</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Program *</label>
                            <select name="item_id" id="itemSelect" class="form-control" required disabled>
                                <option value="">Select Program</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Year Level *</label>
                            <select name="year_level" id="yearSelect" class="form-control" required disabled>
                                <option value="">Select Year</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Block *</label>
                            <select name="block" id="blockSelect" class="form-control" required>
                                <option value="">Select Block</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                            </select>
                        </div>
                    </div>
                    <div id="sectionPreview"
                        style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: none; text-align: center; border: 1px dashed #2196f3;">
                        <span style="color: #555; font-size: 0.9rem;">New Section Name:</span><br>
                        <strong id="previewText" style="color: #0056b3; font-size: 1.2rem;"></strong>
                    </div>
                    <button type="submit" name="save_section" class="btn btn-success">Save Section</button>
                </form>
            </div>

            <!-- SECTIONS LIST -->
            <div class="card">
                <h3 style="margin: 0 0 20px 0;">📑 Sections</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course/Strand</th>
                            <th>Type</th>
                            <th>Year</th>
                            <th>Block</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_sections->num_rows > 0): ?>
                            <?php while ($section = $result_sections->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-weight: 600; color: #0056b3;">
                                        <?php echo htmlspecialchars($section['code']); ?> -
                                        <?php echo htmlspecialchars($section['name']); ?></td>
                                    <td><?php echo htmlspecialchars($section['grade_level']); ?></td>
                                    <td>
                                        <span
                                            style="padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; background: <?php echo $section['type'] == 'College' ? '#3498db' : '#2ecc71'; ?>; color: white;">
                                            <?php echo htmlspecialchars($section['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            style="padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; background: #f39c12; color: white;">
                                            <?php echo htmlspecialchars($section['block']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?delete_section=<?php echo $section['id']; ?>" class="btn btn-danger"
                                            style="padding: 5px 10px; font-size: 0.85rem;"
                                            onclick="return confirm('Delete this section?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #888; padding: 30px;">No sections found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        // Data for dropdowns
        const courses = [
            <?php
            $courses->data_seek(0);
            while ($c = $courses->fetch_assoc()) {
                echo "{id: '{$c['id']}', name: '{$c['course_code']} - {$c['course_name']}'},";
            }
            ?>
        ];

        const strands = [
            <?php
            $strands->data_seek(0);
            while ($s = $strands->fetch_assoc()) {
                echo "{id: '{$s['id']}', name: '{$s['strand_code']} - {$s['strand_name']}'},";
            }
            ?>
        ];

        function toggleItems() {
            const cat = document.getElementById('categorySelect').value;
            const itemSelect = document.getElementById('itemSelect');
            const yearSelect = document.getElementById('yearSelect');

            itemSelect.innerHTML = '<option value="">Select Program</option>';
            yearSelect.innerHTML = '<option value="">Select Year</option>';

            itemSelect.disabled = (cat === '');
            yearSelect.disabled = (cat === '');

            if (cat === 'college') {
                courses.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    itemSelect.appendChild(opt);
                });

                // Add College Years
                for (let i = 1; i <= 4; i++) {
                    const opt = document.createElement('option');
                    opt.value = i;
                    opt.textContent = i + (i === 1 ? 'st' : (i === 2 ? 'nd' : (i === 3 ? 'rd' : 'th'))) + ' Year';
                    yearSelect.appendChild(opt);
                }
            } else if (cat === 'shs') {
                strands.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    itemSelect.appendChild(opt);
                });

                // Add SHS Grades
                const g11 = document.createElement('option'); g11.value = '11'; g11.textContent = 'Grade 11';
                const g12 = document.createElement('option'); g12.value = '12'; g12.textContent = 'Grade 12';
                yearSelect.appendChild(g11);
                yearSelect.appendChild(g12);
            }
        }

        // Mappings
        const courseMappings = <?php echo json_encode($college_courses); ?>;
        const strandMappings = <?php echo json_encode($shs_strands); ?>;

        // Auto-fill Listeners
        const addCourseCode = document.getElementById('addCourseCode');
        if (addCourseCode) {
            addCourseCode.addEventListener('input', function () {
                const code = this.value.toUpperCase();
                if (courseMappings[code]) {
                    document.getElementById('addCourseName').value = courseMappings[code];
                }
            });
        }

        const addStrandCode = document.getElementById('addStrandCode');
        if (addStrandCode) {
            addStrandCode.addEventListener('input', function () {
                const code = this.value.toUpperCase();
                if (strandMappings[code]) {
                    document.getElementById('addStrandName').value = strandMappings[code];
                }
            });
        }

        // Section Preview
        function updatePreview() {
            const cat = document.getElementById('categorySelect').value;
            const itemSelect = document.getElementById('itemSelect');
            const yearSelect = document.getElementById('yearSelect');
            const blockSelect = document.getElementById('blockSelect');

            const previewDiv = document.getElementById('sectionPreview');
            const previewText = document.getElementById('previewText');

            if (previewDiv && cat && itemSelect.value && yearSelect.value && blockSelect.value) {
                let code = '';
                const selectedText = itemSelect.options[itemSelect.selectedIndex].text;
                // Extract code from "CODE - Name" format
                if (selectedText.includes(' - ')) {
                    code = selectedText.split(' - ')[0];
                } else {
                    code = selectedText;
                }

                const sectionName = `${code} ${yearSelect.value}-${blockSelect.value}`;
                previewText.textContent = sectionName;
                previewDiv.style.display = 'block';
            } else if (previewDiv) {
                previewDiv.style.display = 'none';
                previewText.textContent = '';
            }
        }

        // Attach listeners to section form inputs
        document.getElementById('categorySelect').addEventListener('change', updatePreview);
        document.getElementById('itemSelect').addEventListener('change', updatePreview);
        document.getElementById('yearSelect').addEventListener('change', updatePreview);
        document.getElementById('blockSelect').addEventListener('change', updatePreview);
    </script>

</body>

</html>