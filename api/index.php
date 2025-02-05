<?php

/**
 * This is the entry point for the APIs
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

#define('DB_SERVER', 'http://10.8.18.1');
#define('DB_PORT', '5984');
define('DB_SERVER', 'http://admin:YkTaZK5S@10.76.254.3');
define('DB_PORT', '15984');
define('DB_PREFIX', 'zz_');





// PGSQL
/*

try {
	$dsn = "pgsql:host=$host;port=5432;dbname=$db;";
	// make a database connection
	$pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

	if ($pdo) {
		$lang='eng';
	}
} catch (PDOException $e) {
	die($e->getMessage());
} finally {
	if ($pdo) {
		$pdo = null;
	}
}

*/



// CORS
header('Access-Control-Allow-Headers:Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, X-Auth-Token');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Max-Age:86400');

require_once 'wrapper/bigcouch.php';
require_once 'utils_validator.php';
require_once 'lib/restler/restler.php';
require_once 'lib/KLogger.php';
use Luracast\Restler\Restler;

$r = new Restler();
$r->setSupportedFormats('JsonFormat', 'UploadFormat');
$r->addAPIClass('phones');
$r->addAPIClass('providers');
$r->addAPIClass('accounts');
$r->addAuthenticationClass('AccessControl');
$r->handle();

?>
