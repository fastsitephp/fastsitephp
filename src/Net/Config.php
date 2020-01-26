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

namespace FastSitePHP\Net;

/**
 * Network Configuration Info for your Server or Environment
 */
class Config
{
    /**
     * Return a (fqdn) 'fully-qualified domain name' for the server that is 
     * running this script. A fqdn might not be available which will result
     * in null being returned. This function performs a DNS lookup on the
     * hostname of the server.
     * 
     * @return string|null 'server.example.com'
     */
    public function fqdn()
    {
        $host = gethostname();
        $fqdn = null;
        if ($host !== false) {
            $record = dns_get_record($host);
            $fqdn = (isset($record[0]) && isset($record[0]['host']) ? $record[0]['host'] : $host);
        }
        return $fqdn;        
    }

    /**
     * Return the IPv4 Address of the computer or server that is running the script. This can be used
     * if a site is installed in an environment with multiple web servers to determine what server a
     * specific user is accessing. If a computer is not connected to a network and PHP is running from
     * a development environment then this function will likely return the localhost address '127.0.0.1'
     * however if connected to the internet or a corporate network then this function will 
     * return the computer's network IP address. To get the IP Address of the Web Server Software see 
     * the function [FastSitePHP\Web\Request->serverIp()].
     *
     * @return string|null
     */
    public function networkIp()
    {
        // Get list of IPv4 Addresses for the Computer
        $ip_list = $this->networkIpList();
        
        // Check if more than one IP Address was returned. If so
        // then prioritize the main ethernet interface/adapter.
        $ip_count = count($ip_list);
        if ($ip_count > 1) {
            // Run one of the following commands [ip addr], [ifconfig], 
            // or [ipconfig] and parse the results to an object. 
            $net_info = $this->parseNetworkInfo($this->networkInfo());
            
            // Code changes can be manually tested by
            // reading from saved text files, example:
            // $net_info = $this->parseNetworkInfo(file_get_contents('{{path}}'));

            if ($net_info !== null) {
                if (property_exists($net_info, 'interfaces')) {
                    // Linux/Unix
                    foreach ($net_info->interfaces as $interface) {
                        if (($interface->type === 'eth0' || $interface->type === 'en0') &&
                            is_string($interface->inet) && 
                            filter_var($interface->inet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                        ) {
                            return $interface->inet;
                        }
                    }
                } else {
                    // Windows
                    foreach ($net_info->adapters as $adapter) {
                        // Make sure that Oracle VirtualBox Host IP's are skipped 
                        // and removed from the main array.
                        if (strpos($adapter->name, 'VirtualBox Host-Only Network') !== false) {
                            // array_values() is used to re-index the array because if 
                            // the removed item is the first item in the array then
                            // $ip_list[0] would no longer be available.
                            if (array_key_exists('IPv4 Address', $adapter->properties)) {
                                $ip_list = array_values(array_diff($ip_list, array($adapter->properties['IPv4 Address'])));
                            }
                            continue;
                        }

                        // If there is an Ethernet Adapter defined then use the IP from it
                        if (stripos($adapter->name, 'Ethernet adapter Ethernet') !== false &&
                            array_key_exists('IPv4 Address', $adapter->properties) &&
                            filter_var($adapter->properties['IPv4 Address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                        ) {
                            return $adapter->properties['IPv4 Address'];
                        }
                    }
                }
            }
        }

        // Return the first IP Address found
        return ($ip_count > 0 ? $ip_list[0] : null);
    }

    /**
     * Return any array of IPv4 Address of the computer or server that is running the script.
     * Often a server will have more than 1 IP assigned.
     * 
     * @return array
     */
    public function networkIpList()
    {
        // First get the host name of the computer then
        // resovle the host name to get all asigned IP Addresses.
        $host = gethostname();
        $ip_list = array();
        if ($host !== false) {
            $ip_list = gethostbynamel($host);
        }

        // If either gethost*() function returned false then there
        // was an error so return an empty array or the IP list.
        return ($ip_list === false ? array() : $ip_list);        
    }

    /**
     * Return a string of Network Info from the OS. If Network info can't be 
     * determined then null will be returned. This function works with
     * various OS's including Windows, Mac, and recent versions of Linux.
     * It runs the following commands: 
     * 
     *     Win:   ipconfig 
     *            ipconfig /all    (If the optional [$all] parameter is true)
     *     Mac:   ifconfig
     *     Other: ip addr
     *            ifconfig
     * 
     * For Linux newer versions will typically include the [ip] command.
     * For older versions of Linux or Unix OS's [ifconfig] will usually
     * be used.
     * 
     * @param bool $all (Optional)
     * @return null
     * @throws \Exception
     */
    public function networkInfo($all = false)
    {
        // Check to make sure that [shell_exec()] has not been disabled and if it
        // has provide a helpful message so that it's easy for the developer to solve.
        // If you see this error then [shell_exec()] is likely disabled in the [php.ini]
        // file so see the link: https://php.net/disable-functions
        // For info on editing [php.ini] refer to file [test-app.php] route '/check-server-config'
        // NOTE - this error is not Unit Tested but must be manually tested by disabling [shell_exec()].
        if (!function_exists('shell_exec')) {
            throw new \Exception(sprintf('[%s->%s] depends on [shell_exec()] which has likely been disabled for security reasons on this computer or server. Please refer to online documentation or source code comments on how to enable this feature.', __CLASS__, __FUNCTION__));
        }

        // Choose command(s) to run based on OS
        $fallback_cmd = null;
        switch (PHP_OS) {
            case 'WINNT': // Windows
                $cmd = 'ipconfig' . ($all === true ? ' /all' : '');
                break;
            case 'Darwin': // Mac
                $cmd = 'ifconfig';
                break;
            default:
                // All Others - *nix (Linux and Unix)
                // Newer versions of Linux will usually include the [ip] command
                // while older versions and other versions of Unix will usually
                // include the [ifconfig] command. For a comparison of the two
                // commands see:
                //   https://access.redhat.com/sites/default/files/attachments/rh_ip_command_cheatsheet_1214_jcs_print.pdf
                // Reference Links:
                //   https://linux.die.net/man/8/ip
                //   https://linux.die.net/man/8/ifconfig
                //   https://wiki.linuxfoundation.org/networking/iproute2
                $cmd = 'ip addr';
                $fallback_cmd = 'ifconfig';
                break;
        }

        // Run command(s) and return result (string or null)
        $info = shell_exec($cmd);
        if ($info === null && $fallback_cmd !== null) {
            $info = shell_exec($fallback_cmd);
        }
        return $info;
    }

    /**
     * Parse Network Info that from the function [networkInfo()] from a string
     * to a PHP Basic Object. By default an error will cause [null] to be returned
     * unless the parameter [$exception_on_parse_error] is set to [true].
     *
     * @param string|null $config_text
     * @param bool $exception_on_parse_error (Optional)
     * @return null|\stdClass
     * @throws \Exception
     */
    public function parseNetworkInfo($config_text, $exception_on_parse_error = false)
    {
        // Always return null if null is passed, for example if calling 
        // $this->parseNetworkInfo($this->networkInfo()) and networkInfo() fails.
        if ($config_text === null) {
            return null;
        }

        // Before parsing convert all lines to Unix Line endings
        //   "\r" = Carriage Return = Mac before OSX
        //   "\n" = Line Feed = *nix
        //   "\r\n" = CR/LF = Windows
        $config_text = str_replace("\r\n", "\n", $config_text);
        $config_text = str_replace("\r", "\n", $config_text);

        // Parse based on format:
        //   Linux/Unix - Text from either [ip addr] or [ifconfig]
        //   Mac - Text from [ifconfig]
        //   Windows - Text from [ipconfig]
        if (strpos($config_text, "inet") !== false) {
            return $this->parseIpAddr($config_text, $exception_on_parse_error);
        } elseif (strpos($config_text, 'Windows IP Configuration') !== false) {
            return $this->parseIpConfig($config_text, $exception_on_parse_error);
        } else {
            if ($exception_on_parse_error) {
                throw new \Exception(sprintf('Could not parse Network Info. Unexpected Text format when [%s->%s()] was called', __CLASS__, __FUNCTION__));
            } else {
                return null;
            }
        }
    }

    /**
     * Parse for Interface Types with IPv4 and IPv6 Addresses from
     * Linux/Unix Commands [ip addr] or [ifconfig]
     *
     * @param string|null $config_text
     * @param bool $exception_on_parse_error (Optional)
     * @return null|\stdClass
     * @throws \Exception
     */
    private function parseIpAddr($config_text, $exception_on_parse_error)
    {
        // Results of [ip] and [ifconfig] commands have a general expected 
        // format but vary in overall output format so only IP Addresses are 
        // parsed as that is that would be the most common use of these commands.
        //
        // Some examples for the source code of [ip] and [ifconfig] commands:
        // https://git.kernel.org/pub/scm/linux/kernel/git/shemminger/iproute2.git/tree/ip/ipaddress.c
        // https://opensource.apple.com/source/network_cmds/network_cmds-511.50.3/ifconfig.tproj/ifconfig.c.auto.html
        // https://github.com/freebsd/freebsd/blob/master/sbin/ifconfig/ifconfig.c

        $interfaces = array();
        $interface = null;

        // Loop through all lines of text
        $config_text = explode("\n", $config_text);
        foreach ($config_text as $line) {
            // New Interfaces will always start the line without
            // a space or tab character. Properties of each interface
            // will start with spaces or a tab.
            $pos = strpos($line, ' ');
            if ($pos !== 0 && strpos($line, "\t") !== 0) {
                // Determine format, some examples:
                //   Linux ip addr:  '2: eth0: <BROADCAST,MULTICAST...'
                //   Linux ifconfig: 'eth0      Link encap:Ethernet...'
                //   Mac ifconfig:   'en0: flags=8863<UP,BROADCAST...'
                if (preg_match('/^\d: ([a-zA-Z0-9]*?): </', $line)) {
                    $value = explode(':', $line);
                    $type = trim($value[1]);
                } else {
                    $pos2 = strpos($line, ':');
                    $pos = min($pos, $pos2);
                    $type = substr($line, 0, $pos);
                }

                // Add Interface to the Array
                $interface = new \stdClass;
                $interface->type = $type;
                $interface->inet = null;
                $interface->inet6 = null;
                $interfaces[] = $interface;
            }

            // Find IP Info
            $line = trim($line);
            if (strpos($line, 'inet') === 0) {
                // Error if an IP Address was found before the
                // first Interface. This would indicate that
                // invalid text was passed to this function.
                if ($interface === null) {
                    if ($exception_on_parse_error) {
                        throw new \Exception(sprintf('Could not parse Network Interface. Found [inet] before Interface Type when [%s->parseNetworkInfo()] was called', __CLASS__));
                    } else {
                        return null;
                    }
                }
                
                // Which Address Type
                $type = (strpos($line, 'inet6') === 0 ? 'inet6' : 'inet');

                // Linux ifconfig will often start with 'inet addr:IP'
                // All other values are expected to be 'inet IP ..' 
                $pos = strlen($type);
                if (strpos($line, $type . ' addr:') === 0) {
                    $pos += 6;
                }

                // Get only the IP
                $ip = trim(substr($line, $pos));
                $pos = strpos($ip, ' ');
                $ip = substr($ip, 0, $pos);

                // [ip addr] will likely include network mask
                // in a CIDR string as part of the IP so remove
                // it if found to include only IP Address.
                $pos = strpos($ip, '/');
                if ($pos !== false) {
                    $ip = substr($ip, 0, $pos);
                }

                // Set IP Property dynamically by type. 
                // Will be a string if only one IP exits for the 
                // Interface or an array if multiple IP's exist.
                if ($interface->{$type} === null) {
                    $interface->{$type} = $ip;
                } elseif (is_array($interface->{$type})) {
                    $interface->{$type}[] = $ip;
                } else {
                    $interface->{$type} = array($interface->{$type}, $ip);
                }
            }
        }

        // Return all Interfaces that contain 
        // either an IPv4 or IPv6 Address.
        $network = new \stdClass;
        $network->interfaces = array();
        foreach ($interfaces as $interface) {
            if ($interface->inet !== null || $interface->inet6 !== null) {
                $network->interfaces[] = $interface;
            }
        }
        return $network;
    }

    /**
     * Parse text from Windows Commands [ipconfig] or [ipconfig /all]
     *
     * @param string|null $config_text
     * @param bool $exception_on_parse_error (Optional)
     * @return null|\stdClass
     * @throws \Exception
     */
    private function parseIpConfig($config_text, $exception_on_parse_error)
    {
        // Build return object
        $network = new \stdClass;
        $network->name = '';
        $network->properties = array();
        $network->adapters = array();

        // Get Windows IP Name
        $config_text = explode("\n", $config_text);
        $line_count = count($config_text);
        if ($line_count > 3 && $config_text[0] === '' && $config_text[1] !== '' && $config_text[2] === '') {
            $network->name = $config_text[1];
        }

        // Validate for expected format
        if ($network->name !== 'Windows IP Configuration') {
            if ($exception_on_parse_error) {
                throw new \Exception(sprintf('Could not parse Windows Network Info. Unexpected 2nd line to equal [Windows IP Configuration] when [%s->parseNetworkInfo()] was called', __CLASS__));
            } else {
                return null;
            }
        }

        // Find general properties
        $n = 3;
        while ($n < $line_count) {
            $line = $config_text[$n];
            if ($line === '') {
                break;
            }

            // Get Name
            $pos = strpos($line, ' . ');
            $name = rtrim(substr($line, 3, $pos - 3), ' .');

            // Get Value
            $pos = strpos($line, ' . : ');
            $value = '';
            if ($pos !== false) {
                $value = substr($line, $pos + 5);
            }
                
            // Add to Array
            $network->properties[$name] = $value;

            // Next line
            $n++;
        }

        // Process each line
        $last_line = $n;
        $adapter = null;
        $new_group = false;
        $last_property = null;
        for ($n = $last_line; $n < $line_count; $n++) {
            // Get the current line
            $line = $config_text[$n];

            // Start of a new group
            if ($line === '') {
                $new_group = true;
                continue;
            }

            // Group Name
            if ($new_group && strpos($line, ' ') !== 0 && substr($line, -1) === ':') {
                $adapter = new \stdClass;
                $adapter->name = substr($line, 0, -1);
                $adapter->properties = array();
                $network->adapters[] = $adapter;
                $last_property = null;
            // Parse item rows
            } else if (substr($line, 0, 3) === '   ') {
                // Get Name
                $pos = strpos($line, ' . ');
                if ($pos === false) {
                    // Handle items with multiple values such as networks that
                    // use multiple DNS Servers
                    $value = trim($line);
                    if ($last_property !== null && $value !== '') {
                        if (is_array($adapter->properties[$last_property])) {
                            $adapter->properties[$last_property][] = $value;
                        } else {
                            $adapter->properties[$last_property] = array(
                                $adapter->properties[$last_property],
                                $value,
                            );
                        }
                    }
                } else {
                    // Get Name and Value
                    $name = rtrim(substr($line, 3, $pos - 3), ' .');
                    $pos = strpos($line, ' . : ');
                    $value = '';
                    if ($pos !== false) {
                        $value = substr($line, $pos + 5);
                    }

                    // This should never happen, if it does then the
                    // code was likely changed or the text passed to this
                    // function is not valid.
                    if ($adapter === null) {
                        if ($exception_on_parse_error) {
                            throw new \Exception(sprintf('Could not parse Windows Network Info at Line [%s] when [%s->parseNetworkInfo()] was called', $line, __CLASS__));
                        } else {
                            return null;
                        }
                    }

                    // Add to Array
                    $adapter->properties[$name] = $value;
                    $last_property = $name;                    
                }
            }

            // Update flag variable
            $new_group = false;
        }

        // Return parsed values
        return $network;        
    }
}
