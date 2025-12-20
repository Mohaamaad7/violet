<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivityContext
{
    /**
     * Handle an incoming request.
     * Stores IP address, user agent, and device type for activity logging.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Store context in config for activity log to use
        config([
            'activitylog.context.ip_address' => $request->ip(),
            'activitylog.context.user_agent' => $request->userAgent(),
            'activitylog.context.device_type' => $this->getDeviceType($request->userAgent()),
        ]);

        return $next($request);
    }

    /**
     * Determine device type from user agent
     */
    protected function getDeviceType(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/i', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/ipad|tablet/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }
}
