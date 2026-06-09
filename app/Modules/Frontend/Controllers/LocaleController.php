<?php

namespace App\Modules\Frontend\Controllers;

use App\Controllers\BaseController;

class LocaleController extends BaseController
{
    public function switch(string $locale)
    {
        $supported = config('App')->supportedLocales ?? ['en'];
        if (! in_array($locale, $supported, true)) $locale = 'en';

        // Persist user's choice (1 year)
        setcookie('saloncms_locale', $locale, [
            'expires'  => time() + 31536000,
            'path'     => '/',
            'samesite' => 'Lax',
            'httponly' => false,
        ]);

        // Redirect back to where they came from (default: home)
        $back = $this->request->getServer('HTTP_REFERER') ?: site_url('/');
        return redirect()->to($back);
    }
}
