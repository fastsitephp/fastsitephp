<?php
/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP\Data\Log;

/**
 * Simple File Logger that uses the [Psr\Log\LoggerInterface]
 */
class FileLogger extends AbstractLogger
{
    /**
     * Path of the log file. The file will be created on first used if it does not exist.
     * @var string
     */
    public $file_path = null;

    /**
     * Default Log file format
     * @var string
     */
    public $log_format = '{date} {level} - {message}{line_break}';

    /**
     * Line breaks default to the line breaks for the OS:
     *     "\r\n" - Windows
     *     "\n"   - Other OS's
     * @var string
     */
    public $line_break = PHP_EOL;

    /**
     * Class Constructor. The file path to write to is
     * set when this the object is created.
     *
     * @param string $file_path
     */
    public function __construct($file_path)
    {
        $this->file_path = $file_path;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $message = $this->format($message, $context);
        $message = $this->format($this->log_format, array(
            'date' => date($this->date_format),
            'level' => strtoupper($level),
            'message' => $message,
            'line_break' => $this->line_break,
        ));
        file_put_contents($this->file_path, $message, FILE_APPEND);
    }
}
