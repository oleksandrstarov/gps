<?php
#### config ####
$gpskey=''; ## <- password for tracking client url
$gpxkey=''; ## <- password fpr gpx upload afterwards
$adminpsw=''; ## <- admin password
$rgurl='http://map.routegadget.net/'; ## <- live/reply viewer to be used
$title='Live Orienteering GPS tracking'; ##<- title at event list page
$footer='<i>Footer text</i>';## <- footer text at bottom right
$path='./live/'; ## path to folder all data gets stored
$allowall=false; ## true or false; if true then androids with 
                 ##wrong gpskey and unregistered ios devices are accepted.
$traccar=true;   ##  true or false; if true then Traccar app for IOS is presented as a
                 ##  client alternative. 
                 ##  Note! Traccar clients can be used only if the index.php script of
                 ##  this system is at the root of the host.
                 ##  In practice this means this must be instaled on own sub host, like
		         ##  'live.yourhost.com/index.php'
