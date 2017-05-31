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

if ($gpskey == '' && !$allowall) {
    exit;
}

if (array_key_exists('act', $in) || array_key_exists('lat', $in)) {
    
    
} else {
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    list($url, $rest) = explode('?', $url);
    
    print "<!doctype html>
<html>
<head>
<style>
body{font-family: Verdana, Arial, Helvetica, sans-serif; color: #000000; font-size: 13px; }

</style>
<meta name='viewport' content='width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1'>
</head>
<body BGCOLOR='#ffffff'>
<br>
<b>Welcome to RG live GPS tracking</b>
<br><br>
Select your operating system:
<br><br>
<a href='#' onclick='document.getElementById(\"gpslogger\").style.display = \"block\";document.getElementById(\"traccar\").style.display = \"none\";'>Android</a>";

## is this istalled to host's root
if(substr($_SERVER[REQUEST_URI],0,8) != '/gps.php'){
    $traccar=false; ## trackar works only at host's root
}

if($traccar){

print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' onclick='document.getElementById(\"traccar\").style.display = \"block\";document.getElementById(\"gpslogger\").style.display = \"none\";'>IOS</a>";
}

print "<br>
<div id='traccar' style='display:none;'>
<br><br>Install Traccar client for IOS: <a href='https://itunes.apple.com/us/app/traccar-client/id843156974?mt=8' target='_blank'>Traccar client</a>.

<br>
<br>
Traccar client settings:

<br><br>- <b>Device identifier:</b> <i>Use the string you get by filling the form below</i> 
<br>- <b>Server address:</b> $_SERVER[HTTP_HOST]
<br>- <b>Server port:</b> 80
<br>- <b>encryption:</b> Off
<br>- <b>frequency:</b> 5   (Location stored every 5 seconds)
<br>- <b>distance:</b> 0
 <br><br>
Fill in the form to get <b>Device identifier</b> for your Traccar app:<br><br>
<form action='$url' method=post>
<input type=hidden name=act value='url' />
Password (ask tracking session organizer):
<br><input type=text name=key value=''  style='width:100%; background-color:#ffff90;' />
<br><br>Your nickname:
<br><input type=text name=name value=''  style='width:100%; background-color:#ffff90;' />
<input type='hidden' name='app' value='traccar'>
<br><br><input type=submit value='Get Device identifier!'  style='width:100%;' />
</form>
</div>
<br><br>
<div id='gpslogger' style='display:none;'>
Install GPS Logger app for Android: <a href='https://play.google.com/store/apps/details?id=com.mendhak.gpslogger' target='_blank'>GPS Logger</a> 
<br><br>
Install the app, set it to send point to  \"custom URL\" you can get the url from below.
<br><br>In addition, configure it like in screen captures below.
<br><br>
<b>Tracking url for your phone:</b>
 <br><br><form action='$url' method=post>
<input type=hidden name=act value='url' />
Password (ask tracking session organizer):
<br><input type=text name=key value=''  style='width:100%; background-color:#ffff90;' />
<br><br>Your nickname:
<br><input type=text name=name value=''  style='width:100%; background-color:#ffff90;' />
<input type='hidden' name='app' value='gpslogger'>
<br><br><input type=submit value='Get tracking url!'  style='width:100%;' />
</form>
<br>
<br>
<img src='gpstrackermenu1.png' width='70%'>
<br>
<img src='gpstrackermenu2.png' width='70%'>
<br>
<img src='gpstrackermenu3.png' width='70%'>
</div>
</body>";
    
    exit;
}
if ($in['act'] == 'url') {
    if ($in['key'] == $gpskey) {
        print "<!doctype html>
<html>
<head>
<style>
body{font-family: Verdana, Arial, Helvetica, sans-serif; color: #000000; font-size: 13px; }

}
</style>
<meta name='viewport' content='width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1'>
</head>
<body BGCOLOR='#ffffff'>
<br>";
if($in['app'] =='gpslogger'){
        $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
        $url .= "?lat=%LAT&lon=%LON&time=%TIME&s=%SPD&acc=%ACC&aid=%AID&desc=%DESC&act=s&key=" . urlencode($in['key']) . "&name=" . urlencode($in['name']);
        print "<br>GPS Logger:<br>Tap text field below to copy it to clipboard.<br><input value='" . $url . "' onclick='this.focus();this.select();document.execCommand(\"copy\");alert(\"url is now copied to clipboard\");' style='width:100%; background-color:#ffff90;'>";
}
if($in['app'] =='traccar'){

	$uid=uniqid().$in['key'].','.$in['name'];

        print "<br><br><br>Traccar:<br>Tap text field below and copy it to the clipboard.<br><input value='" . $uid . "' onclick='this.focus();this.select();' style='width:100%; background-color:#ffff90;'>";
}
        print "</body></html>";
    } else {
        print "<!doctype html>
<html>
<head>
<style>
body{font-family: Verdana, Arial, Helvetica, sans-serif; color: #000000; font-size: 13px; }

</style>
<meta name='viewport' content='width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1'>
</head>
<body BGCOLOR='#ffffff'>incorrect pswd</body></html>";
    }
    
    exit;
    
}

## allow all if configured so

if ($allowall) {
    $in['key'] = $gpskey;
}

# traccar ios clinet support
if (array_key_exists('id', $in)) {
    $in['name'] = $in['id'];
    if (file_exists($path . '/.htnameconf')) {
        $runners = file($path . '/.htnameconf');
        
        for ($c = 0; $c < count($runners); $c++) {
            list($id, $name, $rest) = explode('|', $runners[$c]);
            
            if ($id == $in['id']) {
                $in['key']  = $gpskey;
                $in['name'] = $name;
                $in['aid']  = $in['id'];
            }
            
        }
    }
}

# save gps data
if ($in['key'] == $gpskey) {
    if ($in['act'] == 's' || array_key_exists('lat', $in)) {
        if (file_exists($path . '/.htnameconf')) {
            $runners = file($path . '/.htnameconf');
            
            for ($c = 0; $c < count($runners); $c++) {
                list($id, $name, $rest) = explode('|', $runners[$c]);
                
                if ($id == $in['aid']) {
                    $in['name'] = $name;
                }
                
            }
        }
        
        $name = preg_replace('/\\n/', '', $in['name']);
        $name = preg_replace('/\\r/', '', $name);
        $name = preg_replace('/"/', '', $name);
        $name = preg_replace('/\\\\/', '', $name);
        
        if (array_key_exists('timestamp', $in)) {
            $ts = 1 * $in['timestamp'];
        } else {
            
            list($in['time'], $rest) = explode('.', $in['time']);
            list($rest, $in['time']) = explode('T', $in['time']);
            list($hour, $min, $sec) = explode(':', $in['time']);
            
            $ts = 1 * $sec + 60 * $min + 60 * 60 * $hour;
        }
        if ($ts == 0) {
            exit;
        } # skip if time is missing
        
        $servertime = time();
        $ts_        = $ts - floor($ts / 3600) * 3600;
        $ts_        = floor(($servertime - $ts_) / 3600 + .5) * 3600 + $ts_;
        $ts         = $ts_;
        
        if ($ts > $servertime + 900) {
            exit;
        } # skip times over 15 min in future
        
        $lon = floor(1000000 * $in['lon']) / 1000000;
        $lat = floor(1000000 * $in['lat']) / 1000000;
        
        $long = floor(1000000 * $in['longitude']) / 1000000;
        if ($lon == 0 && $long != 0) {
            $lon = $long;
        }
        $aid = $in['aid'];
        
        $aid = preg_replace('/\\n/', '', $aid);
        $aid = preg_replace('/\\r/', '', $aid);
        $aid = preg_replace('/"/', '', $aid);
        $aid = preg_replace('/\\\\/', '', $aid);
        
        
        if (!$in['desc'] == '') {
            $in['desc'] = preg_replace('/\\n/', '', $in['desc']);
            $in['desc'] = preg_replace('/\\r/', '', $in['desc']);
            $in['desc'] = preg_replace('/"/', '', $in['desc']);
            $in['desc'] = preg_replace('/\\\\/', '', $in['desc']);
            
            $desc = ',"desc":"' . $in['desc'] . '"';
        }
        
        if (array_key_exists('speed', $in)) {
            $in['s'] = $in['speed'];
        }
        
        $s = floor(10 * $in['s']) / 10;
        $s = ',"sp":"' . $s . '"';
        
        $newrow = '{"id":"' . $aid . '","lat":' . $lat . ',"lon":' . $lon . ',"sec":' . $ts . $s . $desc . ',"name":"' . $name . '"},' . "\n";
        $file   = $path . '/.htgps';
        file_put_contents($file, $newrow, FILE_APPEND);
        exit;
        
    }
}