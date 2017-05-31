<?php
$time_start = microtime(true);
error_reporting(0);
include_once 'config.php';
if (!empty($_GET)) {
    $in = $_GET;
} else if (!empty($HTTP_GET_VARS)) {
    $in = $HTTP_GET_VARS;
}

if (!empty($_POST)) {
    $in = $_POST;
} else if (!empty($HTTP_POST_VARS)) {
    $in = $HTTP_POST_VARS;
}

if (empty($in)) {
    $in = array();
}

$events = file($path . '/events.txt');

# map
if (array_key_exists('map', $in)) {
    
    for ($c = 0; $c < count($events); $c++) {
        list($id, $date, $image, $name, $details) = explode('|', $events[$c], 5);
        
        if ($id == $in['map']) {
            $mapfile = $path . '/map' . $id . '.jpg';
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-type: image/jpeg');
            readfile($mapfile);
            exit;
            
        }
    }
    
    
    exit;
}

if (!array_key_exists('n', $in)) {
    exit;
}
$in['n'] = max(1 * $in['n'], 0);

header('Access-Control-Allow-Origin: *');

if ($in['n'] != 0) {
    exit;
}

$zip = new ZipArchive;
if ($zip->open($path . '/archive' . (1 * $in['id']) . '.zip') === TRUE) {
    $dat = $zip->getFromName('gps.txt');
    $zip->close();
}

print '[';
print $dat;


$time_end = microtime(true);
$time     = $time_end - $time_start;

print '{"n":' . count($dat) . ',"duration":' . $time . '}]';
