/**
 * FastSitePHP Unit Testing JavaScript File
 */

/* Validates with [jshint] */
/* global runHttpUnitTest */
/* jshint strict: true */

(function () {
    "use strict"; // Invoke strict mode
    
    runHttpUnitTest("Networking IP Object", "test-net-ip.php/check-net-ip-class", {
        response: {
            get_class: "FastSitePHP\\Net\\IP",
            get_parent_class: false
        }
    });
    
    runHttpUnitTest("Networking IP Object - Properties", "test-net-ip.php/check-net-ip-properties", {
        response: "All properties matched for [FastSitePHP\\Net\\IP]: "
    });
    
    runHttpUnitTest("Networking IP Object - Functions", "test-net-ip.php/check-net-ip-methods", {
        response: "All methods matched for [FastSitePHP\\Net\\IP]: cidr, privateNetworkAddresses"
    });

    runHttpUnitTest("Networking IP Object - cidr() - General Errors", "test-net-ip.php/cidr-general-errors", {
        response: {
            test0: "[InvalidArgumentException][The function [FastSitePHP\\Net\\IP::cidr()] was called with an invalid parameter. The $cidr parameter must be defined a string but instead was defined as type [integer].]",
            test1: "[InvalidArgumentException][The function [FastSitePHP\\Net\\IP::cidr()] was called with invalid parameters. The $ip_to_compare parameter must be defined a string or null but instead was defined as type [integer].]",
            test2: {
                CIDR_Notation: null,
                Error_Message: "Error Parsing CIDR Notation Value, it should be in the format of {IP Address}/{Subnet Mask Bits}"
            },
            test3: "[InvalidArgumentException][The value [] is not in valid IPv6 format]"
        }
    });

    runHttpUnitTest("Networking IP Object - cidr() - IPv4", "test-net-ip.php/cidr-ipv4", {
        response: {
            item1: {
                CIDR_Notation: "10.63.5.183/24",
                Address_Type: "IPv4",
                IP_Address: "10.63.5.183",
                Subnet_Mask: "255.255.255.0",
                Subnet_Mask_Bits: 24,
                Cisco_Wildcard: "0.0.0.255",
                Network_Address: "10.63.5.0",
                Broadcast: "10.63.5.255",
                Network_Range_First_IP: "10.63.5.0",
                Network_Range_Last_IP: "10.63.5.255",
                Usable_Range_First_IP: "10.63.5.1",
                Usable_Range_Last_IP: "10.63.5.254",
                Addresses_in_Network: 256,
                Usable_Addresses_in_Network: 254
            },
            item2: {
                CIDR_Notation: "54.231.17.108/17",
                Address_Type: "IPv4",
                IP_Address: "54.231.17.108",
                Subnet_Mask: "255.255.128.0",
                Subnet_Mask_Bits: 17,
                Cisco_Wildcard: "0.0.127.255",
                Network_Address: "54.231.0.0",
                Broadcast: "54.231.127.255",
                Network_Range_First_IP: "54.231.0.0",
                Network_Range_Last_IP: "54.231.127.255",
                Usable_Range_First_IP: "54.231.0.1",
                Usable_Range_Last_IP: "54.231.127.254",
                Addresses_in_Network: 32768,
                Usable_Addresses_in_Network: 32766
            }
        }
    });

    runHttpUnitTest("Networking IP Object - cidr() - IPv4 Compare", "test-net-ip.php/cidr-ipv4-compare", {
        response: {
            item00: { cidr: "10.63.5.183/24", ip_to_compare: "10.63.5.120", result: true },
            item01: { cidr: "10.63.5.183/24", ip_to_compare: "10.63.4.183", result: false },
            item02: { cidr: "10.10.120.12", ip_to_compare: "10.10.120.12", result: true },
            item03: { cidr: "10.10.120.12", ip_to_compare: "10.10.120.13", result: false },
            item04: { cidr: "54.231.0.0/17", ip_to_compare: "54.231.17.108", result: true },
            item05: { cidr: "54.231.128.0/19", ip_to_compare: "54.231.17.108", result: false },
            item06: { cidr: "10.0.0.0/8", ip_to_compare: "abc", result: false },
            item07: { cidr: ["10.0.0.0/8", "54.231.0.0/17"], ip_to_compare: "54.231.17.109", result: true },
            item08: { cidr: ["10.0.0.0/8", "54.231.0.0/17"], ip_to_compare: "169.254.1.1", result: false },
            item09: { cidr: ["172.16.0.0/12", "169.254.0.0/16"], ip_to_compare: "169.254.1.1", result: true },
            item10: { cidr: ["127.0.0.0/8"], ip_to_compare: "127.0.0.1", result: true },
            item11: { cidr: "10.0.0.0/8", ip_to_compare: "10.10.120.13:8080", result: true },
            item12: { cidr: "127.0.0.0/8", ip_to_compare: "127.0.0.1", result: true },
            item13: { cidr: "127.0.0.0/8", ip_to_compare: "127.0.0.2", result: true },
            item14: { cidr: "10.0.0.0/8", ip_to_compare: "10.0.0.1", result: true },
            item15: { cidr: "172.16.0.0/12", ip_to_compare: "172.16.0.1", result: true },
            item16: { cidr: "192.168.0.0/16", ip_to_compare: "192.168.0.1", result: true },
            item17: { cidr: "169.254.0.0/16", ip_to_compare: "169.254.1.1", result: true }
        }
    });

    runHttpUnitTest("Networking IP Object - cidr() - IPv4 Errors", "test-net-ip.php/cidr-ipv4-errors", {
        response: {
            test0: {
                CIDR_Notation: null,
                Error_Message: "The value [abc.abc.abc.abc] is not in valid IPv4 format"
            },
            test1: "[InvalidArgumentException][The value [abc.abc.abc.abc] is not in valid IPv4 format]",
            test2: "[InvalidArgumentException][The bit range for the subnet mask is not valid. Bit range was specified as -1 from [127.0.0.1/-1] but it must be between 0 and 32]",
            test3: "[InvalidArgumentException][The bit range for the subnet mask is not valid. Bit range was specified as 33 from [127.0.0.1/33] but it must be between 0 and 32]"
        }
    });

    runHttpUnitTest("Networking IP Object - cidr() - IPv6", "test-net-ip.php/cidr-ipv6", {
        response: {
            item1: {
                CIDR_Notation: "fe80::b091:1117:497a:9dc1/48",
                Address_Type: "IPv6",
                IP_Address: "fe80::b091:1117:497a:9dc1",
                Subnet_Mask: "ffff:ffff:ffff::",
                Subnet_Mask_Bits: 48,
                Network_Address: "fe80::",
                Network_Range_First_IP: "fe80::",
                Network_Range_Last_IP: "fe80::ffff:ffff:ffff:ffff:ffff",
                Addresses_in_Network: "1208925819614629174706176"
            },
            item2: {
                CIDR_Notation: "2001:db8:0123:4567:89ab:cdef:9876:5432/32",
                Address_Type: "IPv6",
                IP_Address: "2001:db8:123:4567:89ab:cdef:9876:5432",
                Subnet_Mask: "ffff:ffff::",
                Subnet_Mask_Bits: 32,
                Network_Address: "2001:db8::",
                Network_Range_First_IP: "2001:db8::",
                Network_Range_Last_IP: "2001:db8:ffff:ffff:ffff:ffff:ffff:ffff",
                Addresses_in_Network: "79228162514264337593543950336"
            }
        }
    });

    runHttpUnitTest("Networking IP Object - cidr() - IPv6 Compare", "test-net-ip.php/cidr-ipv6-compare", {
        response: {
            item00: { cidr: "fe80::/10", ip_to_compare: "fe80::b091:1117:497a:9dc1", result: true },
            item01: { cidr: "::1", ip_to_compare: "0000:0000:0000:0000:0000:0000:0000:0001", result: true },
            item02: { cidr: "::1/128", ip_to_compare: "0000:0000:0000:0000:0000:0000:0000:0001", result: true },
            item03: { cidr: "2001:db8::/32", ip_to_compare: "fe80::b091:1117:497a:9dc1", result: false },
            item04: { cidr: "2001:db8::/32", ip_to_compare: "2001:db8:0123:4567:89ab:cdef:9876:5432", result: true },
            item05: { cidr: "fe80::/10", ip_to_compare: "127.0.0.1", result: false },
            item06: { cidr: "fe80::/10", ip_to_compare: "abc", result: false },
            item07: { cidr: ["::1", "2001:db8::/32"], ip_to_compare: "fe80::b091:1117:497a:9dc1", result: false },
            item08: { cidr: ["::1", "2001:db8::/32"], ip_to_compare: "2001:db8:0123:4567:89ab:cdef:9876:5432", result: true },
            item09: { cidr: "2001:db8::/32", ip_to_compare: "[2001:db8:cafe::17]:4711", result: true },
            item10: { cidr: "fe80::/10", ip_to_compare: "fe80::3030:70d9:5af2:cc71%3", result: true },
            item11: { cidr: "fe80::/10", ip_to_compare: "fe80::3%eth0", result: true },
            item12: { cidr: "fe80::/10", ip_to_compare: "[fe80::3030:70d9:5af2:cc71%3]:4712", result: true },
            item13: { cidr: "2001:4860::/32", ip_to_compare: "2001:4860:4801:1303:0:6006:1300:b075", result: true },
            item14: { cidr: "fc00::/7", ip_to_compare: "fddb:1273:5643::1234", result: true }
        }
    });

    runHttpUnitTest("Networking IP Object - cidr() - IPv6 Errors", "test-net-ip.php/cidr-ipv6-errors", {
        response: {
            test0: {
                CIDR_Notation: null,
                Error_Message: "The value [ggg::] is not in valid IPv6 format"
            },
            test1: {
                CIDR_Notation: null,
                Error_Message: "The value [abc] is not in valid IPv6 format"
            },
            test2: "[InvalidArgumentException][The value [ggg::] is not in valid IPv6 format]",
            test3: "[InvalidArgumentException][The bit range for the subnet mask is not valid. Bit range was specified as -1 from [fe80::/-1] but it must be between 0 and 128]",
            test4: "[InvalidArgumentException][The bit range for the subnet mask is not valid. Bit range was specified as 129 from [fe80::/129] but it must be between 0 and 128]"
        }
    });
    
    runHttpUnitTest("Networking IP Object - privateNetworkAddresses() for IPv4 Addresses", "test-net-ip.php/private-network-addresses-ipv4", {
        type: "text",
        response: "Arrays match: 127.0.0.0/8, 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, 169.254.0.0/16"
    });

    runHttpUnitTest("Networking IP Object - privateNetworkAddresses() for IPv4 and IPv6 Addresses", "test-net-ip.php/private-network-addresses-all", {
        type: "text",
        response: "Arrays match: 127.0.0.0/8, 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, 169.254.0.0/16, ::1/128, fc00::/7, fe80::/10"
    });

    runHttpUnitTest("Request Proxy Function - clientIp() using IPv4", "test-net-ip.php/client-ip-ipv4", {
        response: "Success for clientIp() function with IPv4 Addresses, Completed 46 Unit Tests and 3 Exception Tests"
    });

    runHttpUnitTest("Request Proxy Function - clientIp() using IPv6", "test-net-ip.php/client-ip-ipv6", {
        response: "Success for clientIp() function with IPv6 Addresses, Completed 28 Unit Tests and 3 Exception Tests"
    });

    runHttpUnitTest("Request Proxy Function - protocol()", "test-net-ip.php/verify-protocol", {
        response: "Success for protocol() function, Completed 15 Unit Tests",
    });

    runHttpUnitTest("Request Proxy Function - host()", "test-net-ip.php/verify-host", {
        response: "Success for host() function, Completed 11 Unit Tests and 5 Exception Tests",
    });

    runHttpUnitTest("Request Proxy Function - port()", "test-net-ip.php/verify-port", {
        response: "Success for port() function, Completed 12 Unit Tests",
    });

})();
