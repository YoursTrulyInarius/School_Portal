<?php
require_once 'config.php';
$res = $conn->query("DESCRIBE grades");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . $conn->error;
}
?>
