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

use FastSitePHP\Application;

/**
 * HTML Logger that uses the [Psr\Log\LoggerInterface]. 
 * 
 * This class can be used for temporary development logs because it outputs an 
 * HTML table of logged messages after the response is sent or depending on 
 * options can be used to replace the original response.
 */
class HtmlLogger extends AbstractLogger
{
    private $logs = array();

    /**
     * File path for the PHP Template used to show the logs
     * @var string
     */
    public $temlate_file = __DIR__ . '/../../Templates/html-template.php';

    /**
     * Class Constructor
     * 
     * The FastSitePHP Application must be passed when this class is created.
     * Once called it adds either a [beforeSend()] or [after()] event based
     * on the optional parameter [$replace_response].
     * 
     * @param Application $app
     * @param null|bool $replace_response
     */
    public function __construct(Application $app, $replace_response = false)
    {
        $logger = $this;        
        if ($replace_response) {
            $app->beforeSend(function() use ($logger) {
                return $logger->getHtml();
            });
        } else {
            $app->after(function() use ($logger) {
                echo $logger->getHtml();
            });
        }
    }

    /**
     * Return HTML that will be used to show the logged messages. 
     * This function gets called to replace the current route or 
     * after the response is sent.
     */
    public function getHtml()
    {
        // Start new output buffering
        ob_start();

        // Extract logs to local scope for the template
        $logs = $this->logs;

        // Process template and return results
        include $this->temlate_file;
        return ob_get_clean();
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
        $this->logs[] = array(
            date($this->date_format),
            strtoupper($level),
            $this->format($message, $context),
        );
    }
}