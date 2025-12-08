<?php
// C:\xampp\htdocs\School_Portal\seed_courses.php
require_once 'config.php';

$courses = [
    // College Courses
    ['code' => 'BTVTED', 'name' => 'Bachelor of Technical-Vocational Teacher Education', 'type' => 'College'],
    ['code' => 'BAELS', 'name' => 'Bachelor of Arts in English Language Studies', 'type' => 'College'],
    ['code' => 'IT/ACT', 'name' => 'Information Technology / Associate in Computer Technology', 'type' => 'College'],
    
    // Senior High Strands
    ['code' => 'ABM', 'name' => 'Accountancy, Business and Management', 'type' => 'Senior High'],
    ['code' => 'STEM', 'name' => 'Science, Technology, Engineering and Mathematics', 'type' => 'Senior High'],
    ['code' => 'HUMSS', 'name' => 'Humanities and Social Sciences', 'type' => 'Senior High'],
    ['code' => 'SMAW', 'name' => 'Shielded Metal Arc Welding (NCII)', 'type' => 'Senior High'],
    ['code' => 'CSS', 'name' => 'Computer Systems Servicing (NCII)', 'type' => 'Senior High'],
    ['code' => 'EIM', 'name' => 'Electrical Installation and Maintenance (NCII)', 'type' => 'Senior High'],
    ['code' => 'AS', 'name' => 'Automotive Servicing (NCII)', 'type' => 'Senior High'],
];

echo "<h3>Seeding Courses...</h3>";

foreach ($courses as $course) {
    // Check if course already exists
    $check = $conn->query("SELECT id FROM courses WHERE course_code = '{$course['code']}'");
    
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO courses (course_code, course_name, description) 
                VALUES ('{$course['code']}', '{$course['name']}', '{$course['type']}')";
        
        if ($conn->query($sql) === TRUE) {
            echo "✓ Added: {$course['code']} - {$course['name']}<br>";
        } else {
            echo "✗ Error adding {$course['code']}: " . $conn->error . "<br>";
        }
    } else {
        echo "○ Already exists: {$course['code']}<br>";
    }
}

echo "<br><strong>Seeding complete!</strong>";
?>
