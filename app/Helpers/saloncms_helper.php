<?php

/**
 * SalonCMS shared helpers — loaded via app/Config/Autoload.php $helpers.
 */

if (! function_exists('phone_local')) {
    /**
     * Strip the leading country code from an international phone number.
     * "+94 71 200 1111" → "71 200 1111"
     * "+9471200111"     → "71200111"
     * "071234567"       → "071234567"  (already local — left alone)
     */
    function phone_local(?string $phone): string
    {
        if ($phone === null) return '';
        return ltrim(preg_replace('/^\+\d{1,3}[\s\-]*/', '', trim($phone)));
    }
}

if (! function_exists('phone_digits')) {
    /** Return only the digits of a phone string (for matching). */
    function phone_digits(?string $phone): string
    {
        return preg_replace('/\D/', '', (string) $phone);
    }
}

if (! function_exists('country_dial_codes')) {
    /**
     * Curated list of countries with their international dialing codes.
     * Sri Lanka first (default market). Each: ['iso','name','dial','flag'].
     */
    function country_dial_codes(): array
    {
        return [
            ['iso'=>'LK','name'=>'Sri Lanka','dial'=>'94','flag'=>'🇱🇰'],
            ['iso'=>'IN','name'=>'India','dial'=>'91','flag'=>'🇮🇳'],
            ['iso'=>'AE','name'=>'United Arab Emirates','dial'=>'971','flag'=>'🇦🇪'],
            ['iso'=>'SA','name'=>'Saudi Arabia','dial'=>'966','flag'=>'🇸🇦'],
            ['iso'=>'QA','name'=>'Qatar','dial'=>'974','flag'=>'🇶🇦'],
            ['iso'=>'KW','name'=>'Kuwait','dial'=>'965','flag'=>'🇰🇼'],
            ['iso'=>'OM','name'=>'Oman','dial'=>'968','flag'=>'🇴🇲'],
            ['iso'=>'BH','name'=>'Bahrain','dial'=>'973','flag'=>'🇧🇭'],
            ['iso'=>'GB','name'=>'United Kingdom','dial'=>'44','flag'=>'🇬🇧'],
            ['iso'=>'US','name'=>'United States','dial'=>'1','flag'=>'🇺🇸'],
            ['iso'=>'CA','name'=>'Canada','dial'=>'1','flag'=>'🇨🇦'],
            ['iso'=>'AU','name'=>'Australia','dial'=>'61','flag'=>'🇦🇺'],
            ['iso'=>'SG','name'=>'Singapore','dial'=>'65','flag'=>'🇸🇬'],
            ['iso'=>'MY','name'=>'Malaysia','dial'=>'60','flag'=>'🇲🇾'],
            ['iso'=>'MV','name'=>'Maldives','dial'=>'960','flag'=>'🇲🇻'],
            ['iso'=>'PK','name'=>'Pakistan','dial'=>'92','flag'=>'🇵🇰'],
            ['iso'=>'BD','name'=>'Bangladesh','dial'=>'880','flag'=>'🇧🇩'],
            ['iso'=>'NP','name'=>'Nepal','dial'=>'977','flag'=>'🇳🇵'],
            ['iso'=>'CN','name'=>'China','dial'=>'86','flag'=>'🇨🇳'],
            ['iso'=>'HK','name'=>'Hong Kong','dial'=>'852','flag'=>'🇭🇰'],
            ['iso'=>'JP','name'=>'Japan','dial'=>'81','flag'=>'🇯🇵'],
            ['iso'=>'KR','name'=>'South Korea','dial'=>'82','flag'=>'🇰🇷'],
            ['iso'=>'TH','name'=>'Thailand','dial'=>'66','flag'=>'🇹🇭'],
            ['iso'=>'ID','name'=>'Indonesia','dial'=>'62','flag'=>'🇮🇩'],
            ['iso'=>'PH','name'=>'Philippines','dial'=>'63','flag'=>'🇵🇭'],
            ['iso'=>'VN','name'=>'Vietnam','dial'=>'84','flag'=>'🇻🇳'],
            ['iso'=>'DE','name'=>'Germany','dial'=>'49','flag'=>'🇩🇪'],
            ['iso'=>'FR','name'=>'France','dial'=>'33','flag'=>'🇫🇷'],
            ['iso'=>'IT','name'=>'Italy','dial'=>'39','flag'=>'🇮🇹'],
            ['iso'=>'ES','name'=>'Spain','dial'=>'34','flag'=>'🇪🇸'],
            ['iso'=>'NL','name'=>'Netherlands','dial'=>'31','flag'=>'🇳🇱'],
            ['iso'=>'CH','name'=>'Switzerland','dial'=>'41','flag'=>'🇨🇭'],
            ['iso'=>'SE','name'=>'Sweden','dial'=>'46','flag'=>'🇸🇪'],
            ['iso'=>'NO','name'=>'Norway','dial'=>'47','flag'=>'🇳🇴'],
            ['iso'=>'IE','name'=>'Ireland','dial'=>'353','flag'=>'🇮🇪'],
            ['iso'=>'NZ','name'=>'New Zealand','dial'=>'64','flag'=>'🇳🇿'],
            ['iso'=>'ZA','name'=>'South Africa','dial'=>'27','flag'=>'🇿🇦'],
            ['iso'=>'TR','name'=>'Turkey','dial'=>'90','flag'=>'🇹🇷'],
            ['iso'=>'RU','name'=>'Russia','dial'=>'7','flag'=>'🇷🇺'],
            ['iso'=>'BR','name'=>'Brazil','dial'=>'55','flag'=>'🇧🇷'],
        ];
    }
}

if (! function_exists('phone_compose')) {
    /**
     * Combine a country dial code + a national number into clean international
     * format: phone_compose('94', '0771234567') => "+94771234567".
     */
    function phone_compose(?string $dial, ?string $national): string
    {
        $dial = preg_replace('/\D/', '', (string) $dial);
        $nat  = ltrim(preg_replace('/\D/', '', (string) $national), '0');
        if ($nat === '') return '';
        return '+' . $dial . $nat;
    }
}

if (! function_exists('phone_split')) {
    /**
     * Split a stored mobile back into ['dial','national'] for editing.
     * Best-effort: peels a known dial code from international numbers, otherwise
     * treats a leading-0 number as local under $defaultDial.
     */
    function phone_split(?string $mobile, string $defaultDial = '94'): array
    {
        $raw    = trim((string) $mobile);
        $digits = preg_replace('/\D/', '', $raw);
        if ($digits === '') return ['dial' => $defaultDial, 'national' => ''];

        $hadIntl = str_starts_with($raw, '+') || str_starts_with($digits, '00');
        if (str_starts_with($digits, '00')) $digits = substr($digits, 2);

        $isLocalZero = ! $hadIntl && str_starts_with($digits, '0');
        if (! $isLocalZero) {
            $best = null;
            foreach (country_dial_codes() as $c) {
                if (str_starts_with($digits, $c['dial']) && (strlen($digits) - strlen($c['dial'])) >= 7) {
                    if ($best === null || strlen($c['dial']) > strlen($best)) $best = $c['dial'];
                }
            }
            if ($best !== null) return ['dial' => $best, 'national' => substr($digits, strlen($best))];
        }
        return ['dial' => $defaultDial, 'national' => ltrim($digits, '0')];
    }
}

if (! function_exists('auth_has')) {
    /**
     * Check whether the current session user has a permission slug
     * (e.g. 'invoices.delete'). super_admin always returns true.
     * Returns true when no user is logged in only for super_admin role.
     */
    function auth_has(string $perm): bool
    {
        $role = session('user.role');
        if ($role === 'super_admin') return true;
        $perms = session('user.perms') ?: [];
        return in_array($perm, $perms, true);
    }
}

if (! function_exists('auth_role_is')) {
    /** Quick role check. */
    function auth_role_is(string ...$roles): bool
    {
        return in_array((string) session('user.role'), $roles, true);
    }
}
