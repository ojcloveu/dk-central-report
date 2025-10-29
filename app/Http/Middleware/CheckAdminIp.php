<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminIp
{
    protected $excludedRoutes = [
        '/health',
        '/lb-health',
        '/status',
        '/ping',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip whitelist for health check routes
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        $clientIP = $this->getRealIP($request);

        if (!$this->isWhitelisted($clientIP)) {
            abort(403, 'Your IP address (' . $clientIP . ') is not authorized to access this resource.');
        }

        return $next($request);
    }

    private function isExcludedRoute(Request $request)
    {
        $path = $request->path();
        return in_array('/' . $path, $this->excludedRoutes) ||
               in_array($path, $this->excludedRoutes);
    }

    private function getRealIP(Request $request)
    {
        // Get real IP from load balancer headers
        if ($request->header('X-Forwarded-For')) {
            $ips = explode(',', $request->header('X-Forwarded-For'));
            return trim($ips[0]);
        }

        if ($request->header('X-Real-IP')) {
            return $request->header('X-Real-IP');
        }

        return $request->ip();
    }

    private function isWhitelisted($ip)
    {
        $whitelist = $this->getWhitelistFromEnv();

        if (empty($whitelist) || in_array('*', $whitelist)) {
            return true;
        }

        return in_array($ip, $whitelist);
    }

    private function getWhitelistFromEnv()
    {
        $ips = config('security.allowed_ips');

        if (empty($ips)) {
            return [];
        }

        // Split by comma and trim whitespace
        $ipList = array_map('trim', explode(',', $ips));

        // Remove empty values
        return array_filter($ipList);
    }
}
