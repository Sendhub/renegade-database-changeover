<?php

/**
 * @author Jay Taylor [@jtaylor]
 * @date 2012-08-14
 *
 * @description Very simple query replay system.
 */

ini_set('max_execution_time', 0); // A server timeout during replay would be undesirable.

require_once dirname(__file__) . '/config.inc.php';

function isFormSubmitted() {
    $out = (array_key_exists('since', $_REQUEST) && is_numeric($_REQUEST['since']) > 0);
    return $out;
}

?><html>
<head>
<title>PG Replay Tool</title>
<link rel="stylesheet" href="/style.css" type="text/css" />
</head>
<body><?php

if (isFormSubmitted() !== true) { ?>
<form>
<fieldset>
<legend>DB Replay</legend>
<ul>
<li>
<label for="sourceHost">Source Host</label>
<select name="sourceHost" id="sourceHost">
<option value=""></option>
<?php
    $dh = opendir(BASE_PATH);
    while (($entry = readdir($dh)) !== false) {
        if (is_dir(BASE_PATH . '/' . $entry) && substr($entry, 0, 1) != '.') {
            ?><option value="<?php echo $entry; ?>"<?php echo array_key_exists('sourceHost', $_REQUEST) && $_REQUEST['sourceHost'] == $entry ? ' selected="selected"' : ''; ?>><?php echo $entry; ?></option>
<?php
        }
    }
?>
</select></li>

<li><label for="since">Since Unix Timestamp</label><input type="text" name="since" id="since"<?php echo array_key_exists('since', $_REQUEST) ? ' value="' . $_REQUEST['since'] . '"' : ''; ?> /> (current time is <?php echo time(); ?>)</li>

<li><label for="host">Destination Host</label><input type="text" name="host" id="host"<?php echo array_key_exists('host', $_REQUEST) ? ' value="' . $_REQUEST['host'] . '"' : ''; ?> /></li>
<li><label for="database">Database</label><input type="text" name="database" id="database" value="<?php echo array_key_exists('database', $_REQUEST) ? $_REQUEST['database'] : ''; ?>" /></li>
<li><label for="port">Port</label><input type="text" name="port" id="port"<?php echo array_key_exists('port', $_REQUEST) ? ' value="' . $_REQUEST['port'] . '"' : ''; ?> /></li>
<li><label for="user">User</label><input type="text" name="user" id="user"<?php echo array_key_exists('user', $_REQUEST) ? ' value="' . $_REQUEST['user'] . '"' : ''; ?> /></li>
<li><label for="password">Password</label><input type="text" name="password" id="password"<?php echo array_key_exists('password', $_REQUEST) ? ' value="' . $_REQUEST['password'] . '"' : ''; ?> /></li>

<input type="submit" />
</fieldset>
</form>
<?php } else {
    $since = (double)$_REQUEST['since'];
    $sourceHost = $_REQUEST['sourceHost'];
    $host = $_REQUEST['host'];
    $database = $_REQUEST['database'];
    $port = (int)$_REQUEST['port'];
    $user = $_REQUEST['user'];
    $password = $_REQUEST['password'];

    $files = scandir(BASE_PATH . '/' . $sourceHost);

    // Filter out old records.
    foreach ($files as $i => $fileName) {
        if (preg_match('/^[0-9\.]\+-.*$/', $fileName) || (double)preg_replace('/^([0-9\.]\+)-.*$/', '\1', $fileName) < $since) {
            unset($files[$i]);
        }
    }

    function jlog($msg, $newline=true) {
        echo $msg;
        if ($newline === true) {
            echo "<br />\n";
        }
        flush();
    }

    jlog('Connecting to db..', false);

    $res = pg_pconnect("dbname=$database host=$host user=$user password=$password port=$port" . (PG_USE_SSL ? ' sslmode=require' : ''));

    jlog('Connected<br />');

    $numFiles = count($files);

    $i = 1;
    foreach ($files as $fileName) {
        $stmt = file_get_contents(BASE_PATH . '/' . $sourceHost . '/' . $fileName);
        pg_query($res, $stmt);
        jlog($i++ . ' of ' . $numFiles);
    }

    pg_close($res);

    echo 'succeeded';
}
?>
</body>
</html>
