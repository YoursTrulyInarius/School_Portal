<?php
// seed_dummy_students.php
// Run this ONCE via browser: http://localhost/School_Portal/seed_dummy_students.php
// Then delete or restrict this file.

require_once 'config.php';

// Safety: only run if not already seeded (check count)
$check = $conn->query("SELECT COUNT(*) as cnt FROM students");
$row = $check->fetch_assoc();
if ($row['cnt'] >= 150) {
    die("<p>Seed already ran — found {$row['cnt']} students. Delete this file.</p>");
}

$password_plain = 'Student@123';
$password_hash  = password_hash($password_plain, PASSWORD_DEFAULT);

// 150 unique real-like names (Filipino + common international mix)
$names = [
    ['Amara',    'Reyes'],
    ['Bianca',   'Santos'],
    ['Carlos',   'Dela Cruz'],
    ['Daniela',  'Villanueva'],
    ['Eduardo',  'Mendoza'],
    ['Fatima',   'Garcia'],
    ['Gabriel',  'Torres'],
    ['Hannah',   'Aquino'],
    ['Ignacio',  'Bautista'],
    ['Jamaica',  'Castillo'],
    ['Kevin',    'Ramos'],
    ['Liana',    'Flores'],
    ['Marco',    'Hernandez'],
    ['Naomi',    'Gutierrez'],
    ['Oliver',   'Morales'],
    ['Patricia', 'Jimenez'],
    ['Quentin',  'Valdez'],
    ['Rhea',     'Cruz'],
    ['Sebastian','Navarro'],
    ['Tricia',   'Padilla'],
    ['Ulrich',   'Ramirez'],
    ['Vanessa',  'Gonzalez'],
    ['Walton',   'Lim'],
    ['Ximena',   'Abad'],
    ['Yolanda',  'Sison'],
    ['Zander',   'Dizon'],
    ['Abigail',  'Magno'],
    ['Bernard',  'Ocampo'],
    ['Carmela',  'Salazar'],
    ['Darius',   'Espinosa'],
    ['Elaine',   'Ibarra'],
    ['Francis',  'Mercado'],
    ['Gloria',   'Pascual'],
    ['Hector',   'Aguilar'],
    ['Irene',    'Dela Torre'],
    ['Julian',   'Bondoc'],
    ['Katrina',  'Medina'],
    ['Lorenzo',  'Sta. Maria'],
    ['Melissa',  'Buenaventura'],
    ['Nathan',   'Soriano'],
    ['Odette',   'Perez'],
    ['Paolo',    'Domingo'],
    ['Queenie',  'Macaraeg'],
    ['Rafael',   'Varga'],
    ['Shaira',   'Tolentino'],
    ['Tristan',  'Mateo'],
    ['Ursula',   'Corpuz'],
    ['Vivienne', 'Fajardo'],
    ['Warren',   'Alcantara'],
    ['Xenia',    'Andrada'],
    ['Yasmin',   'Lagman'],
    ['Zoe',      'Cabrera'],
    ['Aliyah',   'Tomas'],
    ['Bryce',    'Candelaria'],
    ['Corazon',  'Ilagan'],
    ['Derek',    'Salas'],
    ['Estrella', 'Dela Rosa'],
    ['Ferdinand','Montes'],
    ['Giselle',  'Sarmiento'],
    ['Harold',   'Banaag'],
    ['Ingrid',   'Cuenca'],
    ['Jerome',   'Apostol'],
    ['Krista',   'Baluyot'],
    ['Leandro',  'Evangelista'],
    ['Mikaela',  'Ferrer'],
    ['Nelson',   'Bacani'],
    ['Orenda',   'Pangilinan'],
    ['Philip',   'Serrano'],
    ['Quiana',   'Dimapilis'],
    ['Rodel',    'Enriquez'],
    ['Sophia',   'Chua'],
    ['Tobias',   'Magbanua'],
    ['Uma',      'Sicat'],
    ['Victor',   'Mallari'],
    ['Wilda',    'Punzalan'],
    ['Xerxes',   'Ong'],
    ['Yves',     'Resurreccion'],
    ['Zoraida',  'Dela Vega'],
    ['Angelica', 'Pineda'],
    ['Benedict', 'Dacumos'],
    ['Celestine','Caballero'],
    ['Daphne',   'Villarin'],
    ['Emilio',   'Tanaka'],
    ['Florencia','Hashimoto'],
    ['Gianni',   'Kobayashi'],
    ['Helena',   'Yamamoto'],
    ['Ivan',     'Suzuki'],
    ['Jade',     'Nakamura'],
    ['Kieran',   'Watanabe'],
    ['Luna',     'Sato'],
    ['Miguel',   'Ito'],
    ['Nadia',    'Kato'],
    ['Oscar',    'Hayashi'],
    ['Priya',    'Tanaka'],
    ['Quinn',    'Inoue'],
    ['Rosario',  'Shimizu'],
    ['Salvador', 'Kim'],
    ['Theresa',  'Park'],
    ['Ulysses',  'Choi'],
    ['Valentina','Lee'],
    ['Wesley',   'Han'],
    ['Xochitl',  'Jung'],
    ['Yuna',     'Kwon'],
    ['Zelda',    'Shin'],
    ['Aaron',    'Estrada'],
    ['Beatrice', 'Vergara'],
    ['Cassandra','Delos Reyes'],
    ['Dominic',  'Orozco'],
    ['Emilia',   'Villanueva'],
    ['Felix',    'Guerrero'],
    ['Genevieve','Sandoval'],
    ['Hilario',  'Contreras'],
    ['Isadora',  'Herrera'],
    ['Jasper',   'Avila'],
    ['Katerina', 'Medrano'],
    ['Lester',   'Vidal'],
    ['Maricel',  'Concepcion'],
    ['Nicolo',   'Briones'],
    ['Ophelia',  'Sumague'],
    ['Preston',  'Barroga'],
    ['Rowena',   'Celestino'],
    ['Sandro',   'Arevalo'],
    ['Tiffany',  'Bueno'],
    ['Umberto',  'Abas'],
    ['Valeria',  'Arce'],
    ['Wendell',  'Bello'],
    ['Xiomara',  'Cabato'],
    ['Yosef',    'Camacho'],
    ['Zinnia',   'Capili'],
    ['Alexei',   'Carbonell'],
    ['Brianna',  'Carpio'],
    ['Cedric',   'Casas'],
    ['Dorothea', 'Castano'],
    ['Ephraim',  'Catalan'],
    ['Felisa',   'Sian'],
    ['Gregorio', 'Cuarto'],
    ['Harriet',  'Cuevas'],
    ['Icarus',   'Dacaymat'],
    ['Jennica',  'Dacut'],
    ['Kristopher','Dagohoy'],
    ['Lavinia',  'Dalida'],
    ['Macario',  'Damasco'],
    ['Nerissa',  'David'],
    ['Orestes',  'Dayrit'],
    ['Perla',    'De Leon'],
    ['Ramiro',   'Del Rosario'],
    ['Severino', 'Dela Fuente'],
    ['Tessie',   'Delgado'],
    ['Uriel',    'Delos Santos'],
    ['Verna',    'Desales'],
    ['Wilhelm',  'Dimaano'],
    ['Ysabel',   'Dimaculangan'],
    ['Zosimo',   'Diones'],
];

$inserted = 0;
$errors   = [];

foreach ($names as $index => $name) {
    [$firstname, $lastname] = $name;

    // username: firstname + index+1 (e.g., amara1)
    $num      = $index + 1;
    $username = strtolower(preg_replace('/\s+/', '', $firstname)) . $num;

    // email: firstname + num @gmail.com
    $email    = strtolower(preg_replace('/\s+/', '', $firstname)) . $num . '@gmail.com';

    // LRN: 12-digit starting from 202400001001
    $lrn      = '2024' . str_pad($num, 8, '0', STR_PAD_LEFT);

    // addresses - cycle through realistic ones
    $addresses = [
        'Brgy. San Isidro, Quezon City',
        '123 Rizal Ave., Manila',
        'Blk 5 Lot 3, Cavite City',
        '456 Mabini St., Cebu City',
        'Purok 2, Davao del Sur',
        '789 Luna Street, Pasig City',
        'Barangay Poblacion, Makati',
        'Phase 3, Antipolo City',
        'No. 10 Bonifacio St., Iloilo',
        'Sitio Bagong Bayan, Batangas',
    ];
    $address = $addresses[$index % count($addresses)];

    // contact number
    $contact = '09' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);

    // Insert into users table
    $stmt = $conn->prepare("INSERT IGNORE INTO users (username, password, role, email) VALUES (?, ?, 'student', ?)");
    $stmt->bind_param('sss', $username, $password_hash, $email);
    if (!$stmt->execute()) {
        $errors[] = "User insert failed for $username: " . $stmt->error;
        continue;
    }
    $user_id = $conn->insert_id;

    if ($user_id == 0) {
        // username already existed — skip this entry
        $errors[] = "Skipped $username (already exists)";
        continue;
    }

    // Insert into students table
    $stmt2 = $conn->prepare(
        "INSERT IGNORE INTO students (user_id, lrn, firstname, lastname, address, contact_number)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt2->bind_param('isssss', $user_id, $lrn, $firstname, $lastname, $address, $contact);
    if ($stmt2->execute()) {
        $inserted++;
    } else {
        $errors[] = "Student insert failed for $username: " . $stmt2->error;
        // Roll back the user row
        $conn->query("DELETE FROM users WHERE id = $user_id");
    }
}

echo "<h2>✅ Seed Complete</h2>";
echo "<p><strong>$inserted</strong> dummy students inserted.</p>";
echo "<p><strong>Default password for all:</strong> <code>Student@123</code></p>";

if ($errors) {
    echo "<h3>⚠️ Errors / Skipped (" . count($errors) . ")</h3><ul>";
    foreach ($errors as $e) {
        echo "<li>" . htmlspecialchars($e) . "</li>";
    }
    echo "</ul>";
}

echo "<p><a href='admin/users.php?role=student'>→ View Student List in Admin</a></p>";
echo "<p style='color:red'><strong>⚠️ Delete this file after use!</strong></p>";
?>
