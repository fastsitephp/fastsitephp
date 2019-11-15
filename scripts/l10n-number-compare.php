<!DOCTYPE html>

<?php
    // See notes in [l10n-date-time-compare.php]. This file is similar but checking
    // [Lang\L10N->formatNumber()] against [Intl.NumberFormat]

    // Autoloader and Setup App
    require __DIR__ . '/../autoload.php';
    $app = new \FastSitePHP\Application();
    $app->setup('UTC');
    $app->show_detailed_errors = true;
    set_time_limit(0);

    $l10n = new \FastSitePHP\Lang\L10N();
    $all_langs = $l10n->supportedLocales();
    
    $number = 1234567890.12345;
?>

<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
        <style>
            body { margin:100px; }
            table { border-collapse:collapse; display:inline-block; margin:20px; }
            table caption { font-size:1.5em; font-weight:bold; line-height:1.5em; margin-bottom:.5em; }
            td, th { border:1px solid #000; padding:5px 10px; white-space:nowrap; }
        </style>
    </head>
    <body>
        <div style="font-size:1.5em; margin-left:50px;"><strong>Browser Language:</strong>  <span id="language"></span></div>

        <table id="date-time-table">
            <caption>Number Formatting</caption>
            <thead>
                <tr>
                    <th>Language</th>
                    <th>PHP</th>
                    <th>JS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_langs as $lang) { ?>
                <tr>
                    <td><?php echo $lang ?></td>
                    <td><?php echo $l10n->locale($lang)->formatNumber($number, 5) ?></td>
                    <td></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <script>
            function formatNumber(number, lang) {
                if (Intl.DateTimeFormat.supportedLocalesOf([lang], {localMatcher:'lookup'}).length === 0) {
                    return '';
                }

                const decimalPlaces = 5;
                const options = { 
                    minimumFractionDigits:decimalPlaces, 
                    maximumFractionDigits:decimalPlaces,
                };
                return new Intl.NumberFormat(lang, options).format(number);
            }

            const number = 1234567890.12345;
            const table = document.getElementById("date-time-table");
            const rows = table.tBodies[0].rows;
            for (let n = 0, m = rows.length; n < m; n++) {
                const lang = rows[n].cells[0].textContent;
                rows[n].cells[2].textContent = formatNumber(number, lang);
                if (rows[n].cells[2].textContent !== rows[n].cells[1].textContent) {
                    rows[n].cells[2].style.backgroundColor = '#ffc8c8';
                }
            }

            document.getElementById("language").innerHTML = navigator.language;
        </script>
    </body>
</html>
