<?php

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 'On');

if ( isset($_POST["PHPSESSID"]) ) {
    session_id($_POST["PHPSESSID"]);
} else
if ( isset($_GET["PHPSESSID"]) ) {
    session_id($_GET["PHPSESSID"]);
}
session_start();

require_once 'lib/App.php';
new Zone_App();
?>