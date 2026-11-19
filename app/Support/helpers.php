<?php

use App\Support\WmsPortal;

if (! function_exists('wms_route')) {
    /**
     * Generate a URL for the signed-in user's WMS portal.
     */
    function wms_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return WmsPortal::route($name, $parameters, $absolute);
    }
}

if (! function_exists('wms_route_name')) {
    /**
     * Resolve the full route name for the signed-in user's WMS portal.
     */
    function wms_route_name(string $name): string
    {
        return WmsPortal::routeName($name);
    }
}

if (! function_exists('wms_view')) {
    /**
     * Resolve the full view name for the signed-in user's WMS portal.
     */
    function wms_view(string $name): string
    {
        return WmsPortal::view($name);
    }
}
