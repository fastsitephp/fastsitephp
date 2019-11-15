<?php
/**
 * Copyright 2019 Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP\Lang;

/**
 * Helper class to display time text strings.
 */
class Time
{
    /**
     * Convert the number of seconds into an English text string.
     * 
     * Example:
     *     Time::secondsToText(129680) = 1 Day, 12 Hours, 1 Minute, and 20 Seconds
     *     Time::secondsToText(120)    = 2 Minutes
     *     Time::secondsToText(119)    = 1 Minute and 59 Seconds
     *     Time::secondsToText(10)     = 10 Seconds
     * 
     * For a basic numeric format of 'HH:MM:SS' you can instead use a PHP built-in
     * function as long as the time is less than 24 hours:
     *     date('H:i:s', $seconds)
     * 
     * @param int $seconds
     * @return string
     */
    public static function secondsToText($seconds)
    {
        // Using Calendar Year (365 days) and not Astronomical Year (365.25 days)
        $years = (int)floor($seconds / 31536000);   // 31536000 = 60 * 60 * 24 * 365
        $days = (int)floor($seconds / 86400 % 365); //    86400 = 60 * 60 * 24
        $hours = (int)floor($seconds / 3600 % 24);  //     3600 = 60 * 60
        $minutes = (int)floor($seconds / 60 % 60);
        $seconds = (int)($seconds % 60);
        return self::english($years, $days, $hours, $minutes, $seconds);
    }

    /**
     * Language 'en'
     */
    private static function english($years, $days, $hours, $minutes, $seconds)
    {
        $list = array();
        if ($years !== 0) {
            $list[] = sprintf('%d Year%s', $years, ($years === 1 ? '' : 's'));
        }
        if ($days !== 0) {
            $list[] = sprintf('%d Day%s', $days, ($days === 1 ? '' : 's'));
        }
        if ($hours !== 0) {
            $list[] = sprintf('%d Hour%s', $hours, ($hours === 1 ? '' : 's'));
        }
        if ($minutes !== 0) {
            $list[] = sprintf('%d Minute%s', $minutes, ($minutes === 1 ? '' : 's'));
        }
        if (count($list) === 0 || $seconds !== 0) {
            $list[] = sprintf('%d Second%s', $seconds, ($seconds === 1 ? '' : 's'));
        }

        $count = count($list);
        if ($count === 1) {
            return $list[0];
        } elseif ($count == 2) {
            return $list[0] . ' and ' . $list[1];
        } else {
            $result = implode(', ', $list);
            $pos = strrpos($result, ', ');
            return substr_replace($result, ', and ', $pos, 2);
        }
    }
}
