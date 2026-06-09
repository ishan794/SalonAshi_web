<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$db = \Config\Database::connect();
$settings = [
    'biz_phone' => '075 217 1225',
    'biz_email' => 'info@salonashi.com',
    'biz_address' => '641 Govinna Road, Athurugiriya',
    'biz_hours' => 'Open Daily: 9:00 AM - 8:00 PM'
];

foreach ($settings as $k => $v) {
    // Check if exists
    $exists = $db->table('settings')->where('k', $k)->countAllResults();
    if ($exists) {
        $db->table('settings')->where('k', $k)->update(['v' => $v, 'updated_at' => date('Y-m-d H:i:s')]);
    } else {
        $db->table('settings')->insert(['k' => $k, 'v' => $v, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
echo "Settings updated.";
