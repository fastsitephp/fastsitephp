<?php
// Test Script for manually testing the Database class.
// In the future this code can be used as a starting point 
// when creating the full unit tests. In the meantime this 
// manually helps confirm the class works as expected.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

header('Content-Type: text/plain');

// NOTE - sqlite doesn't support [querySets] while mysql does
// If mysql is used and errors in the middle of this script
// then it has to be manually deleted.
// $con = 'mysql:host=localhost;dbname=test;charset=utf8';
$con = 'sqlite::memory:';

$db = new \FastSitePHP\Data\Database($con);
$sql = <<<SQL
    CREATE TABLE test (id INT PRIMARY KEY, data TEXT);
    INSERT INTO test (id, data) VALUES (1, 'test');
    INSERT INTO test (id, data) VALUES (2, 'test');
    INSERT INTO test (id, data) VALUES (3, 'test');
SQL;
echo '# Creating [test] Table';
echo "\n";
echo '# Running [execute()] - no params';
echo "\n";
var_dump($db->execute($sql));
echo "\n";
echo "\n";

echo '# Running [execute()] - with params';
echo "\n";
$sql = 'INSERT INTO test (id, data) VALUES (?, ?)';
print_r($db->execute($sql, array(4, 'test')));
echo "\n";
echo "\n";

echo '# Running [executeMany()]';
echo "\n";
$sql = 'INSERT INTO test (id, data) VALUES (?, ?)';
$records = array(
    array(5, 'record'),
    array(6, 'record'),
    array(7, 'record'),
);
print_r($db->executeMany($sql, $records));
echo "\n";
echo "\n";

echo '# Running [query()] - no params';
echo "\n";
$sql = 'SELECT * FROM test';
print_r($db->query($sql));
echo "\n";
echo "\n";

echo '# Running [queryOne()] - no params';
echo "\n";
print_r($db->queryOne($sql));
echo "\n";
echo "\n";

echo '# Running [query()] - with params';
echo "\n";
$sql = 'SELECT * FROM test WHERE data = ?';
var_export($db->query($sql, array('test')));
echo "\n";
echo "\n";

echo '# Running [queryOne()] - with params';
echo "\n";
print_r($db->queryOne($sql, array('test')));
echo "\n";
echo "\n";

echo '# Running [query()] - no records returned - no params';
echo "\n";
$sql = 'SELECT * FROM test WHERE id = 10';
print_r($db->query($sql));
echo "\n";
echo "\n";

echo '# Running [query()] - no records returned - with params';
echo "\n";
$sql = 'SELECT * FROM test WHERE id = ?';
var_export($db->query($sql, array(10)));
echo "\n";
echo "\n";

echo '# Running [queryOne()] - no records returned - no params';
echo "\n";
$sql = 'SELECT * FROM test WHERE id = 10';
var_dump($db->queryOne($sql));
echo "\n";
echo "\n";

echo '# Running [queryOne()] - no records returned - with params';
echo "\n";
$sql = 'SELECT * FROM test WHERE id = ?';
var_dump($db->queryOne($sql, array(10)));
echo "\n";
echo "\n";

if ($con !== 'sqlite::memory:') {
    echo '# Running [querySets()] - no params';
    echo "\n";
    $sql = <<<SQL
        SELECT * FROM test;
        SELECT * FROM test WHERE id = 0;
        SELECT * FROM test WHERE id = 1;
SQL;
    print_r($db->querySets($sql));
    echo "\n";
    echo "\n";

    echo '# Running [querySets()] - with params';
    echo "\n";
    $sql = <<<SQL
        SELECT * FROM test WHERE id = ?;
        SELECT * FROM test WHERE id = ?;
        SELECT * FROM test WHERE id = ?;
SQL;
    print_r($db->querySets($sql, array(2, 0, 3)));
    echo "\n";
    echo "\n";
}

echo '# Drop Table [test] using [execute()]';
echo "\n";
$sql = 'DROP TABLE test';
var_dump($db->execute($sql));
echo "\n";
echo "\n";

