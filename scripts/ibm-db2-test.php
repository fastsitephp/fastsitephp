<?php

// This is a manual test script for connecting to an IBM Database with the
// class [Db2Database]. This script is intended to be run directly on an IBM
// Server such as AIX (AS/400) that contains the database.

// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 'on');

// Installation
// If running from AIX and developing on Windows files are often copied through
// command line FTP. This can make copying or updating many files difficult
// so this script assumes this and needed database files are in the same directory.

// #------------------------------
// # Copy file to AS/400
// #------------------------------
// cd <FILES_DIR>
// ftp <IP_OR_SERVER_NAME> 
// cd <DIR_FOR_PHP_FILES>
// put DatabaseInterface.php
// put AbstractDatabase.php
// put AbstractVendorDatabase.php
// put Db2Database.php
// put ibm-db2-test.php

// #------------------------------
// # Run Script on the AS/400
// #------------------------------
// AZ   # Or the command for your system
// call qp2term
// cd /usr/local/zendphp7/bin
// php-cli <DIR_FOR_PHP_FILES>/ibm-db2-test.php

// Running on IBM i-Series?
if (PHP_OS !== 'AIX') {
    echo 'WARNING - This script is intended for IBM AIX. If you want to run it from another computer. Then you will need to modify this file.';
    echo "\n";
    exit();
}

// Load Required Files, modify this if needed based on how you copied the files
require_once 'DatabaseInterface.php';
require_once 'AbstractDatabase.php';
require_once 'AbstractVendorDatabase.php';
require_once 'Db2Database.php';

// Status
echo 'Connecting to Database';
echo "\n";

// Connect to the Database
// NOTE - If running directly on the server you can use the server's credentials
// and pass [null] for [DSN, User, and Password]. In fact this is recommended 
// as you can get better performance. If empty strings '' are used as shown in the 
// PHP docs online it can cause extra print spool jobs to run so use [null] instead.  
$dsn = null;
$user = null;
$password = null;
$persistent = false;
$db = new \FastSitePHP\Data\Db2Database($dsn, $user, $password, $persistent);

// Trim Strings (DB2 and AS/400) will often use CHAR columns
$db->trimStrings(true);

// Query System Tables
$sql = <<<SQL
    SELECT SYSTEM_TABLE_SCHEMA, TABLE_NAME
    FROM QSYS2.SYSTABLES 
    WHERE SYSTEM_TABLE_SCHEMA = ?
    ORDER BY SYSTEM_TABLE_SCHEMA, TABLE_NAME
    FETCH FIRST 4 ROWS ONLY
SQL;
$sql = trim($sql);
$params = ['QSYS'];

// NOTE - on Tested IBM Systems Named Parameters didn't work when using
// PHP 7.1 with latest Zend Server (late 2018).
// $params = [':schema' => 'QSYS'];

echo 'query(): ';
echo json_encode($db->query($sql, $params));
echo "\n";

echo 'queryOne(): ';
echo json_encode($db->queryOne($sql, $params));
echo "\n";

echo 'queryValue(): ';
echo json_encode($db->queryValue($sql, $params));
echo "\n";

echo 'queryList(): ';
echo json_encode($db->queryList($sql, $params));
echo "\n";

// To test record inserts, modify the below INSERT queries
// or copy and modify this CREATE TABLE query with a 'LIBRARY' (LIB)
// that the user has access too; then also update the INSERT queries.
/*
CREATE TABLE LIB.QTEMP (
	ID FOR UNIQUE DECIMAL(7, 0)
		GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1, NO CYCLE),
	DATA CHAR(100)
)
*/
/*

// Add Records
$sql = "INSERT INTO LIB.QTEMP (DATA) VALUES ('Test')";
echo 'execute(): ';
echo $db->execute($sql);
echo "\n";

$sql = "INSERT INTO LIB.QTEMP (DATA) VALUES (?)";
$records = [['Test2'], ['Test3']];
echo 'executeMany(): ';
echo $db->executeMany($sql, $records);
echo "\n";

*/