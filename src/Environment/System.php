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

namespace FastSitePHP\Environment;

/**
 * System and OS Info
 */
class System
{
    /**
     * Returns an array of basic information related to the Operating System.
     * This typically includes human readable information such as the OS Version.
     * On some older UNIX platforms this function may instead return the OS that
     * PHP was built on but this is not expected on Windows, Linux, or Mac.
     *
     * For detailed system information see the function [systemInfo()] in this class.
     * See also [\FastSitePHP\Net\Config->fqdn()]
     *
     * Keys in the Returned Array:
     *     [ 'OS Type', 'Version Info', 'Release Version', 'Host Name', 'CPU Type' ]
     *
     * @return array
     */
    public function osVersionInfo()
    {
        // This function returns all info from [php_uname()] which will often
        // vary from what the OS may return however it is still accurate and
        // returns info in format that is easy to read by an administrator or
        // developer.
        //
        // For example on a tested version of Windows 10 this returned:
        //   {"Version Info": "build 14393 (Windows 10)"}
        // While the Windows Registry shows the following:
        //   HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows NT\CurrentVersion
        //     ProductName  = 'Windows 10 Pro'
        //     CurrentBuild = 14393
        return array(
            'OS Type' => php_uname('s'),
            'Version Info' => php_uname('v'),
            'Release Version' => php_uname('r'),
            'Host Name' => php_uname('n'),
            'CPU Type' => php_uname('m'),
        );
    }

    /**
     * Return a string of System Info from the OS. If System info can't be
     * determined then null will be returned. This function works with
     * various OS's including Windows, Mac, and recent versions of Linux.
     * It runs the following commands:
     *
     *     Linux:   cat /etc/os-release
     *     Windows: ver
     *     Mac:     system_profiler SPHardwareDataType SPSoftwareDataType
     *     FreeBSD: uname -mrs
     *     IBM AIX: uname -a
     *
     * If running on Linux and the file [/etc/os-release] doesn't exist
     * system info can possibly be obtained from one of the following commands:
     *
     *     lsb_release -a
     *     cat /etc/*-release
     *     cat /etc/*_version
     *
     * On Windows detailed info may be obtained by using the command [systeminfo] instead
     * however calling [systeminfo] often takes 10 - 30 seconds to run.
     *
     * FreeBSD also supports the command [freebsd-version] which will likely include
     * the same info as this function.
     *
     * For IBM iSeries (AS/400) Newer versions of the OS inlude commands
     * [lscfg, oslevel, prtconf] however often the OS is not updated so
     * this function simply returns [uname -a].
     *
     * @link https://www.linux.org/docs/man5/os-release.html
     * @link https://docs.microsoft.com/en-us/windows-server/administration/windows-commands/systeminfo
     * @link https://docs.microsoft.com/en-us/windows-server/administration/windows-commands/ver
     * @link https://www.freebsd.org/cgi/man.cgi?query=freebsd-version
     * @link https://www.ibm.com/developerworks/aix/library/au-aix_cmds/index.html
     * @return string|null
     */
    public function systemInfo()
    {
        $cmd = null;
        switch (PHP_OS) {
            case 'WINNT':
                $cmd = 'ver';
                break;
            case 'Darwin': // Mac
                $cmd = 'system_profiler SPSoftwareDataType SPHardwareDataType';
                break;
            case 'FreeBSD':
                $cmd = 'uname -mrs';
                break;
            case 'AIX': // IBM
                $cmd = 'uname -a';
                break;
            default: // Assume Linux
                $os_release = '/etc/os-release';
                if (is_file($os_release)) {
                    return file_get_contents($os_release);
                }
        }
        if ($cmd !== null) {
            return shell_exec($cmd);
        }
        return null;
    }

    /**
     * Return an array of information related to free, used, and total space for a
     * filesystem drive or disk partition. The returned values include the size
     * calculated in Bytes, Megabytes, Gigabytes, and Percent. If this function is
     * called with no parameters then the default drive or disk will be used. To get
     * info for specific drive call this function with the disk partition (Mac, Linux)
     * or Drive Letter (Windows), for example '/dev/disk0' or 'D:'. Internally this
     * function uses PHP functions [disk_free_space()] and [disk_total_space()].
     *
     * Keys in the Returned Array:
     *   [ 'Drive',
     *     'Free Space Bytes', 'Free Space MB', 'Free Space GB', 'Free Space Percent',
     *     'Used Space Bytes', 'Used Space MB', 'Used Space GB', 'Used Space Percent',
     *     'Total Space Bytes', 'Total Space MB', 'Total Space GB' ]
     *
     * @param null|string $drive
     * @return array
     */
    public function diskSpace($drive = null)
    {
        // Get default Drive if this function is called with no parameters.
        // In Windows this will most often be 'C:' however if Windows is installed
        // on another Drive such as 'D:' then that drive will be returned.
        if ($drive === null) {
            $drive = (PHP_OS === 'WINNT' ? getenv('SystemDrive') : '/');
        }

        // Get Free, Total, and Used Disk Space in Bytes.
        // If the disk doesn't exist or is not linked then PHP will return the WARNING:
        //	 disk_free_space(): The system cannot find the path specified.
        // For example if Windows Drive 'D:' is not mapped and this function is called
        // checking Drive 'D:' then a warning will be generated by PHP.
        $free_space = disk_free_space($drive);
        $total_space = disk_total_space($drive);
        $used_space = $total_space - $free_space;

        // Return an array of the storage size categories with Bytes, MB, GB, and %
        return array(
            'Drive' => $drive,
            'Free Space Bytes' => $free_space,
            'Free Space MB' => round($free_space / pow(1024, 2), 2),
            'Free Space GB' => round($free_space / pow(1024, 3), 2),
            'Free Space Percent' => round($free_space / $total_space * 100, 2),
            'Used Space Bytes' => $used_space,
            'Used Space MB' => round($used_space / pow(1024, 2), 2),
            'Used Space GB' => round($used_space / pow(1024, 3), 2),
            'Used Space Percent' => round($used_space / $total_space * 100, 2),
            'Total Space Bytes' => $total_space,
            'Total Space MB' => round($total_space / pow(1024, 2), 2),
            'Total Space GB' => round($total_space / pow(1024, 3), 2),
        );
    }

    /**
     * Windows only function that returns an array of drive letters mapped to the server.
     * For example if the server running PHP has drives C, D, and Z mapped then this function
     * will return array('C:', 'D:', 'Z:'). I this function is called from a Non-Windows
     * computer then an empty array will be returned.
     *
     * @return array
     */
    public function mappedDrives()
    {
        // Only run on Windows
        if (PHP_OS !== 'WINNT') {
            return array();
        }

        // PHP Networking functions use internal OS function calls
        // and will raise E_WARNING errors if they fail. To make sure
        // this code doesn't trigger any errors turn off error reporting
        // for E_WARNING and get the current error level to later reset.
        // E_WARNING can be manually verified by adding the line [echo 1 / 0;].
        $current_error_level = error_reporting(E_ALL & ~E_WARNING);

        // Loop from ASCII values for letters A to Z and check if the drive
        // exists or not. If it does then add it to the array.
        $drives = array();
        for ($letter = ord('A'), $z = ord('Z'); $letter <= $z; $letter++) {
            $drive = chr($letter) . ':';
            if (disk_total_space($drive) !== false) {
                $drives[] = $drive;
            }
        }

        // Reset error reporting back to it's original state
        // and return the Array of Mapped Drives
        error_reporting($current_error_level);
        return $drives;
    }
}
