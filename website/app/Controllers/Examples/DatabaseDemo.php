<?php

namespace App\Controllers\Examples;

use FastSitePHP\Application;
use FastSitePHP\Data\Database;
use FastSitePHP\Lang\I18N;
use FastSitePHP\Web\Request;

class DatabaseDemo
{
    /**
     * Return HTML for the Web Page
     */
    public function get(Application $app, $lang)
    {
        // Load Language File
        I18N::langFile('database-demo', $lang);

        // Add Code Example from text file
        $file_path = $app->config['I18N_DIR'] . '/code/database-demo.{lang}.txt';
        $app->locals['i18n']['db_code'] = I18N::textFile($file_path, $app->lang);

        // Add a record for the request and get the 20 most recent records
        $records = $this->insertAndSelectRecords($app);

        // Render the View
        $templates = [
            'old-browser-warning.htm',
            'examples/database-demo.php',
            'examples/database-demo.htm',
            'table-highlighter.htm',
        ];
        return $app->render($templates, [
            'nav_active_link' => 'examples',
            'records' => $records,
            'controller_code' => file_get_contents(__FILE__),
        ]);
    }

    /**
     * JSON Service that logs requests from button clicks.
     * Returns the 20 most recent records.
     */
    public function routePage(Application $app, $lang, $color)
    {
        return ['records' => $this->insertAndSelectRecords($app)];
    }

    /**
     * Add a record for the request and return recent records
     */
    private function insertAndSelectRecords(Application $app)
    {
        $db = $this->connectToDb();
        $this->insertRecord($app, $db);
        $records = $this->getRecentRecords($db);
        return $records;
    }

    /**
     * Return a Db Connection to a SQLite Database in the Temp
     * Directory. Create the file if it doesn't yet exist.
     */
    private function connectToDb()
    {
        // Path to SQLite Db
        $path = sys_get_temp_dir() . '/database-demo.sqlite';
        $this->checkDb($path);

        // Connect and create table the first time the db is used
        $dsn = 'sqlite:' . $path;
        $db = new Database($dsn);
        $sql = <<<'SQL'
            CREATE TABLE IF NOT EXISTS requests (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    url TEXT,
                    method TEXT,
                    user_agent TEXT,
                    date_requested TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
SQL;
        $db->execute($sql);
        return $db;
    }

    /**
     * Delete the SQLite Database every time it reaches over 10 megabytes.
     * It is intended only as a temporary database for this demo page.
     */
    private function checkDb($path) {
        if (is_file($path)) {
            $ten_megabytes = (1024 * 1024 * 10);
            $file_size = filesize($path);
            if ($file_size > $ten_megabytes) {
                unlink($path);
                // Add to a log file each time it's removed
                $log_path = sys_get_temp_dir() . '/database-demo.txt';
                $now = date(DATE_RFC2822);
                $contents = "${path} deleted at ${now}\n";
                file_put_contents($log_path, $contents, FILE_APPEND);
            }
        }
    }

    /**
     * Insert a Record for each Request
     */
    private function insertRecord(Application $app, Database $db)
    {
        $req = new Request();
        $sql = 'INSERT INTO requests (url, method, user_agent) VALUES (?, ?, ?)';
        $params = [$app->requestedPath(), $req->method(), $req->userAgent()];
        $db->execute($sql, $params);
    }

    /**
     * Return the 20 most recent records.
     * The SELECT statement is defined in a [*.sql] file,
     * which allows for easy editing and testing outside of PHP code.
     */
    private function getRecentRecords(Database $db)
    {
        $sql = file_get_contents(__DIR__ . '/../../Models/DatabaseDemo.sql');
        $records = $db->query($sql);
        return $records;
    }
}