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

# is hidden until
$hidden       = file($path . '/hiddenuntil.txt');
$hiddentstamp = 1 * $hidden[0];
$servertime   = time();
if ($hiddentstamp > $servertime) {
    exit;
}
## live status ###
$state = file($path . '/liveonoff.txt');
if (1 * $state[0] == 0) {
    exit;
}

# map
if (array_key_exists('map', $in)) {
    
    if (file_exists($path . '/.htmap')) {
        header('Content-type: image/jpeg');
        while (ob_get_level()) {
            ob_end_clean();
        }
        readfile($path . '/.htmap');
    }
    exit;
}

if (!array_key_exists('n', $in)) {
    exit;
}

header('Access-Control-Allow-Origin: *');

$dat = file($path . '/.htgps');

if ($in['n'] > 0) {
    $in['n'] = max(1 * $in['n'], 0);
    print '[';
    
    for ($i = $in['n']; $i < count($dat); $i++) {
        print $dat[$i];
    }
} else {
    print '[';
    print implode('', $dat);
}

$time_end = microtime(true);
$time     = $time_end - $time_start;

print '{"n":' . count($dat) . ',"duration":' . $time . '}]';
