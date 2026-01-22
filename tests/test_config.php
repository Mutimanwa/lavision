<?php
echo "Testing config.php loading...\n";
require_once __DIR__ . '/../src/Config/config.php';
echo "SERVICES_PATH: " . (defined('SERVICES_PATH') ? SERVICES_PATH : 'NOT DEFINED') . "\n";
echo "SRC_PATH: " . (defined('SRC_PATH') ? SRC_PATH : 'NOT DEFINED') . "\n";

echo "Loading router.php...\n";
require_once SERVICES_PATH . '/router.php';
echo "Router loaded, testing get_current_page()...\n";

$_GET['page'] = 'test';
$result = get_current_page();
echo "get_current_page() returned: $result\n";

echo "All tests passed\n";
?>