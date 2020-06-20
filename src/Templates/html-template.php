<?php

/**
 * @var array<int, string> $logs
 */

?>
<style>
    .section-log { background-color:white; padding:50px; margin-top:50px; text-align:center; }
    .section-log table { margin:auto; text-align:left; border-collapse:collapse; }
    .section-log thead { background-color:#7be5db; }
    .section-log th,
    .section-log td { padding:8px 10px; border:1px solid black; vertical-align:top; }
    .section-log tbody tr:nth-child(even) { background-color:hsla(174, 67%, 90%, 1); }
    .log-level-error { background-color:red; }
</style>
<div class="section-log">
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Level</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="log-time"><?php echo htmlspecialchars($log[0], ENT_QUOTES, 'UTF-8', true) ?></td>
                    <td class="log-level-<?php echo htmlspecialchars(strtolower($log[1]), ENT_QUOTES, 'UTF-8', true) ?>">
                        <?php echo htmlspecialchars($log[1], ENT_QUOTES, 'UTF-8', true) ?>
                    </td>
                    <td><?php echo htmlspecialchars($log[2], ENT_QUOTES, 'UTF-8', true) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<script>
    // The time that comes from PHP will likely be UTC time with a format such as "2018-12-13T01:49:51+0000"
    // Convert it to a local time (example "12/12/2018, 5:49:51 PM" for US PST)
    document.addEventListener('DOMContentLoaded', function () {
        var elements = document.querySelectorAll('.log-time');
        Array.prototype.forEach.call(elements, function(el) {
            var d = new Date(el.textContent);
            if (!isNaN(d.getTime())) {
                var time = (typeof d.toLocaleString === 'function' ? d.toLocaleString() : d.toString());
                el.textContent = time;
            }
        });
    });
</script>