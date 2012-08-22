<?php

/**
 * @author Jay Taylor [@jtaylor]
 * @date 2012-08-14
 */

error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Location to store received SQL statements.
define('BASE_PATH', '/mnt/dbupgrade.sendhub.com/upgradeData');

// Whether or not to connect to postgres using SSL.
define('PG_USE_SSL', TRUE);

