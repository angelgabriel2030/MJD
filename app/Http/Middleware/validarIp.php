<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class validarIp
{
    private array $allowedIps = [
        '127.0.0.1',
    ];

    private array $allowedDomains = [
        'https://azariah-unbrittle-gwen.ngrok-free.dev',
        'ngrok.io',
        'ngrok-free.app',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');

        if (in_array($clientIp, $this->allowedIps)) {
            return $next($request);
        }

        if ($this->isAllowedDomain($origin) || $this->isAllowedDomain($referer)) {
            return $next($request);
        }

        $ngrokHeader = $request->header('X-Forwarded-For');
        if ($ngrokHeader) {
            $forwardedIps = explode(',', $ngrokHeader);
            $originalIp = trim($forwardedIps[0]);
            
            if (in_array($originalIp, $this->allowedIps)) {
                return $next($request);
            }
        }

        return response()->json([
            'error' => 'Acceso denegado',
            'message' => 'Tu IP no está autorizada para acceder a este servicio',
            'your_ip' => $clientIp
        ], 403);
    }

    private function isAllowedDomain(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST) ?? $url;

        foreach ($this->allowedDomains as $domain) {
            if (str_contains($host, $domain)) {
                return true;
            }
        }

        return false;
    }
}
