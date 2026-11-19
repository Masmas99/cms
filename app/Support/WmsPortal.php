<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class WmsPortal
{
    /**
     * Determine the signed-in user's WMS portal.
     */
    private static function portal(): string
    {
        return Auth::user()?->isAdmin() ? 'admin' : 'ess';
    }

    /**
     * Determine the route-name prefix for the signed-in user's WMS portal.
     */
    public static function routePrefix(): string
    {
        return self::portal().'.wms.';
    }

    /**
     * Build the full route name for a WMS screen.
     */
    public static function routeName(string $name): string
    {
        if ($name === 'dashboard') {
            return self::portal() === 'admin' ? 'admin.dashboard' : 'dashboard';
        }

        return self::routePrefix().$name;
    }

    /**
     * Build the full view name for a WMS screen.
     */
    public static function view(string $name): string
    {
        return 'pages.'.self::portal().'.'.$name;
    }

    /**
     * Generate a URL for a WMS screen in the signed-in user's portal.
     */
    public static function route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route(self::routeName($name), $parameters, $absolute);
    }
}
