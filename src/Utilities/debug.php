<?php
/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (https://conradsollitt.com)
 * @license  MIT License
 */

// Use for checking script time and memory. This can get called from [index.php].
// Results are handled at the end of this file and displayed on the bottom of the page.
$start_time = microtime(true);
$starting_memory = memory_get_usage(false);

// Call this at the end of the page, call by entering the line [$showDebugInfo();]
$showDebugInfo = function ($show_as_text = false) use ($start_time, $starting_memory) {
    // Is the response is json then return and do not show the html debug info
    if (in_array('Content-type: application/json', headers_list())) {
        return;
    }

    // Calculate the script time in micro-seconds and memory in bytes
    $script_time = microtime(true) - $start_time;
    $ending_memory = memory_get_usage(false);
    $peak_memory = memory_get_peak_usage(false);

    // HTML or Plain Text format?
    $show_as_text |= in_array('Content-Type: text/plain', headers_list());
    $show_as_text |= (php_sapi_name() === 'cli');

    // Output HTML showing time and memory statistics
    // NOTE - [data-memory-bytes] can be read by JavaScript to get raw byte count
    if ($show_as_text) {
        echo "\n";
        echo str_repeat('-', 80);
        echo "\n";
        echo 'Script Time:' . $script_time . "\n";
        echo 'Starting Memory Usage: ' .  ($starting_memory / 1024) . ' KB' . "\n";
        echo 'Ending Memory Usage: ' .  ($ending_memory / 1024) . ' KB' . "\n";
        echo 'Peak Memory Usage: ' .  ($peak_memory / 1024) . ' KB' . "\n";
    } else {
        echo '<br/><br/><div class="FastSitePHP-debug-info" style="text-align:center;">';
        echo '<div style="margin:10px auto 40px auto; text-align:left; display:inline-block; box-shadow:0 1px 2px 0 rgba(0,0,0,.5); border-radius:5px; padding:10px; background-color:#fff;">';
        echo '<strong>Script Time:</strong> <span id="FastSitePHP-script-time">', $script_time, '</span>';
        echo '<br/><strong>Starting Memory Usage:</strong> <span id="FastSitePHP-starting-memory-usage" data-memory-bytes="', $starting_memory, '">', ($starting_memory / 1024), ' KB</span>';
        echo '<br/><strong>Ending Memory Usage:</strong> <span id="FastSitePHP-ending-memory-usage" data-memory-bytes="', $ending_memory, '">', ($ending_memory / 1024), ' KB</span>';
        echo '<br/><strong>Peak Memory Usage:</strong> <span id="FastSitePHP-peak-memory-usage" data-memory-bytes="', $peak_memory, '">', ($peak_memory / 1024), ' KB</span>';
    }

    // microtime() calculates on microseconds (one millionth of a second) however accuracy is based
    // on the computer's hardware is zero is returned it doesn't necessarily mean that the page
    // was generated in under a millionth of a second. When it happens display a message.
    if (stripos((string)$script_time, '.') === false) {
        echo  '<br/><br/>The script time to generate this page was less than one thousandth of a second';
    }
    if ($show_as_text) {
        echo "\n";
    } else {
        echo '</div></div>';
    }
};
