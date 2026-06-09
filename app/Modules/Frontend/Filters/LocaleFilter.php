<?php

namespace App\Modules\Frontend\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Set the request locale from (in order):
 *   1. ?lang=xx query string (one-shot test)
 *   2. saloncms_locale cookie (user's choice)
 *   3. salon_default_lang setting (admin's default)
 *   4. Config\App::$defaultLocale (en)
 */
class LocaleFilter implements FilterInterface
{
    private const COOKIE = 'saloncms_locale';

    public function before(RequestInterface $request, $arguments = null)
    {
        $supported = config('App')->supportedLocales ?? ['en'];

        $locale = $request->getGet('lang')
            ?: ($_COOKIE[self::COOKIE] ?? null);

        if (! $locale) {
            // fall back to admin-set default in the settings table (cheap, cached)
            try {
                $locale = (new \App\Modules\Settings\Models\SettingModel())->get('salon_default_lang');
            } catch (\Throwable $e) { /* settings table missing — ignore */ }
        }

        if ($locale && in_array($locale, $supported, true)) {
            $request->setLocale($locale);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
