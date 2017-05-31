<?php

include_once 'config.php';
error_reporting(0);
$version='20170526';

## test is passwords set or still original ones
if ($adminpsw == '' || $gpskey == '' || $gpxkey == '') {
    
    print "All necessary passwords are not set. You must open config file with text editor and edit it. These are not set yet:";
    if ($adminpsw == '') {
        print "<br>- admin password";
    }
    if ($gpskey == '') {
        print "<br> - password for live tracking (Android) clients";
    }
    if ($gpxkey == '') {
        print "<br> - password for gpx file upload  to archived sessions";
    }
    exit;
}

# testi if path is correct and events.txt is tehre
if (!file_exists($path . '/events.txt')) {
    print "path varaiable on config.php is not correct";
    exit;
}

# test is there write permissions
if (!file_exists($path . '/rw.txt')) {
    $servertime = time();
    $file       = $path . '/rw.txt';
    file_put_contents($file, '' . $servertime . "\n", FILE_APPEND);
    
    $test = file($file);
    $val  = $test[0];
    if (1 * $val != $servertime) {
        print 'Write permissions to the folder data is stored seems to be incorrect.';
        exit;
    }
}
## ok tests done ###

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


## traccar client handling is here because it can send data only to host root

# traccar ios clinet support
if ($traccar && array_key_exists('id', $in) && array_key_exists('lat', $in)) {

    $in['aid']=substr($in['id'], 0, 13);

    list($key,$in['name']) = explode(',',$in['id'],2);

    $in['key']=substr($key, 13);

## allow all if configured so

if ($allowall) {
    $in['key'] = $gpskey;
}
    if (file_exists($path . '/.htnameconf')) {
        $runners = file($path . '/.htnameconf');
        
        for ($c = 0; $c < count($runners); $c++) {
            list($id, $name, $rest) = explode('|', $runners[$c]);
            
            if ($id == $in['id']) {
                $in['name'] = $name;
                $in['aid']  = $in['id'];
            }
            
        }
    }

# save gps data
if ($in['key'] == $gpskey) {
    if ($in['act'] == 's' || array_key_exists('lat', $in)) {
        
        $name = preg_replace('/\\n/', '', $in['name']);
        $name = preg_replace('/\\r/', '', $name);
        $name = preg_replace('/"/', '', $name);
        $name = preg_replace('/\\\\/', '', $name);
        
        if (array_key_exists('timestamp', $in)) {
            $ts = 1 * $in['timestamp'];
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
        
        
        if (array_key_exists('speed', $in)) {
            $in['s'] = $in['speed'];
        }
        
        $s = floor(10 * $in['s']*1.852/3.6) / 10;# knots to m/s 
        $s = ',"sp":"' . $s . '"';
        
        $newrow = '{"id":"' . $aid . '","lat":' . $lat . ',"lon":' . $lon . ',"sec":' . $ts . $s . ',"name":"' . $name . '"},' . "\n";
        $file   = $path . '/.htgps';
        file_put_contents($file, $newrow, FILE_APPEND);
        exit;
        }
    }
}

## traccar end ####

###########
$myurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$myurl = implode('/', explode('/', $myurl, -1)) . '/';

##########


### gpx export####
if ($in['act'] == 'getgpx') {
    
    header('Access-Control-Allow-Origin: *');
    header("Content-Type: text/xml");
    header('Content-Disposition: attachment; filename="' . $in['name'] . '.gpx"');
    
    print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" version=\"1.1\" creator=\"RouteGadget GPS Tracking\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:tp1=\"http://www.garmin.com/xmlschemas/TrackPointExtension/v1\" xsi:schemaLocation=\"http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd\">
<trk>
<trkseg>";
    
    $events  = file($path . '/events.txt');
    $pretime = -1;
    for ($c = 0; $c < count($events); $c++) {
        list($id, $date, $image, $name, $details) = explode('|', $events[$c], 5);
        
        $id = 1 * $id;
        
        if ($id == $in['id']) {
            
            $zip = new ZipArchive;
            if ($zip->open($path . '/archive' . (1 * $id) . '.zip') === TRUE) {
                $dat = $zip->getFromName('gps.txt');
                $zip->close();
            }
            
            $d = json_decode('[' . $dat . '{}]');
            
            $format = "Y-m-d\TH:i:s\Z";
            
            for ($i = 0; $i < count($d); $i++) {
                
                if ($d[$i]->id == $in['aid'] && $pretime < $d[$i]->sec) {
                    $pretime = $d[$i]->sec;
                    $rname   = $d[$i]->name;
                    
                    print " <trkpt lat=\"" . ($d[$i]->lat) . "\" lon=\"" . ($d[$i]->lon) . "\">
<time>" . gmdate($format, ($d[$i]->sec)) . "</time>
</trkpt>\n";
                    
                }
            }
        }
    }
    print "</trkseg>
</trk>
<name>$rname</name>
</gpx>";
    exit;
}


###################


print "<!DOCTYPE html><html>
<head>
<meta charset=\"UTF-8\" />
<link rel=\"stylesheet\" href=\"style.css\" />
<title>$title</title>
<!-- version: $version -->
</head>
<body>
<div class='brd' style='background-image: url(\"banner.png\");' onclick='document.location=\"/\"'>
<h1>$title</h1>
<i>$title2</i>
</div>
";
## live status ###
$livestate = 1;
$state     = file($path . '/liveonoff.txt');
if (1 * $state[0] == 0) {
    $livestate = 0;
}
## live event name ##
$livenames = file($path . '/livename.txt');
$livename  = $livenames[0];
if (strlen($livename) < 3) {
    $livename = "Live session";
}
# is hidden until
$hidden       = file($path . '/hiddenuntil.txt');
$hiddentstamp = 1 * $hidden[0];

# live image ##
$cooordinates = file($path . '/.htmapname');
$coord        = $cooordinates[0];
###########################
$livemapurl   = '';
if (strlen($coord) > 3) {
    $livemapurl = 'mapurl=' . urlencode($myurl . 'live.php?map=1&') . '?' . $coord;
}
print "<div class='brd'>";
if ($livestate == 0) {
    print "<p>No ongoing live sessions at the moment</p>";
} else {
    $servertime = time();
    if ($hiddentstamp > $servertime) {
        print "<p>Live GPS tracking \"<i>$livename</i>\" will open in  " . floor(($hiddentstamp - $servertime) / 60 / 60) . "h " . (floor(($hiddentstamp - $servertime) / 60) - 60 * floor(($hiddentstamp - $servertime) / 60 / 60)) . " min</p>";
    } else {
        
        print "<p>GPS tracking session \"<i>$livename</i>\" is on!</p>";
        
        print "<p><a href='" . $rgurl . "?" . $livemapurl . "&liveurl=" . urlencode($myurl . 'live.php?') . "&title=" . urlencode($livename) . "'>Watch <i>$livename</i></a></p>";
        
        
        $dat = file($path . '/.htgps');
        if (count($dat) > 2) {
            
            list($rest, $sec) = explode('sec":', $dat[count($dat) - 1], 2);
            list($sec, $rest) = explode(',', $sec, 2);
            #print " $sec ".time();
            $sec    = 1 * $sec;
            $latest = time() - $sec;
            if ($latest > 0 && $latest < 24 * 60 * 60) {
                print "<p>Latest GPS point recorded " . (floor($latest / 60 / 60)) . ' h ' . floor($latest / 60 - (floor($latest / 60 / 60) * 60)) . " min ago</p>";
            }
            
        } else {
            print "<p>No track data stored yet for this session</p>";
        }
    }
}

print "</div>";


### event list####
if ($in['act'] == '') {
    $j = 0;
    $y = date("Y");
    
    $events = file($path . '/events.txt');
    
    for ($c = 0; $c < count($events); $c++) {
        list($id, $date, $image, $name, $details) = explode('|', $events[$c], 5);
        
        $events[$c] = implode('|', array(
            $date,
            $id,
            $image,
            $name,
            $details
        ));
    }
    
    sort($events);
    reset($events);
    $events = array_reverse($events);
    print "<div class='eventlist'><p>&nbsp;<b>Archived sessions</b></p><table cellspacing='0' cellpadding='5' style='width:100%;margin:0;'>";
    
    
    foreach ($events as $rec) {
        $rec = rtrim($rec);
        list($date, $id, $image, $name, $details) = explode('|', $rec, 5);
        list($year, $mon, $day) = explode('-', $date);
        
        
        if ($image == '') {
            $ismap = '<strike style="color:#b0b0b0">Map</strike>';
        } else {
            $ismap = 'Map';
        }
        
        $bg = 'odd';
        if ($j % 2 == 0) {
            $bg = 'even';
        }
        $mapurl = '';
        if ($image == '') {
            $mapurl = '';
        } else {
            $mapurl = urlencode($myurl . 'session.php?map=' . $id . '&?') . $image;
        }
        $j++;
        
        if ($name == '') {
            $name = 'Unnamed event';
        }
        
        print "<tr id='e" . $id . "' class='$bg'><td style='width:10%'><a href='?y=" . $year . "#e" . $id . "'>#</a> $date</td><td><a href='" . $rgurl . "?mapurl=" . $mapurl . "&replayurl=" . urlencode($myurl . 'session.php?id=' . $id) . "&title=" . urlencode($date . ' ' . $name) . "'>$name</a></td><td>$ismap</td><td><a href='?act=gpx&id=" . $id . "'>GPX</a></td></tr>\n";
        # <a href='?act=gpxup&id=".$id."'>Add your gpx track</a>
        print "<tr class='$bg'><td colspan='1'></td><td colspan='3' style='width: 65%'>$details</td></tr>\n";
        
    }
    print "</table></div>";
    #print "<br><br><a href='index.php?act=admin'>Admin</a>";
    
    print "<div class='brd' style='text-align: right'>
<i>$footer</i>
</div>";
    
    exit;
}

### gpx upload

if ($in['act'] == 'gpxup') {
    print "<div class='brd'>";
    if ($in['key'] != $gpxpassword) {
        print "wrong password";
        exit;
        
    }
    
    if ($_FILES["fileToUpload"]["size"] > 4500000) {
        echo "Sorry, your file is too large.";
        exit;
    }
    
    $tmin = -1;
    $tamx = -1;
    
    $gpx = file($_FILES["fileToUpload"]["tmp_name"]);
    $aid = 'gpx_' . generateRandomString(10);
    
    #nickname
    $name = preg_replace('/\\n/', '', $in['name']);
    $name = preg_replace('/\\r/', '', $name);
    $name = preg_replace('/"/', '', $name);
    $nick = preg_replace('/\\\\/', '', $name);
    
    
    if (strlen($nick) < 3) {
        print "Track was not stored. Nickname is too short.";
        exit;
    }
    
    
    
    $xml = simplexml_load_string(implode('', $gpx)) or die("Error in gpx file.");
    
    
    foreach ($xml->trk->trkseg->trkpt as $trkpt) {
        
        $sec = floor(date("U", strtotime($trkpt->time)));
        
        if ($tmin == -1 || $tmin > $sec) {
            $tmin = $sec;
        }
        if ($tmax == -1 || $tmax < $sec) {
            $tmax = $sec;
        }
        
        
        $out .= '{"id":"' . $aid . '","lat":' . (floor(1000000 * substr($trkpt['lat'], 0, 11)) / 1000000) . ',"lon":' . (floor(1000000 * substr($trkpt['lon'], 0, 11)) / 1000000) . ',"sec":' . $sec . ',"name":"' . $nick . '"},' . "\n";
        
    }
    $lmin = -1;
    $lmax = -1;
    if (!file_exists($path . '/archive' . (1 * $in['id']) . '.zip')) {
        print "Track was not stored. No arhived session on server to append to!";
        exit;
    }
    
    $zip = new ZipArchive;
    if ($zip->open($path . '/archive' . (1 * $in['id']) . '.zip') === TRUE) {
        $dat = $zip->getFromName('gps.txt');
        $zip->close();
    } else {
        print "failed!";
        exit;
    }
    
    $data = explode("\n", $dat);
    
    for ($i = 0; $i < count($data); $i++) {
        $tmp = explode('sec":', $data[$i], 2);
        $tmp = explode(',', $tmp[1], 2);
        $sec = 1 * $tmp[0];
        if ($lmin == -1 || $lmin > $sec) {
            $lmin = $sec;
        }
        if ($lmax == -1 || $lmax < $sec) {
            $lmax = $sec;
        }
        
        $tmp = explode('name":"', $data[$i], 2);
        $tmp = explode('"', $tmp[1], 2);
        if ($tmp[0] == $nick) {
            print "Track was not stored. track for $nick already exists";
            exit;
            
        }
        
    }
    
    
    #print "$tmin $tmax $lmin $lmax <br><br>\n";
    if ($tmin < $lmax + 60 * 60 * 5 && $tmax > $lmin - 60 * 60 * 5) {
        
        if (file_exists($path . '/archive' . (1 * $in['id']) . '.zip')) {
            $out = $dat . $out;
            
            $newzip   = new ZipArchive();
            $filename = $path . '/temparchive' . (1 * $in['id']) . '.zip';
            
            if ($newzip->open($filename, ZipArchive::CREATE) !== TRUE) {
                print "cannot open <$filename>\n";
                exit;
            }
            
            $newzip->addFromString('gps.txt', $out);
            $newzip->close();
            
            if (filesize($filename) > filesize($path . '/archive' . (1 * $in['id']) . '.zip')) {
                unlink($path . '/archive' . (1 * $in['id']) . '.zip');
                rename($filename, $path . '/archive' . (1 * $in['id']) . '.zip');
                
                print "Track was stored succesfully!";
            } else {
                print "rename failed";
                exit;
            }
        }
        
    } else {
        print "Track was not stored. GPX timespan does not overlap with the existing tracks! ";
    }
    print "<a href='./'>Back</a>";
    print "<br><br></div><div class='brd' style='text-align: right'>
<i>$footer</i>
</div>";
    
    exit;
}


### gpx list####
if ($in['act'] == 'gpx') {
    print "<div class='brd' style='text-align: left'><br>Export GPX files:<br><br>";
    $events = file($path . '/events.txt');
    $gpxurl = '';
    $mapurl = '';
    for ($c = 0; $c < count($events); $c++) {
        list($id, $date, $image, $name, $details) = explode('|', $events[$c], 5);
        
        $id = 1 * $id;
        
        if ($id == $in['id']) {
            
            $runners = array();
            
            $zip = new ZipArchive;
            if ($zip->open($path . '/archive' . (1 * $id) . '.zip') === TRUE) {
                $dat = $zip->getFromName('gps.txt');
                $zip->close();
            }
            
            
            $d = json_decode('[' . $dat . '{}]');
            
            for ($i = 0; $i < count($d); $i++) {
                $aid  = '';
                $name = '';
                
                foreach ($d[$i] as $k => $v) {
                    if ($k == 'id') {
                        $aid = $v;
                    }
                    if ($k == 'name') {
                        $name = $v;
                    }
                }
                
                if ($aid != '' && !array_key_exists($aid, $runners)) {
                    $runners[$aid] = $aid;
                    print '<br><a href="?act=getgpx&id=' . $id . '&aid=' . $aid . '&name=' . urlencode($name) . '">' . $name . '</a>';
                    if ($gpxurl == '') {
                        $gpxurl = urlencode($myurl . '?act=getgpx&id=' . $id . '&aid=' . $aid);
                        
                    } else {
                        $gpxurl = $gpxurl . ',' . urlencode($myurl . '?act=getgpx&id=' . $id . '&aid=' . $aid);
                        
                    }
                }
                
                
            }
            
            if ($image == '') {
                $mapurl = '';
            } else {
                $mapurl = 'mapurl=' . urlencode($myurl . 'session.php?map=' . $id . '&?') . $image;
            }
            
        }
        
        
    }
    if ($gpxurl != '') {
        print '<br><br><a href="' . $rgurl . '?' . $mapurl . '&gpxurl=' . $gpxurl . '">GPX tracks on Routegadget ad hoc</a>';
    }
    
    
    print "</div>";
    
    
    print "<div class='brd'><br><br><b>Upload a new gpx file to this archive</b> <br/>
<br/>Did you ran this event and record your track with GPS watch? You can upload your track.
<br><br>Gpx recording time must overlap with already existing tracks.
<br>
<br>
You need password to uplaod gpx file. Ask session organizer if you don't know it.
<form action='' method='post' enctype='multipart/form-data'>
<br><br>Password: <input type='password' name='name' key='' />
<br><br>Nickname: <input type='text' name='name' value='' />
<input type='hidden' name='act' value='gpxup' />
<br><br>Gpx file: <input type='file' name='fileToUpload' id='fileToUpload'>
<input type='hidden' name='id' value='" . (1 * $in['id']) . "' />
<br><br><input type='submit' value='Upload gpx'/>
</form>
</div>";
    
    
    print "<div class='brd' style='text-align: right'>
<i>$footer</i>
</div>";
}

function generateRandomString($length = 10)
{
    $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

