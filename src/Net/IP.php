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

namespace FastSitePHP\Net;

/**
 * Internet Protocol
 *
 * This class includes several static functions for working with IP Addresses
 * and CIDR Strings. Validating IP’s is often important for secure applications.
 */
class IP
{
    /**
     * The function cidr() is named for Classless Inter-Domain Routing (CIDR) which
     * is an Internet Standard that allows for an Internet Protocol Address (IP Address)
     * and its Subnet mask to be defined in a compact format. The format named CIDR Notation
     * allows for various network settings to be calculated from a string. CIDR Notation
     * supports both IPv4 and IPv6 Addresses and it is commonly used to specify a network
     * IP range and to compare other IP addresses to the network range.
     *
     * An example of a CIDR Notation value is '10.63.5.183/24' and for this example means that
     * the computer or device is on a private network (typically a corporate office network),
     * that the IP Address of a specific computer is '10.63.5.183', that the network
     * has a subnet mask of '255.255.255.0', and that there are 256 available IP Addresses
     * for the network. Other IP Address can be compared to this CIDR Value to see if
     * they are on the same network.
     *
     * This function is used internally by FastSitePHP with [Web\Request->clientIp()] and
     * other functions when checking trusted proxy addresses.
     *
     * This function can be called with 2 different parameter options and has different return
     * types based on how the function was called. If only a CIDR value is passed as the parameter
     * then this function returns an array of information calculated from the CIDR value, and if
     * both a CIDR Value (or an Array of CIDR Values) and an IP Address are passed as parameters
     * then this function compares the IP Address to the CIDR Value or Values and returns true if
     * the IP Address is on the same network and false if not. If there is an error with the
     * CIDR Value format then this function will return an error message in an array if looking
     * up the CIDR Value or throw an exception if comparing a CIDR Value to and IP Address.
     * Additionally if an IP Address is used instead of the CIDR Value then this function will
     * simply compare the two IP Addresses.
     *
     * Examples:
     *      *** These examples are comparing different private
     *          network IP Addresses with either a CIDR Notation
     *          Value or another IP Address
     *      true  = cidr('10.63.5.183/24', '10.63.5.120')
     *      false = cidr('10.63.5.183/24', '10.63.4.183')
     *      true  = cidr('fe80::/10', 'fe80::b091:1117:497a:9dc1')
     *      true  = cidr('10.10.120.12', '10.10.120.12')
     *      false = cidr('10.10.120.12', '10.10.120.13')
     *
     *      *** This example compares an IP Address to different
     *          IP Ranges for Amazon Web Services (AWS);
     *          note - these ranges may change over time
     *      true  = cidr('54.231.0.0/17', '54.231.17.108')    us-east-1
     *      false = cidr('54.231.128.0/19', '54.231.17.108')  eu-west-1
     *
     *      *** The IPv6 Addresses below look different however have the
     *          same value so they return true when compared. They have
     *          the same value because the first address omits leading
     *          zeros for display while the 2nd address does not.
     *      true = cidr('::1', '0000:0000:0000:0000:0000:0000:0000:0001')
     *      true = cidr('::1/128', '0000:0000:0000:0000:0000:0000:0000:0001')
     *
     *      *** This example is showing how an array of CIDR Strings can be used.
     *          If the IP Address being compared matches any of the CIDR Strings
     *          then this function will return true. The function [$app->privateNetworkAddresses()]
     *          is used to return a list of CIDR Strings that would be on a private network.
     *      true  = cidr(array('169.254.0.0/16', '10.0.0.0/8'), '10.10.120.14')
     *      true  = cidr($app->privateNetworkAddresses(), '10.10.120.15')
     *      false = cidr($app->privateNetworkAddresses(), '54.231.17.108')
     *
     *      *** Both Port Numbers and IPv6 Zone Indices can be part of the IP Address
     *          used to compare. Depending upon the environment a Zone Index
     *          may also be referred to as a Zone Identifier or a Scope ID.
     *      true = cidr('10.0.0.0/8', '10.10.120.13:8080')  // IPv4 Port Number
     *      true = cidr('2001:db8::/32', '[2001:db8:cafe::17]:4711') // IPv6 Port Number
     *      true = cidr('fe80::/10', 'fe80::3030:70d9:5af2:cc71%3') // IPv6 Zone Index
     *
     *      *** If an IPv6 Address is compared to an IPv4 CIDR or vice-versa
     *          this function will return false
     *      false = cidr('10.0.0.0/8', 'fe80::b091:1117:497a:9dc1')
     *      false = cidr('fe80::/10', '10.10.120.12')
     *
     *      *** Return Network Information including IP Range from a IPv4 CIDR Notation Value
     *      cidr('10.63.5.183/24')
     *      returns array(
     *          'CIDR_Notation' => '10.63.5.183/24',
     *          'Address_Type' => 'IPv4',
     *          'IP_Address' => '10.63.5.183',
     *          'Subnet_Mask' => '255.255.255.0',
     *          'Subnet_Mask_Bits' => 24,
     *          'Cisco_Wildcard' => '0.0.0.255',
     *          'Network_Address' => '10.63.5.0',
     *          'Broadcast' => '10.63.5.255',
     *          'Network_Range_First_IP' => '10.63.5.0',
     *          'Network_Range_Last_IP' => '10.63.5.255',
     *          'Usable_Range_First_IP' => '10.63.5.1',
     *          'Usable_Range_Last_IP' => '10.63.5.254',
     *          'Addresses_in_Network' => 256,
     *          'Usable_Addresses_in_Network' => 254,
     *      )
     *
     *      *** Return Network Information from a IPv6 CIDR Notation Value
     *      cidr('fe80::b091:1117:497a:9dc1/48')
     *      returns array(
     *          'CIDR_Notation' => 'fe80::b091:1117:497a:9dc1/48',
     *          'Address_Type' => 'IPv6',
     *          'IP_Address' => 'fe80::b091:1117:497a:9dc1',
     *          'Subnet_Mask' => 'ffff:ffff:ffff::',
     *          'Subnet_Mask_Bits' => 48,
     *          'Network_Address' => 'fe80::',
     *          'Network_Range_First_IP' => 'fe80::',
     *          'Network_Range_Last_IP' => 'fe80::ffff:ffff:ffff:ffff:ffff',
     *          'Addresses_in_Network' => '1208925819614629174706176',
     *      )
     *
     *      *** Example error when getting values for a CIDR Notation String
     *      cidr('abc.abc.abc.abc/24')
     *      returns array(
     *          'CIDR_Notation' => null,
     *          'Error_Message' => 'The value [abc.abc.abc.abc] is not in valid IPv4 format',
     *      )
     *
     *      *** Example error when comparing an IP Address with a CIDR Value
     *      cidr('abc.abc.abc.abc/24', '127.0.0.1')
     *      throws \InvalidArgumentException('The value [abc.abc.abc.abc] is not in valid IPv4 format')
     *
     *      *** If an invalid IP Address is specified with a valid CIDR value then
     *          this function returns false and does not throw an Exception
     *      false = cidr('10.0.0.0/8', 'abc')
     *
     * @link https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing
     * @link https://en.wikipedia.org/wiki/IPv4
     * @link https://en.wikipedia.org/wiki/IPv6_address
     * @link https://en.wikipedia.org/wiki/Subnetwork
     * @link https://en.wikipedia.org/wiki/IPv4_subnetting_reference
     * @link https://en.wikipedia.org/wiki/IPv6_subnetting_reference
     * @link https://en.wikipedia.org/wiki/Reserved_IP_addresses
     * @param string|array $cidr  CIDR Notation String Value or an array of CIDR Notation Strings
     * @param string|null $ip_to_compare    IP Address to compare to the CIDR Value or Values. Optional, defaults to null.
     * @return array|bool
     * @throws \InvalidArgumentException
     */
    public static function cidr($cidr, $ip_to_compare = null)
    {
        // If the parameters are an Array of CIDR Notation Strings and an
        // IP Address to compare against then recursively call this function
        // comparing each CIDR Value to the IP Address. If one of the items
        // matches return true otherwise return false.
        if (is_array($cidr) && is_string($ip_to_compare)) {
            foreach ($cidr as $cidr_value) {
                if (IP::cidr($cidr_value, $ip_to_compare)) {
                    return true;
                }
            }
            return false;
        }

        // Closure function for handling input errors. This function returns
        // error info in an array if $ip_to_compare is null as the function
        // would likely be used for handling user input, however if $ip_to_compare
        // is not null then this function is likely to be part of code that
        // needs to handle exceptions so an exception is thrown. The calling
        // application can either handle or display the result of the array;
        // or handle exceptions as needed.
        $error_message = function ($error_text) use ($ip_to_compare) {
            if ($ip_to_compare === null) {
                return array(
                    'CIDR_Notation' => null,
                    'Error_Message' => $error_text,
                );
            } else {
                throw new \InvalidArgumentException($error_text);
            }
        };

        // Overview:
        // This function deals with binary and hexadecimal numbers. This is
        // not a common topic for many web developers so a lot of comments
        // and examples are provided so that someone who is not familiar with
        // these topics and wants to learn more can do so. Additionally online
        // there are many very poor-quality, incorrect, and overly-complicated
        // examples of handling CIDR Notation and calculations with IP Addresses
        // and Subnet masks; developers often copy or attempt to the write this
        // code without understanding how it works so comments here explain
        // exactly what is happening.

        // Terms used here:
        //   Base: The number of unique digits in a numeral system,
        //     base-10 or the decimal system is what people count with
        //     and use for everyday math (0 to 9, 10 to 19, ....).
        //   Binary: A base-2 number which has a value of either 0 or 1,
        //     computers and electronic devices use binary numbers.
        //   Hexadecimal: A number of base-16 (0 to F, 10 to FF, ...),
        //     Binary numbers easily translate to hexadecimal numbers so they
        //     are commonly used in computer code. In many programming languages
        //     (including PHP) hexadecimal numbers are prefixed with '0x'
        //   Hex: Abbreviation for hexadecimal
        //   Bit: A single binary value
        //   Byte: 8 bits
        //   Bitwise Operation: Perform a calculation on a bit
        //   ASCII: An ASCII Character Code is a numerical representation
        //     of a Character (Letter)

        //Some helpful links:
        //  https://en.wikipedia.org/wiki/Bitwise_operation
        //  http://php.net/manual/en/language.operators.bitwise.php
        //  https://en.wikipedia.org/wiki/Signed_number_representations
        //  https://en.wikipedia.org/wiki/Binary_number
        //  https://support.microsoft.com/en-us/kb/164015
        //  http://playground.arduino.cc/Code/BitMath
        //  https://en.wikipedia.org/wiki/Radix

        // Example of the same number represented in different bases:
        //   decimal     255
        //   hex         FF
        //   binary      11111111

        // IPv4 using decimal-dotted notation looks like this '10.63.5.183'
        // but could could also be expressed using numbers of other bases:
        //     (hexadecimal)   A.3F.5.B7
        //     (binary)        1010.111111.101.10110111

        // To a computer IPv4 has 32 bits which means it's made of up 32 ones
        // or zeros. An IPv6 address uses the same binary calculations but with
        // larger numbers; 128 bits rather than 32 bits. To perform bitwise
        // operation an IP Address must first be converted from a string format
        // that humans read to a binary format that computers can easily work with.

        // A subnet mask is used by the TCP/IP protocol to determine if
        // the device is on the local network or a remote network.
        //
        // A CIDR Notation value of '{ip}/24' means the subnet mask has 24 leading 1-bits
        // while the remaining 8 bits are all 0's. It looks like this:
        //      (decimal)       255.255.255.0
        //      (hexadecimal)   FF.FF.FF.00
        //      (binary)        11111111.11111111.11111111.00000000

        // Example of computing the first and last IP addresses in a network using
        // the above IP Address and Subnet mask:
        //      ---------------------------------------------------------------
        //      First IP in network range = {ip} AND {mask}
        //
        //      00001010.00111111.00000101.10110111     (10.63.5.183) IP address
        //      bitwise AND                             If 1 is in both positions then 1 otherwise 0
        //      11111111.11111111.11111111.00000000     (255.255.255.0) Subnet mask
        //      ====================================================
        //      00001010.00111111.00000101.00000000     (10.63.5.0)
        //      ---------------------------------------------------------------
        //
        //      ---------------------------------------------------------------
        //      Last IP in network range = {ip} OR (NOT {mask})
        //
        //      1)
        //      11111111.11111111.11111111.00000000     (255.255.255.0)
        //      bitwise NOT                             If 1 then 0 else if 0 then 1
        //      00000000.00000000.00000000.11111111     (0.0.0.255)
        //
        //      2)
        //      00001010.00111111.00000101.10110111     (10.63.5.183) IP address
        //      bitwise OR                              If 1 is in either position then 1 otherwise 0
        //      00000000.00000000.00000000.11111111     (0.0.0.255)
        //      ====================================================
        //      00001010.00111111.00000101.11111111     (10.63.5.255)
        //      ---------------------------------------------------------------

        // In a 32-bit system a signed integer supports both negative and positive
        // numbers and has values from -2147483648 to 2147483647, while an unsigned
        // integer only supports positive numbers and has values from 0 to 4294967295.
        // In binary the signed value of -1 equals 11111111 11111111 11111111 11111111
        // while the unsigned value of 4294967295 has the same bit value. This is because
        // 32 bits allows for a range of 4294967296 numbers (excluding zero) or 2^32.
        // The max unsigned value of 4294967295 is calculated from 2^32-1 because 0
        // would make up the first number available for computer calculations.
        //
        // PHP does not support unsigned integers so negative numbers are used when the
        // value is greater than 2147483647. There are different methods of representing
        // negative decimal numbers in binary, however PHP (and computer processors in
        // general) use a method known as Two's complement signed number representation.
        // It works by having the lowest negative number represent the first number
        // in binary after the highest positive decimal number.
        //
        // Hexadecimal examples of binary-to-decimal number conversion on a 32-bit system:
        //  0x00000000 = Lowest 32-bit hex number
        //  0xffffffff = Highest 32-bit hex number
        //
        //  hex      | decimal
        //  --------------------
        //  0x00000000 = 0    Lowest 32-bit hex number
        //  0x00000001 = 1
        //  ..
        //  0x00000009 = 9
        //  0x0000000a = 10
        //  0x0000000b = 11
        //  0x0000000c = 12
        //  0x0000000d = 13
        //  0x0000000e = 14
        //  0x0000000f = 15
        //  0x00000010 = 16
        //  0x00000011 = 17
        //  ..
        //  0x7ffffffe =  2147483646
        //  0x7fffffff =  2147483647  Highest 32-bit positive integer
        //  0x80000000 = -2147483648  Lowest 32-bit negative integer
        //  0x80000001 = -2147483647
        //  0x80000002 = -2147483646
        //  ...
        //  0xfffffffe = -2
        //  0xffffffff = -1   Highest 32-bit hex number

        // For this function IPv4 addresses are converted to and from integers using
        // functions ip2long() and long2ip(). IPv6 addresses are converted to and
        // from a packed internet address string format using functions inet_pton()
        // and inet_ntop(); a packed internet address string is really a string of
        // 16 characters where each character is a different ASCII value. PHP bitwise
        // operators (AND &), (OR |), (NOT ~), (XOR ^) work on integers and byte values of
        // a string so performing needed calculations is easy. When viewing code online
        // many examples are overly-complicated for calculating a network from an ip/subnet
        // however in reality it's easy as long as the values are in a binary format
        // that allow for bitwise operations.
        //
        // The functions inet_pton() and inet_ntop() are operating system functions
        // that work with both IPv4 and IPv6 but depending on how PHP is compiled
        // they may or may not be available so to be safe the functions ip2long()
        // and long2ip() are used for IPv4. If the functions are not available it
        // also means that IPv6 would not be available for PHP on the machine.

        // Initial Data Type Validation
        if (!is_string($cidr)) {
            $error = 'The function [%s::%s()] was called with an invalid parameter. The $cidr parameter must be defined a string but instead was defined as type [%s].';
            $error = sprintf($error, __CLASS__, __FUNCTION__, gettype($cidr));
            throw new \InvalidArgumentException($error);
        } elseif ($ip_to_compare !== null && !is_string($ip_to_compare)) {
            $error = 'The function [%s::%s()] was called with invalid parameters. The $ip_to_compare parameter must be defined a string or null but instead was defined as type [%s].';
            $error = sprintf($error, __CLASS__, __FUNCTION__, gettype($ip_to_compare));
            throw new \InvalidArgumentException($error);
        }

        // Split the string into an array. If passed correctly
        // it should be in the format of '{ip}/{bits}'. However
        // if comparing then only the IP Address is needed as this
        // function is intended on being called from with-in a loop
        // using multiple values (see the [Web\Request->clientIp()] function).
        $data = explode('/', $cidr);
        $size = count($data);
        $has_bit_mask = ($size === 2);

        if (!$has_bit_mask && !($size === 1 && $ip_to_compare !== null)) {
            return $error_message('Error Parsing CIDR Notation Value, it should be in the format of {IP Address}/{Subnet Mask Bits}');
        }

        // Parse IP and Subnet mask bits from the $cidr parameter
        $ip = $data[0];
        $bits = ($has_bit_mask ? (int)$data[1] : 0);

        // Determine the type of IP Address:
        //   IPv4 has 32 bits and looks like '10.63.5.183'
        //   IPv6 has 128 bits and looks like 'fe80::b091:1117:497a:9dc1'
        $ip_version = (strpos($ip, '.') !== false ? 'IPv4' : 'IPv6');
        $filter_type = ($ip_version === 'IPv4' ? FILTER_FLAG_IPV4 : FILTER_FLAG_IPV6);
        $max_bits = ($ip_version === 'IPv4' ? 32 : 128);

        // Check the IP Address from the CIDR String
        if (!filter_var($ip, FILTER_VALIDATE_IP, $filter_type)) {
            return $error_message(sprintf('The value [%s] is not in valid %s format', $ip, $ip_version));
        }

        // Check the bit range from the CIDR Notation Value
        if ($bits < 0 || $bits > $max_bits) {
            return $error_message(sprintf('The bit range for the subnet mask is not valid. Bit range was specified as %d from [%s] but it must be between 0 and %d', $bits, $cidr, $max_bits));
        }

        // If this function is comparing a CIDR Notation String to an IP Address,
        // then remove Port Number from the IP Address if defined and make sure
        // the IP Address to compare is a of the same IP Address type, for example
        // only compare IPv4 to IPv4 and not IPv4 to IPv6.
        if ($ip_to_compare !== null) {
            // If the IP Address has port number included then remove it.
            // Regular expressions could be used here however any matching
            // value should be an IP Address so this check is using basic
            // string functions strpos() and substr().
            if ($ip_version === 'IPv4') {
                // Handle IPv4 Port Numbers, Example: 10.10.10.1:8080
                if (($pos = strpos($ip_to_compare, ':')) !== false) {
                    $ip_to_compare = substr($ip_to_compare, 0, $pos);
                }
            } else {
                // Handle IPv6 Port Numbers, Example: [2001:db8:cafe::17]:4711
                if (substr($ip_to_compare, 0, 1) === '[' && ($pos = strpos($ip_to_compare, ']')) !== false) {
                    $ip_to_compare = substr($ip_to_compare, 1, $pos - 1);
                }

                // Handle IPv6 Local-Link Zone Indices (Depending upon the environment a
                // Zone Index may also be referred to as a Zone Identifier or a Scope ID).
                // These will be at the end IPv6 Address and start with a [%] character.
                // Examples:
                //   fe80::3030:70d9:5af2:cc71%3
                //   fe80::3%eth0
                if (($pos = strpos($ip_to_compare, '%')) !== false) {
                    $ip_to_compare = substr($ip_to_compare, 0, $pos);
                }
            }

            // Check for the correct IP Address Type ('IPv4' or 'IPv6')
            if (!filter_var($ip_to_compare, FILTER_VALIDATE_IP, $filter_type)) {
                return false;
            }
        }

        // Data is valid so calculate subnet mask and return or compare values
        if ($ip_version == 'IPv4') {
            // The function ip2long() takes an IP Address in
            // decimal-dotted notation (e.g.: '255.255.255.0')
            // and converts it to a number. The number generated
            // will be different between 32-bit and 64-bit systems,
            // however when converted to binary format the bit
            // values will be the same.
            //
            // 32-Bit
            //   int(-256) = ip2long('255.255.255.0')
            //   11111111111111111111111100000000 = decbin(-256)
            //   or grouped into bytes
            //   11111111.11111111.11111111.00000000
            // 64-Bit
            //   int(4294967040) = ip2long('255.255.255.0')
            // 	 11111111111111111111111100000000 = decbin(4294967040)
            //
            $ip = ip2long($ip);

            // If only comparing IP Addresses and not Network Address
            // then compare values and return a bool
            if (!$has_bit_mask && $ip_to_compare !== null) {
                $ip_to_compare = ip2long($ip_to_compare);
                return ($ip === $ip_to_compare);
            }

            // Calculate value of the Subnet mask.
            //
            // Because integers in PHP are signed the number -1 results with
            // all one's for the bits when converted to binary.
            //  -1 in binary on a 32-bit system looks like this for an integer
            //  because there 32 total bits in an integer:
            //      11111111 11111111 11111111 11111111
            //  but on a 64-bit system which has 64 bits for an integer it looks this:
            //      11111111 11111111 11111111 11111111 11111111 11111111 11111111 11111111
            //
            //  Example for '{ip}/24'
            //  8 = (32-24) and 8 in binary = 1000 which looks like on 32-bit:
            //      00000000 00000000 00000000 00001000
            //  To determine the subnet mask shift-left (<<) the bits by 8:
            //      11111111 11111111 11111111 11111111
            //      00000000 00000000 00000000 00001000
            //      11111111 11111111 11111111 00000000 = (255.255.255.0)
            //  -1 is not the only value that would work. In PHP values 4294967295,
            //  ~0, 0xffffffff, and more would also work because they end up with
            //  all 1 bits for the value on the left.
            //
            //  On a 64-bit system there will be more bytes in the integer but when converted back
            //  to a IP address in the common decimal-dotted notation (255.255.255.0) only the first
            //  four bytes of the integer are used.
            //
            $subnet_mask = -1 << (32 - $bits);

            // If comparing an CIDR Value to an IP Address get the value of the IP to compare,
            // compare the resulting network address of both IP Addresses, and return a bool.
            //   Network address = {ip} bitwise AND {subnet_mask}
            if ($ip_to_compare !== null) {
                $ip_to_compare = ip2long($ip_to_compare);
                return (($ip & $subnet_mask) === ($ip_to_compare & $subnet_mask));
            }

            // Otherwise if not comparing and IP Address to a CIDR Value return an array of
            // network values calculated from the CIDR Notation String
            return array(
                'CIDR_Notation' => $cidr,
                'Address_Type' => $ip_version,
                'IP_Address' => long2ip($ip),
                'Subnet_Mask' => long2ip($subnet_mask),
                'Subnet_Mask_Bits' => $bits,
                'Cisco_Wildcard' => long2ip(~$subnet_mask),
                'Network_Address' => long2ip($ip & $subnet_mask),
                'Broadcast' => long2ip($ip | ~$subnet_mask),
                'Network_Range_First_IP' => long2ip($ip & $subnet_mask), //Same as Network Address
                'Network_Range_Last_IP' => long2ip($ip | ~$subnet_mask), //Same as Broadcast
                'Usable_Range_First_IP' => long2ip(($ip & $subnet_mask) + 1),
                'Usable_Range_Last_IP' => long2ip(($ip | ~$subnet_mask) - 1),
                'Addresses_in_Network' => pow(2, (32 - $bits)),
                'Usable_Addresses_in_Network' => (pow(2, (32 - $bits)) - 2),
            );
        } else {
            // Get the IPv6 Address in packed internet address string format.
            // Note - 'pton' stands for Presentation-format To Network-format.
            $ip = inet_pton($ip);
            if ($ip === false) {
                // This should never happen because of earlier validation using
                // `filter_var()` so it cannot be Unit Tested. However it's included in case
                // the code changes and primarily so that it better validates with PHP linters.
                return $error_message(sprintf('The value [%s] is not in valid for the function [inet_pton()].', $ip));
            }

            // If only comparing IP Addresses and not Network Address
            // then compare values and return a bool
            if (!$has_bit_mask && $ip_to_compare !== null) {
                $ip_to_compare = inet_pton($ip_to_compare);
                return ($ip === $ip_to_compare);
            }

            // Create a string that represents the Subnet Mask in binary format (bit string).
            // Example: 48 means the first 48 bits are 1's and the remaining 80 bits are 0's.
            // 11111111111111111111111111111111111111111111111100000000000000000000000000000000000000000000000000000000000000000000000000000000
            $binary_string = str_repeat('1', $bits) . str_repeat('0', (128 - $bits));

            // Create an array that represents the binary string as a list of binary number strings in byte size
            // 11111111,11111111,11111111,11111111,11111111,11111111,00000000,00000000,00000000,00000000,00000000,00000000,00000000,00000000,00000000,00000000
            $binary_values = str_split($binary_string, 8);

            // Convert the binary array values to a packed internet address string:
            // Example (each number represents an ASCII character in the string):
            //   255,255,255,255,255,255,0,0,0,0,0,0,0,0,0,0
            $subnet_mask = '';
            foreach ($binary_values as $bin_value) {
                $subnet_mask .= chr(bindec($bin_value));
            }

            // If comparing an CIDR Value to an IP Address get the value of the IP to compare,
            // compare the resulting network address of both IP Addresses, and return a bool.
            //   Network address = {ip} bitwise AND {subnet_mask}
            if ($ip_to_compare !== null) {
                $ip_to_compare = inet_pton($ip_to_compare);
                return (($ip & $subnet_mask) === ($ip_to_compare & $subnet_mask));
            }

            // Otherwise if not comparing and IP Address to a CIDR Value return an array of
            // network values calculated from the CIDR Notation String
            return array(
                'CIDR_Notation' => $cidr,
                'Address_Type' => $ip_version,
                'IP_Address' => inet_ntop($ip),
                'Subnet_Mask' => inet_ntop($subnet_mask),
                'Subnet_Mask_Bits' => $bits,
                'Network_Address' => inet_ntop($ip & $subnet_mask),
                'Network_Range_First_IP' => inet_ntop($ip & $subnet_mask), // Same as Network Address
                'Network_Range_Last_IP' => inet_ntop($ip | ~$subnet_mask),
                'Addresses_in_Network' => (function_exists('bcpow') ? bcpow('2', (string)(128 - $bits)) : null),
            );
        }
    }

    /**
     * Return an Array of CIDR Notation Strings that contains Network Addresses that would
     * only be on a private network (for example a Home Office Network or Enterprise LAN).
     * Specific IP Address ranges are assigned for Private networks and in most cases this
     * function will return addresses for both IPv4 and IPv6. This function will only return
     * IPv4 addresses if the server running this function does not support IPv6. This function
     * can be used with the [cidr()] function to test if an IP Address is on a local network.
     *
     * This function is used internally by FastSitePHP with [Web\Request->clientIp()] and
     * other functions when checking trusted proxy addresses.
     *
     * The following CIDR Notation Strings are returned:
     *   [
     *      '127.0.0.0/8',      // IPv4 localhost
     *      '10.0.0.0/8',       // IPv4 Private Network, RFC1918 24-bit block
     *      '172.16.0.0/12',    // IPv4 Private Network, RFC1918 20-bit block
     *      '192.168.0.0/16',   // IPv4 Private Network, RFC1918 16-bit block
     *      '169.254.0.0/16',   // IPv4 local-link
     *      '::1/128',          // IPv6 localhost
     *      'fc00::/7',         // IPv6 Unique local address (Private Network)
     *      'fe80::/10',        // IPv6 local-link
     *   ]
     *
     * The IPv6 Unique local address 'fc00::/7' also covers the IP Range 'fd00::/8'.
     *
     * @link https://en.wikipedia.org/wiki/Private_network
     * @link https://en.wikipedia.org/wiki/Reserved_IP_addresses
     * @link https://en.wikipedia.org/wiki/Localhost
     * @link https://en.wikipedia.org/wiki/Link-local_address
     * @link https://en.wikipedia.org/wiki/Unique_local_address
     * @return array
     */
    public static function privateNetworkAddresses()
    {
        // IPv4 Local/Private Network Addresses
        $cidr_values = array(
            '127.0.0.0/8',      // localhost
            '10.0.0.0/8',       // Private Network, RFC1918 24-bit block
            '172.16.0.0/12',    // Private Network, RFC1918 20-bit block
            '192.168.0.0/16',   // Private Network, RFC1918 16-bit block
            '169.254.0.0/16',   // local-link
        );

        // Not all installations of PHP will support IPv6 so make sure that the
        // required function [inet_ntop()] for IPv6 is included with the
        // version of PHP on the server. If it is included then add IPv6 CIDR Values.
        //
        // According to documentation if [!defined('AF_INET6')] then PHP was not
        // compiled with IPv6 however on Windows this option will likely not be set so
        // checking if the function [inet_ntop()] is defined instead is a more reliable
        // way of checking IPv6 Support because [inet_ntop()] is required for IPv6.
        if (function_exists('inet_ntop')) {
            $cidr_values[] = '::1/128';     // localhost
            $cidr_values[] = 'fc00::/7';    // Unique local address (Private Network)
            $cidr_values[] = 'fe80::/10';   // local-link
        }

        return $cidr_values;
    }
}
