<?php

namespace App\Middleware;

use FastSitePHP\Net\IP;
use FastSitePHP\Web\Request;

class Env
{
    /**
     * Return true if the request is running from localhost '127.0.0.1' (IPv4)
     * or '::1' (IPv6) and if the web server is also running on localhost.
     * 
     * @return bool
     */
    public function isLocalhost()
    {
        $req = new Request();
        return $req->isLocal();
    }

    /**
     * Return true if the web request is coming a local network.
     * (for example 127.0.0.1 or 10.0.0.1).
     * 
     * @return bool
     */
    public function isLocalNetwork()
    {
        $req = new Request();
        $user_ip = $req->clientIp();
        $ip_list = IP::privateNetworkAddresses();
        return IP::cidr($ip_list, $user_ip);
    }

    /**
     * Return true if the web request is coming a local network and
     * and a Proxy Server such as a Load Balancer is being used.
     * 
     * @return bool
     */
    public function isLocalFromProxy()
    {
        $req = new Request();
        $user_ip = $req->clientIp('from proxy');
        $ip_list = IP::privateNetworkAddresses();
        return IP::cidr($ip_list, $user_ip);
    }
}
