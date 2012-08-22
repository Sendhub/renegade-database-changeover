<?php

/**
 * @author Jay Taylor [@jtaylor]
 * @date 2012-08-14
 *
 * @description Very simple logging system.
 *
 * Required request parameters:
 *      @param host The hostname or identifier under which the statement will be stored
 *      @param statement The SQL statement to store
 *      @param timestamp The timestamp this statement was executed, used to determine statement order
 */

require_once dirname(__file__) . '/config.inc.php';

if (!array_key_exists('timestamp', $_REQUEST) || !array_key_exists('statement', $_REQUEST) || !array_key_exists('host', $_REQUEST)) {
    die('missing one of the following parameters: timestamp, statement, or host');
}


$timestamp = preg_replace('/^\.\+/', '', preg_replace('/[^a-zA-Z0-9 _\.-]/', '', $_REQUEST['timestamp']));
$statement = urldecode($_REQUEST['statement']);
$host = preg_replace('/^\.\+/', '', preg_replace('/[^a-zA-Z0-9 _\.-]/', '', $_REQUEST['host']));

if (strlen($timestamp) == 0 || strlen($statement) == 0 || strlen($host) == 0) {
    die('the following parameters must not be empty after parse, but one or more were: timestamp, statement, or host');
}

$storagePath = BASE_PATH . $host;

if (!file_exists($storagePath)) {
    mkdir($storagePath, 0755, true);
}

$filename = $storagePath . '/' . str_replace(' ', '_', $timestamp) . '-' . microtime(true) . '-' . $_SERVER['REMOTE_ADDR'] . '-' . md5($statement);


$fh = fopen($filename, 'w');
if ($fh) {
    fwrite($fh, $statement);
    fclose($fh);
}

echo 'succeeded';

