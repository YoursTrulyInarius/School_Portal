<?php
require_once 'config.php';

try {
    $result = $conn->query("DESCRIBE schedules");
    $output = "";
    if ($result) {
        $output .= "Columns in 'schedules' table:\n";
        while ($row = $result->fetch_assoc()) {
            $output .= $row['Field'] . " - " . $row['Type'] . "\n";
        }
    } else {
        $output .= "Error describing table: " . $conn->error;
    }
    file_put_contents('debug_schedules_out.txt', $output);
    echo "Done.";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
?>
