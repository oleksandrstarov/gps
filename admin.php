 <?php

include_once 'config.php';


if ($adminpsw == '') {
    print "You must set admin password first. Open config.php with text editor and edit it.";
    exit;
}

error_reporting(0);

if (!empty($_POST)) {
    $in = $_POST;
} else if (!empty($HTTP_POST_VARS)) {
    $in = $HTTP_POST_VARS;
}


print "<!DOCTYPE html><html>
<head>
<meta charset=\"UTF-8\" />
<style>
  html {
margin: 14;
font-family: sans-serif,helvetica;
font-size: 12px;
background: #ffffff;
}
  body
{
  background: #ffffff;
}
  :target {
   background: yellow !important;
}
 .brd { border: 1px solid #000060; padding: 5px; width: 700px; margin:auto; background-color:#F7DF9F;}
 .eventlist { border: 1px solid #000060; padding: 0px; width: 710px; margin:auto; background-color:#F7DF9F;}

</style>
<title>$title</title>
</head>
<body>";

if ($in['go'] != $adminpsw) {

if($in['go'] != ''){
sleep(2);
}
    print "<div class='brd'>";
    
    print "<br>
<span>Enter admin password:</span><br>
<form action='' method='post'>
<input type='hidden' name='act' value='menu' />
<input name='go' type='password' />
<input type='submit' value='Enter'/>
</form>
<br>
";
    print "</div>";
    exit;
}
print "<div class='brd'><h1>GPS tracking administration tool</h1></div>";# features with * are not implemented yet

######


if ($in['act'] == 'seton') {
    print "<div class='brd'>";
    
    
    $fp = fopen($path . '/liveonoff.txt', 'w');
    fwrite($fp, "1\n");
    fclose($fp);
    
    print "<br>Live tracking is not \"ON\"<br> Received live data gets stored.";
    
    print "</div>";
    $in['act'] = 'menu';
    
}
#######

if ($in['act'] == 'setoff') {
    print "<div class='brd'>";
    
    
    $fp = fopen($path . '/liveonoff.txt', 'w');
    fwrite($fp, "0\n");
    fclose($fp);
    
    print "<br>Live tracking is not \"OFF\"<br>Received live tracking data is not getting stored.";
    
    print "</div>";
    $in['act'] = 'menu';
    
}
#### sethidden
$servertime = time();
if ($in['act'] == 'sethidden') {
    print "<div class='brd'>";
    $timeleft = $in['hiddenh'] * 60 * 60 + $in['hiddenm'] * 60;
    
    $opentingtime = $servertime + $timeleft;
    $fp           = fopen($path . '/hiddenuntil.txt', 'w');
    fwrite($fp, '' . $opentingtime . "\n");
    fclose($fp);
    
    print "Time is now  $servertime and session will open at $opentingtime (after " . floor(($opentingtime - $servertime) / 60) . " minutes)";
    print "</div>";
    $in['act'] = 'menu';
}

#######
if ($in['act'] == 'clear') {
    print "<div class='brd'>";
    $fp = fopen($path . '.htgps', 'w');
    fclose($fp);
    
    print "Tracks cleared!";
    print "</div>";
    $in['act'] = 'menu';
}
######
if ($in['act'] == 'uploadmap') {
    print "<div class='brd'>";
    
    ### map image
    $filename = basename($_FILES["fileToUpload"]["name"]);
    
    # coordinates
    
    list($remove, $coords) = explode('_', $filename, 2);
    $coords = implode('.', explode('.', $coords, -1));
    
    $uploadOk = 1;
    
    // is georeferenced;
    
    $tmp = explode('_', $coords);
    if (count($tmp) != 9) {
        $uploadOk = 0;
        print "Geo-referencing is not ok";
    }
    
    $imageFileType = pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION);
    // Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }
    
    // Check file size
    if ($_FILES["fileToUpload"]["size"] > 1500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
    } else {
        if (file_exists($path . '/.htmap')) {
            unlink($path . '/.htmap');
        }
        
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $path . '/.htmap')) {
            echo "The image file has been uploaded.";
            $fp = fopen($path . '/.htmapname', 'w');
            fwrite($fp, '_' . $coords . "\n");
            fclose($fp);
            
        } else {
            echo "The image file was not uploaded (too large?).";
        }
    }
    
    
    print "</div>";
    $in['act'] = 'menu';
}
######
if ($in['act'] == 'removelivemap') {
    print "<div class='brd'>";
    
    $fp = fopen($path . '/.htmapname', 'w');
    fclose($fp);
    
    unlink($path . '/.htmap');
    
    print "Done!";
    
    print "</div>";
    $in['act'] = 'menu';
}
######
if ($in['act'] == 'setname') {
    print "<div class='brd'>";
    $fp = fopen($path . '/livename.txt', 'w');
    fwrite($fp, $in['livename'] . "\n");
    fclose($fp);
    
    print "Live sessioin name is now \"" . $in['livename'] . "\"!";
    print "</div>";
    $in['act'] = 'menu';
}

######
if ($in['act'] == 'setrunnername') {
    print "<div class='brd'>";
    
    $in['nick'] = preg_replace('/\\n/', '', $in['nick']);
    $in['nick'] = preg_replace('/\\r/', '', $in['nick']);
    $in['nick'] = preg_replace('/\\|/', '', $in['nick']);
    $in['aid']  = preg_replace('/\\n/', '', $in['aid']);
    $in['aid']  = preg_replace('/\\r/', '', $in['aid']);
    $in['aid']  = preg_replace('/\\|/', '', $in['aid']);
    
    $conf = file($path . '/.htnameconf');
    $fp   = fopen($path . '/.htnameconf', 'w');
    foreach ($conf as $rec) {
        $rec = rtrim($rec);
        list($aid, $name) = explode('|', $rec, 2);
        
        if ($aid == $in['aid']) {
            # skip
        } else {
            fwrite($fp, $rec . "\n");
        }
    }
    fwrite($fp, $in['aid'] . '|' . $in['nick'] . "\n");
    print "<br>Name configuration set:<br>" . $in['aid'] . ' - > ' . $in['nick'];
    fclose($fp);
    ##
    
    print "</div>";
    $in['act'] = 'menu';
}
######
if ($in['act'] == 'archive') {
    print "<div class='brd'>";
    
    
    ## live event name ##
    $livenames = file($path . '/livename.txt');
    $livename  = rtrim($livenames[0]);
    if (strlen($livename) < 3) {
        $livename = "Live session";
    }
    # live image coordinates ##
    $c      = file($path . '/.htmapname');
    $coords = rtrim($c[0]);
    
    $in['details'] = '' . trim(preg_replace('/\s+/', ' ', $in['details']));
    
    $in['day']   = '' . trim(preg_replace('/\s+/', ' ', $in['day']));
    $in['month'] = '' . trim(preg_replace('/\s+/', ' ', $in['month']));
    $in['year']  = '' . trim(preg_replace('/\s+/', ' ', $in['year']));
    
    $edate = $in['year'] . '-' . $in['month'] . '-' . $in['day'];
    
    ## read events
    
    
    $events = file($path . '/events.txt');
    
    $newid = 0;
    foreach ($events as $rec) {
        $rec = rtrim($rec);
        list($id, $date, $image, $name, $details) = explode('|', $rec, 5);
        if ($id > $newid) {
            $newid = $id;
        }
    }
    
    $newid++;
    
    $mapname = 'map' . $newid . '.jpg';
    
    if (strlen($coords) < 10) {
        $coords = '';
    } else {
        
        #print "<br>$liveimage -> $mapname ";
        # image
        
        
        $fp   = fopen($path . '/.htmap', "rb");
        $blob = fread($fp, filesize($path . '/.htmap'));
        fclose($fp);
        
        $fp = fopen($path . '/' . $mapname, 'wb');
        fwrite($fp, $blob);
        fclose($fp);
        
        
    }
    $livename = preg_replace('/\\|/', '', $livename);
    $mapname  = preg_replace('/\\|/', '', $mapname);
    $edate    = preg_replace('/\\|/', '', $edate);
    
    $newrow = '' . $newid . '|' . $edate . '|' . $coords . '|' . $livename . '|';
    
    $newrow = preg_replace('/\\n/', '', $newrow);
    $newrow = preg_replace('/\\r/', '', $newrow);
    
    file_put_contents($path . '/events.txt', $newrow . "\n", FILE_APPEND);
    
    # gps data
    $gps = file($path . '/.htgps');
    
    # make zip
    $zip      = new ZipArchive();
    $filename = $path . "/archive" . $newid . ".zip";
    
    if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
        exit("cannot open <$filename>\n");
    }
    
    $zip->addFromString('gps.txt', join('', $gps));
    $zip->close();
    
    
    #print "$newrow";
    print "<br>Live archived with ID $newid !";
    print "</div>";
    $in['act'] = 'menu';
}

###############################

if ($in['act'] == 'deletesession') {
    print "<div class='brd'>";
    
    
    ## read events
    
    
    $events = file($path . '/events.txt');
    
    
    $fp = fopen($path . '/events.txt', 'w');
    foreach ($events as $rec) {
        $rec = rtrim($rec);
        list($id, $date, $image, $name, $details) = explode('|', $rec, 5);
        
        if ($id == $in['id']) {
            
            #lets not delete now but only re-name gps data and image to be rescued with ftp
            ##
            $tmp = microtime(true);
            rename($path . '/' . $image, $path . '/deleted-' . $tmp . $image);
            rename($path . '/live_' . $id . '.txt', $path . '/deleted-' . $tmp . 'live_' . $id . '.txt');
            
            ##
            print "<br>$id $name removed form event list and gps trackes and image fiels has now delete -prefix.
<br>Recover or delete them permanently with ftp.";
        } else {
            # print other events back untouched
            fwrite($fp, $rec . "\n");
        }
    }
    
    
    print "</div>";
    $in['act'] = 'menu';
}
###############################

if ($in['act'] == 'changesessionname') {
    print "<div class='brd'>";
    
    
    $in['sesname'] = preg_replace('/\\n/', '', $in['sesname']);
    $in['sesname'] = preg_replace('/\\r/', '', $in['sesname']);
    $in['sesname'] = preg_replace('/\\|/', '', $in['sesname']);
    
    ## read events
    
    
    $events = file($path . '/events.txt');
    
    
    $fp = fopen($path . '/events.txt', 'w');
    foreach ($events as $rec) {
        $rec = rtrim($rec);
        list($id, $date, $image, $name, $details) = explode('|', $rec, 5);
        
        if ($id == $in['id']) {
            fwrite($fp, $id . '|' . $date . '|' . $image . '|' . $in['sesname'] . '|' . $details . "\n");
        } else {
            fwrite($fp, $rec . "\n");
        }
    }
    
    print "<br>Name changed !";
    print "</div>";
    $in['act'] = 'menu';
}

###############################

if ($in['act'] == 'menu') {
    
    
    $events = file($path . '/events.txt');
    
    ## set on/off
    print "<div class='brd'><b>Set live tracking \"ON\" or \"OFF\"</b>
<br><br>
<table><tr><td>
<form action='' method='post'>
<input type='hidden' name='act' value='seton' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<input type='submit' value='Set live ON'/>
</form>
</td>
<td>
<form action='' method='post'>
<input type='hidden' name='act' value='setoff' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<input type='submit' value='Set live OFF'/>
</form>
</td></tr></table>
</div>";
    # hidden until
    
    # is hidden until
    $hidden       = file($path . '/hiddenuntil.txt');
    $hiddentstamp = 1 * $hidden[0];
    
    print "<div class='brd'>";
    if ($hiddentstamp < $servertime) {
        print "<p>Current live gps session is published</p>";
        
    } else {
        print "<p>Live session will be published after " . floor(($hiddentstamp - $servertime) / 60 / 60) . "h " . (floor(($hiddentstamp - $servertime) / 60) - 60 * floor(($hiddentstamp - $servertime) / 60 / 60)) . " min</p>";
    }
    print "<b>Set opening time</b
<br><br>

<form action='' method='post'>
<input type='hidden' name='act' value='sethidden' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
Will open after: <input type=text name='hiddenh' value='0' size='5'>h and <input type=text name='hiddenm' size='5' value='0'> minutes.
<input type='submit' value='Set hidden time'/>
</form>

</div>";
    
    # init
    print "<div class='brd'><b>Remove/clear all old tracks</b> from the live session 
<form action='' method='post'>
<input type='hidden' name='act' value='clear' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<input type='submit' value='clear'/>
</form>
</div>";
    
    #upload live map
    print "<div class='brd'><b>Upload geo-referenced map image</b> (jpg/png/gif) for live tracking<br/>
(RG 'ad hoc' georeferencing in file name. You can geo-reference your map image <a href='" . $rgurl . "' target=_blank>here</a>, see <a href=https://www.youtube.com/watch?v=A7EfzcCOGBM target=_blank>tutorial video</a>)
<form action='' method='post' enctype='multipart/form-data'>
<input type='hidden' name='act' value='uploadmap' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<input type='file' name='fileToUpload' id='fileToUpload'>
<input type='submit' value='Upload selected map'/>
</form>
</div>";
    
    #remove live map
    print "<div class='brd'><b>Remove map form current live session</b>
<form action='' method='post'>
<input type='hidden' name='act' value='removelivemap' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<input type='submit' value='remove map'/>
</form>
</div>";
    
    
    #set name
    print "<div class='brd'><b>Set title</b> for current live session
<form action='' method='post'>
<input type='hidden' name='act' value='setname' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<input type='text' name='livename' value='' />
<input type='submit' value='Set live session name'/>
</form>
</div>";
    
    
    
    
    #change name
    print "<div class='brd'><b>Change name of an archived session</b>
<form action='' method='post'>
<input type='hidden' name='act' value='changesessionname' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<br><select name='id'><option name=-1>Select</option>";
    
    
    foreach ($events as $rec) {
        $rec = rtrim($rec);
        list($id, $date, $image, $name, $details) = explode('|', $rec, 5);
        print "<option value='$id'>$name</option>\n";
    }
    
    
    print "</select>
<br>New name:<br>
<input type='text' name='sesname' value='' />
<input type='submit' value='Change session name'/>
</form>
</div>";
    
    #delete session
    print "<div class='brd'><b>Delete an archived session</b>
<form action='' method='post'>
<input type='hidden' name='act' value='deletesession' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<br><select name='id'><option name=-1>Select</option>";
    
    
    foreach ($events as $rec) {
        $rec = rtrim($rec);
        list($id, $date, $image, $name, $details) = explode('|', $rec, 5);
        print "<option value='$id'>$name</option>\n";
    }
    print "</select>
<input type='submit' value='Delete session from archive'/>
</form>
</div>";
    
    #set name for aid
    print "<div class='brd'><b>Set name for a runner</b> (overrides the name coming from tracking client)<br> Also set here allowed device IDs for Traccar client users.
<form action='' method='post'>
<input type='hidden' name='act' value='setrunnername' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<table>
<tr><td>ID:</td><td><input type='text' name='aid' value='' /></td></tr>
<tr><td>Name:</td><td><input type='text' name='nick' value='' /></td></tr>
</table>
<input type='submit' value='Set runner name'/>
</form>
</div>";
    
    #archive live session
    print "<div class='brd'><b>Achive current live session</b> 
<form action='' method='post'>";
    
    
    print "<br>Event date  year-month-day<br><table><tr><td><select name=year>";
    
    for ($yy = date("Y") - 5; $yy < date("Y") + 2; $yy++) {
        if (date("Y") == $yy) {
            print "<option value=$yy selected>$yy</option>\n";
        } else {
            print "<option value=$yy>$yy</option>\n";
        }
    }
    
    print "</select></td><td><select name=month>";
    
    for ($mm = 1; $mm < 13; $mm++) {
        if ($mm < 10) {
            $mm = '0' . $mm;
        }
        if (date("m") == $mm) {
            print "<option value=$mm selected>$mm</option>\n";
        } else {
            print "<option value=$mm>$mm</option>\n";
        }
    }
    print "</select></td><td><select name=day>";
    
    for ($mm = 1; $mm < 32; $mm++) {
        if ($mm < 10) {
            $mm = '0' . $mm;
        }
        if (date("d") == $mm) {
            print "<option value=$mm selected>$mm</option>\n";
        } else {
            print "<option value=$mm>$mm</option>\n";
        }
    }
    print "</select></td></tr></table>";
    
    print "<input type='hidden' name='act' value='archive' />
<input name='go' type='hidden' value='" . $in['go'] . "'/>
<input type='submit' value='Archive'/>
</form>
</div>";
    
    #exit
    print "<div class='brd'>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='Exit admin tool' onClick=\"window.location='./'\" />
</div>";
    
    
    exit;
}

?> 