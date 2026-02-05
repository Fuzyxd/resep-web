<?php
require_once __DIR__ . '/../includes/config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo "CONNERR:" . $conn->connect_error;
    exit(1);
}
$res = $conn->query('SHOW COLUMNS FROM resep');
if (!$res) {
    echo "QERR:" . $conn->error;
    exit(1);
}
$cols = [];
while ($row = $res->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo json_encode($cols);
