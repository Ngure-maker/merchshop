<?php
header('Content-Type: text/plain; charset=utf-8');
echo "step 1 ok\n";

require_once __DIR__ . '/config/universal_database.php';
echo "step 2 ok\n";

$db = new Database();
echo "step 3 ok\n";

$conn = $db->getConnection();
echo "step 4 ok\n";
