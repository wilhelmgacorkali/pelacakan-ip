<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Proxies yang boleh dipercaya.
     * Untuk deployment di reverse proxy, Load Balancer, atau Cloudflare,
     * perlu percaya semua header forwarded agar IP client benar-benar terbaca.
     */
    protected $proxies = '*';

    /**
     * Header yang dipakai Laravel untuk membaca client IP.
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
