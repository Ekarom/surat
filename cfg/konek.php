<?php
// Bridge configuration to adapt dbconn.php for backup_restore.php
require_once __DIR__ . '/../dbconn.php';

// Map variables from dbconn.php to what backup_restore.php expects
if (isset($host)) {
    $db_host = $host;
}
if (isset($user)) {
    $db_user = $user;
}
if (isset($pass)) {
    $db_pass = $pass;
}
if (isset($db)) {
    $database = $db;
}
if (isset($db_initial)) {
    $database_master = $db_initial;
}

// Ensure defaults if variables are missing
if (!isset($db_host))
    $db_host = 'localhost';
if (!isset($db_user))
    $db_user = 'root';
if (!isset($db_pass))
    $db_pass = '';
if (!isset($database))
    $database = 'sas_';

