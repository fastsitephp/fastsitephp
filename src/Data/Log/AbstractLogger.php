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

use Psr\Log\AbstractLogger As PsrLogger;

/**
 * This is a PHP Abstract Class which is used as a base class
 * to provide shared functions for multiple Logger classes.
 * 
 * This class depends on the widely used PSR Logger Interface.
 * 
 * @link https://www.php-fig.org/psr/psr-3/
 * @link https://github.com/php-fig/log
 */
abstract class AbstractLogger extends PsrLogger
{
    /**
     * Format to use when converting dates to a string
     * 
     * @link http://php.net/manual/en/class.datetimeinterface.php#datetime.constants.types
     * @var string
     */
    public $date_format = \DateTime::ISO8601;

    /**
     * Format variables into the message using '{placeholders}' with the variable name.
     * 
     * @param string $message
     * @param array|null $context
     * @return string
     */
    protected function format($message, array $context = array())
    {
        if (\strpos($message, '{') !== false) {
            $replace = array();
            foreach ($context as $key => $val) {
                if ($val === null || \is_bool($val)) {
                    $replace['{' . $key . '}'] = \json_encode($val);                
                } elseif (\is_scalar($val) || (\is_object($val) && \method_exists($val, '__toString'))) {
                    $replace['{' . $key . '}'] = $val;
                } elseif ($val instanceof \DateTimeInterface) {
                    $replace['{' . $key . '}'] = $val->format($this->date_format);
                } elseif (\is_object($val)) {
                    $replace['{' . $key . '}'] = '[Class=' . \get_class($val) . ']';
                } else {
                    $replace['{' . $key . '}'] = '[Data Type=' . \gettype($val) . ']';
                }
            }
            $message = \strtr($message, $replace);
        }
        return $message;
    }
}