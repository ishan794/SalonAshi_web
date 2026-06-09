<?php

namespace App\Modules\Settings\Controllers;

use App\Controllers\BaseController;
use App\Modules\Settings\Models\SettingModel;
use CodeIgniter\HTTP\RedirectResponse;

class SettingsController extends BaseController
{
    private SettingModel $s;

    public function __construct()
    {
        $this->s = new SettingModel();
    }

    // ───── tab routes ─────
    public function index()       { return redirect()->to('/admin/settings/general'); }
    public function general()     { return $this->render('general',     'General Settings'); }
    public function appointments(){ return $this->render('appointments','Appointment Settings'); }

    public function saveAppointments(): RedirectResponse
    {
        $in = $this->request->getPost();
        $slot = (int) ($in['appt_slot_interval'] ?? 15);
        if (! in_array($slot, [5, 10, 15, 20, 30, 60], true)) $slot = 15;

        $this->s->setMany([
            'appt_default_duration'  => (string) max(5, min(480, (int) ($in['appt_default_duration'] ?? 30))),
            'appt_slot_interval'     => (string) $slot,
            'appt_day_start'         => preg_match('/^\d{2}:\d{2}$/', (string)($in['appt_day_start'] ?? '')) ? $in['appt_day_start'] : '09:00',
            'appt_day_end'           => preg_match('/^\d{2}:\d{2}$/', (string)($in['appt_day_end'] ?? '')) ? $in['appt_day_end'] : '19:00',
            'appt_buffer_min'        => (string) max(0, min(120, (int) ($in['appt_buffer_min'] ?? 0))),
            'appt_lead_min'          => (string) max(0, (int) ($in['appt_lead_min'] ?? 0)),
            'appt_max_advance_days'  => (string) max(0, (int) ($in['appt_max_advance_days'] ?? 60)),
            'appt_default_status'    => in_array(($in['appt_default_status'] ?? 'pending'), ['pending','confirmed'], true) ? $in['appt_default_status'] : 'pending',
        ]);
        return redirect()->to('/admin/settings/appointments')->with('flash_success', 'Appointment settings saved.');
    }
    public function business()    { return $this->render('business',    'Business & Logo'); }
    public function smtp()        { return $this->render('smtp',        'SMTP / Email'); }
    public function cron()        { return $this->render('cron',        'Scheduled Tasks (Cron)'); }
    public function updates()     { return $this->render('updates',     'System Updates'); }
    public function users()       { return $this->render('users',       'Users'); }
    public function roles()       { return $this->render('roles',       'Roles'); }
    public function permissions() { return $this->render('permissions', 'Role Permissions'); }
    public function loyalty()     { return $this->render('loyalty',     'Loyalty Program'); }
    public function frontend()    { return $this->render('frontend',    'Public Site Layout'); }
    public function pages()       { return $this->render('pages',       'Public Pages Content'); }
    public function seo()         { return $this->render('seo',         'SEO &amp; Metadata'); }
    public function integrations(){ return $this->render('integrations','Integrations'); }
    public function gateways()     { return $this->render('gateways',     'Payment Gateways'); }

    public function saveSeo(): \CodeIgniter\HTTP\RedirectResponse
    {
        $pages = ['home','services','book','about','team','contact','terms','privacy','refund'];
        $payload = [
            'seo_default_title'       => trim((string) $this->request->getPost('seo_default_title')),
            'seo_default_description' => trim((string) $this->request->getPost('seo_default_description')),
            'seo_default_keywords'    => trim((string) $this->request->getPost('seo_default_keywords')),
            'seo_default_robots'      => $this->request->getPost('seo_default_robots') ?: 'index, follow',
            'seo_twitter_handle'      => trim((string) $this->request->getPost('seo_twitter_handle')),
        ];
        foreach ($pages as $p) {
            $payload['seo_' . $p . '_title']       = trim((string) $this->request->getPost('seo_' . $p . '_title'));
            $payload['seo_' . $p . '_description'] = trim((string) $this->request->getPost('seo_' . $p . '_description'));
            $payload['seo_' . $p . '_keywords']    = trim((string) $this->request->getPost('seo_' . $p . '_keywords'));
        }

        // Handle OG image upload / removal
        if ($this->request->getPost('seo_og_image_remove')) {
            $old = $this->s->get('seo_default_og_image');
            if ($old && is_file(FCPATH . 'uploads/' . $old)) @unlink(FCPATH . 'uploads/' . $old);
            $payload['seo_default_og_image'] = '';
        }
        $file = $this->request->getFile('seo_og_image');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $name = 'seo_og_' . time() . '.' . $file->getExtension();
            $file->move(FCPATH . 'uploads', $name, true);
            $payload['seo_default_og_image'] = $name;
        }

        $this->s->setMany($payload);
        return redirect()->to('/admin/settings/seo')->with('flash_success', 'SEO settings saved.');
    }

    public function saveIntegrations(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->s->setMany([
            'google_place_id'        => trim((string) $this->request->getPost('google_place_id')),
            'google_places_api_key'  => trim((string) $this->request->getPost('google_places_api_key')),
            'reviews_auto_approve'   => $this->request->getPost('reviews_auto_approve') ? '1' : '0',
        ]);
        return redirect()->to('/admin/settings/integrations')->with('flash_success', 'Integrations saved.');
    }

    public function saveGateways(): \CodeIgniter\HTTP\RedirectResponse
    {
        $in = $this->request->getPost();
        
        $payload = [
            'payhere_enabled'     => !empty($in['payhere_enabled']) ? '1' : '0',
            'payhere_sandbox'     => !empty($in['payhere_sandbox']) ? '1' : '0',
            'payhere_merchant_id' => trim((string)($in['payhere_merchant_id'] ?? '')),
            'payhere_app_id'      => trim((string)($in['payhere_app_id'] ?? '')),
            
            'onepay_enabled'      => !empty($in['onepay_enabled']) ? '1' : '0',
            'onepay_sandbox'      => !empty($in['onepay_sandbox']) ? '1' : '0',
            'onepay_merchant_id'  => trim((string)($in['onepay_merchant_id'] ?? '')),
            'onepay_app_id'       => trim((string)($in['onepay_app_id'] ?? '')),
            
            'webxpay_enabled'     => !empty($in['webxpay_enabled']) ? '1' : '0',
            'webxpay_sandbox'     => !empty($in['webxpay_sandbox']) ? '1' : '0',
            'webxpay_merchant_id' => trim((string)($in['webxpay_merchant_id'] ?? '')),

            'genie_enabled'       => !empty($in['genie_enabled']) ? '1' : '0',
            'genie_sandbox'       => !empty($in['genie_sandbox']) ? '1' : '0',
            'genie_merchant_id'   => trim((string)($in['genie_merchant_id'] ?? '')),
        ];

        $masked = '••••••••••••••••••••••••';
        
        $payhereSecret = trim((string)($in['payhere_merchant_secret'] ?? ''));
        if ($payhereSecret !== $masked) {
            $payload['payhere_merchant_secret'] = $payhereSecret;
        }

        $payhereAppSecret = trim((string)($in['payhere_app_secret'] ?? ''));
        if ($payhereAppSecret !== $masked) {
            $payload['payhere_app_secret'] = $payhereAppSecret;
        }

        $onepayApiKey = trim((string)($in['onepay_api_key'] ?? ''));
        if ($onepayApiKey !== $masked) {
            $payload['onepay_api_key'] = $onepayApiKey;
        }

        $onepayHashSalt = trim((string)($in['onepay_hash_salt'] ?? ''));
        if ($onepayHashSalt !== $masked) {
            $payload['onepay_hash_salt'] = $onepayHashSalt;
        }

        $webxpayApiKey = trim((string)($in['webxpay_api_key'] ?? ''));
        if ($webxpayApiKey !== $masked) {
            $payload['webxpay_api_key'] = $webxpayApiKey;
        }

        $genieApiKey = trim((string)($in['genie_api_key'] ?? ''));
        if ($genieApiKey !== $masked) {
            $payload['genie_api_key'] = $genieApiKey;
        }

        $genieMerchantSecret = trim((string)($in['genie_merchant_secret'] ?? ''));
        if ($genieMerchantSecret !== $masked) {
            $payload['genie_merchant_secret'] = $genieMerchantSecret;
        }

        $this->s->setMany($payload);
        return redirect()->to('/admin/settings/gateways')->with('flash_success', 'Payment gateway settings updated.');
    }

    public function savePages(): \CodeIgniter\HTTP\RedirectResponse
    {
        $keys = ['about', 'terms', 'privacy', 'refund'];
        $payload = [];
        foreach ($keys as $k) {
            $payload['page_' . $k . '_title']   = (string) $this->request->getPost('page_' . $k . '_title');
            // Content allows HTML — we keep it raw on purpose. Output is rendered as-is on the public page,
            // which is owner-edited content (the only people with access here are authenticated admins).
            $payload['page_' . $k . '_content'] = (string) $this->request->getPost('page_' . $k . '_content');
        }
        $this->s->setMany($payload);
        return redirect()->to('/admin/settings/pages')->with('flash_success', 'Page content saved.');
    }

    public function saveFrontend(): \CodeIgniter\HTTP\RedirectResponse
    {
        $style = $this->request->getPost('frontend_layout_style');
        if (! in_array($style, ['wide','boxed','centered'], true)) $style = 'wide';
        $width = (string) $this->request->getPost('frontend_container_width');
        $allowedWidths = ['max-w-5xl','max-w-6xl','max-w-7xl','max-w-[1440px]','max-w-full'];
        if (! in_array($width, $allowedWidths, true)) $width = 'max-w-7xl';

        // Languages
        $supported    = ['en', 'si', 'ta'];
        $defaultLang  = (string) $this->request->getPost('salon_default_lang');
        if (! in_array($defaultLang, $supported, true)) $defaultLang = 'en';

        $enabledIn    = (array) $this->request->getPost('frontend_enabled_langs') ?: [];
        $enabledLangs = array_values(array_intersect($supported, $enabledIn));
        if (! in_array('en', $enabledLangs, true)) array_unshift($enabledLangs, 'en'); // English always on
        // Ensure default is among enabled
        if (! in_array($defaultLang, $enabledLangs, true)) $defaultLang = 'en';

        $this->s->setMany([
            'frontend_layout_style'    => $style,
            'frontend_container_width' => $width,
            'salon_default_lang'       => $defaultLang,
            'frontend_enabled_langs'   => implode(',', $enabledLangs),
        ]);
        return redirect()->to('/admin/settings/frontend')->with('flash_success', 'Public site layout updated.');
    }

    public function saveLoyalty(): \CodeIgniter\HTTP\RedirectResponse
    {
        $in = $this->request->getPost();
        $this->s->setMany([
            'loyalty_enabled'            => empty($in['loyalty_enabled']) ? '0' : '1',
            'loyalty_earn_per_lkr'       => (string) (float) ($in['loyalty_earn_per_lkr']     ?? '0.01'),
            'loyalty_redeem_value'       => (string) (float) ($in['loyalty_redeem_value']     ?? '0.5'),
            'loyalty_min_redeem_pts'     => (string) (int)   ($in['loyalty_min_redeem_pts']   ?? '50'),
            'loyalty_tier_silver_pts'    => (string) (int)   ($in['loyalty_tier_silver_pts']  ?? '500'),
            'loyalty_tier_gold_pts'      => (string) (int)   ($in['loyalty_tier_gold_pts']    ?? '1500'),
            'loyalty_tier_platinum_pts'  => (string) (int)   ($in['loyalty_tier_platinum_pts']?? '5000'),
            'loyalty_tier_silver_disc'   => (string) (float) ($in['loyalty_tier_silver_disc'] ?? '5'),
            'loyalty_tier_gold_disc'     => (string) (float) ($in['loyalty_tier_gold_disc']   ?? '10'),
            'loyalty_tier_platinum_disc' => (string) (float) ($in['loyalty_tier_platinum_disc']?? '15'),
        ]);
        return redirect()->to('/admin/settings/loyalty')->with('flash_success', 'Loyalty settings saved.');
    }

    private function render(string $tab, string $title)
    {
        return view('layout/admin', [
            'title'   => 'Settings · ' . $title,
            'content' => view('App\Modules\Settings\Views\layout', [
                'active'  => $tab,
                'subview' => 'App\Modules\Settings\Views\tab_' . $tab,
                's'       => $this->s,
            ]),
        ]);
    }

    // ───── save handlers ─────
    public function saveGeneral(): RedirectResponse
    {
        $rules = [
            'salon_name' => 'required|max_length[120]',
            'salon_currency' => 'required|max_length[10]',
            'salon_tax_pct' => 'permit_empty|decimal',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('flash_error', 'Please fix the errors below.');
        }
        $in = $this->request->getPost();
        $this->s->setMany([
            'salon_name'           => $in['salon_name'] ?? '',
            'salon_currency'       => $in['salon_currency'] ?? 'LKR',
            'salon_timezone'       => $in['salon_timezone'] ?? 'Asia/Colombo',
            'salon_date_format'    => $in['salon_date_format'] ?? 'Y-m-d',
            'salon_tax_pct'        => (string)(float)($in['salon_tax_pct'] ?? 0),
            'salon_invoice_prefix' => $in['salon_invoice_prefix'] ?? 'INV-',
            'salon_appt_prefix'    => $in['salon_appt_prefix'] ?? 'APT-',
        ]);
        return redirect()->to('/admin/settings/general')->with('flash_success', 'General settings saved.');
    }

    public function saveBusiness(): RedirectResponse
    {
        $in = $this->request->getPost();
        $this->s->setMany([
            'biz_address'     => $in['biz_address'] ?? '',
            'biz_phone'       => $in['biz_phone'] ?? '',
            'biz_email'       => $in['biz_email'] ?? '',
            'biz_reg_no'      => $in['biz_reg_no'] ?? '',
            'biz_tax_id'      => $in['biz_tax_id'] ?? '',
            'biz_hours'       => $in['biz_hours'] ?? '',
            'biz_facebook'    => $in['biz_facebook'] ?? '',
            'biz_instagram'   => $in['biz_instagram'] ?? '',
            'biz_map_enabled' => empty($in['biz_map_enabled']) ? '0' : '1',
            // We intentionally keep the embed iframe as-is (unescaped) because it's owner-controlled
            // HTML from Google. It's whitelisted on output to only allow the Google iframe URL.
            'biz_map_embed'   => trim((string) ($in['biz_map_embed'] ?? '')),
            // WhatsApp widget
            'whatsapp_enabled'          => empty($in['whatsapp_enabled']) ? '0' : '1',
            // Strip everything but digits — wa.me only accepts plain digits.
            'whatsapp_number'           => preg_replace('/\D+/', '', (string) ($in['whatsapp_number'] ?? '')),
            'whatsapp_position'         => in_array(($in['whatsapp_position'] ?? ''), ['bottom-left','bottom-right'], true) ? $in['whatsapp_position'] : 'bottom-right',
            'whatsapp_default_message'  => trim((string) ($in['whatsapp_default_message'] ?? '')),
            'whatsapp_tooltip'          => trim((string) ($in['whatsapp_tooltip'] ?? '')),
        ]);

        // Logo upload — surface failures instead of silently skipping.
        $logo = $this->request->getFile('biz_logo');
        if ($logo && $logo->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! $logo->isValid()) {
                return redirect()->back()->with('flash_error', 'Logo upload failed: ' . $logo->getErrorString() . ' (' . $logo->getError() . ')');
            }
            $err = $this->saveImage($logo, 'biz_logo', 'logo');
            if ($err) return redirect()->back()->with('flash_error', $err);
        }
        // Favicon upload
        $fav = $this->request->getFile('biz_favicon');
        if ($fav && $fav->getError() !== UPLOAD_ERR_NO_FILE) {
            if (! $fav->isValid()) {
                return redirect()->back()->with('flash_error', 'Favicon upload failed: ' . $fav->getErrorString() . ' (' . $fav->getError() . ')');
            }
            $err = $this->saveImage($fav, 'biz_favicon', 'favicon');
            if ($err) return redirect()->back()->with('flash_error', $err);
        }
        return redirect()->to('/admin/settings/business')->with('flash_success', 'Business settings saved.');
    }

    public function saveSmtp(): RedirectResponse
    {
        $in = $this->request->getPost();
        $this->s->setMany([
            'smtp_host'       => $in['smtp_host'] ?? '',
            'smtp_port'       => (string)(int)($in['smtp_port'] ?? 587),
            'smtp_encryption' => in_array($in['smtp_encryption'] ?? '', ['', 'tls', 'ssl'], true) ? ($in['smtp_encryption'] ?? '') : '',
            'smtp_user'       => $in['smtp_user'] ?? '',
            // Only overwrite the password if the user typed something
            ...(!empty($in['smtp_pass']) ? ['smtp_pass' => $in['smtp_pass']] : []),
            'smtp_from_email' => $in['smtp_from_email'] ?? '',
            'smtp_from_name'  => $in['smtp_from_name'] ?? '',
        ]);
        return redirect()->to('/admin/settings/smtp')->with('flash_success', 'SMTP settings saved.');
    }

    public function testSmtp(): RedirectResponse
    {
        $to = trim((string) $this->request->getPost('test_to'));
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('flash_error', 'Enter a valid recipient address.');
        }

        $cfg = $this->s->group('smtp_');
        if (empty($cfg['host']) || empty($cfg['from_email'])) {
            return redirect()->back()->with('flash_error', 'Save SMTP host + from-email first.');
        }

        $email = service('email');
        $email->initialize([
            'protocol'  => 'smtp',
            'SMTPHost'  => $cfg['host'],
            'SMTPPort'  => (int) ($cfg['port'] ?? 587),
            'SMTPUser'  => $cfg['user'] ?? '',
            'SMTPPass'  => $cfg['pass'] ?? '',
            'SMTPCrypto'=> $cfg['encryption'] ?? '',
            'fromEmail' => $cfg['from_email'],
            'fromName'  => $cfg['from_name'] ?? ($this->s->get('salon_name') ?? 'SalonCMS'),
            'mailType'  => 'html',
            'charset'   => 'utf-8',
            'wordWrap'  => true,
        ]);
        $email->setFrom($cfg['from_email'], $cfg['from_name'] ?? ($this->s->get('salon_name') ?? 'SalonCMS'));
        $email->setTo($to);
        $email->setSubject('SalonCMS SMTP test');
        $email->setMessage('<p>Hi! 👋</p><p>If you can read this, your SalonCMS SMTP settings are working correctly.</p>');

        if ($email->send()) {
            return redirect()->back()->with('flash_success', 'Test email sent to ' . esc($to));
        }
        return redirect()->back()->with('flash_error', 'Send failed: ' . esc(strip_tags((string) $email->printDebugger(['headers']))));
    }

    public function saveCron(): RedirectResponse
    {
        $in = $this->request->getPost();
        $this->s->setMany([
            'cron_reminder_hours_before' => (string)(int)($in['cron_reminder_hours_before'] ?? 24),
            'cron_daily_report_time'     => $in['cron_daily_report_time'] ?? '20:00',
            'cron_backup_enabled'        => empty($in['cron_backup_enabled']) ? '0' : '1',
            'reminders_enabled'          => empty($in['reminders_enabled']) ? '0' : '1',
            // keep the command's lead-hours key in sync with the cron form field
            'reminders_lead_hours'       => (string)(int)($in['cron_reminder_hours_before'] ?? 24),
        ]);
        return redirect()->to('/admin/settings/cron')->with('flash_success', 'Cron settings saved.');
    }

    // ───── helper: validate + move an uploaded image, store filename ─────
    private function saveImage($file, string $settingKey, string $stem): ?string
    {
        $allowed = ['png','jpg','jpeg','svg','webp','ico'];
        $ext = strtolower($file->getExtension());
        if (! in_array($ext, $allowed, true)) {
            return "Unsupported file type ($ext). Allowed: " . implode(', ', $allowed);
        }
        if ($file->getSize() > 2 * 1024 * 1024) {
            return 'File too large (>2 MB).';
        }
        $dir = FCPATH . 'uploads';
        if (! is_dir($dir)) mkdir($dir, 0775, true);
        $filename = $stem . '-' . date('Ymd-His') . '.' . $ext;
        $file->move($dir, $filename, true);
        // delete previous file if present
        $prev = $this->s->get($settingKey);
        if ($prev && $prev !== $filename && is_file($dir . '/' . $prev)) {
            @unlink($dir . '/' . $prev);
        }
        $this->s->set($settingKey, $filename);
        return null;
    }

    public function saveGithubSettings(): RedirectResponse
    {
        $in = $this->request->getPost();
        
        $payload = [
            'github_repo'   => trim((string) ($in['github_repo'] ?? '')),
            'github_branch' => trim((string) ($in['github_branch'] ?? 'main')),
        ];

        $token = trim((string) ($in['github_token'] ?? ''));
        if ($token !== '••••••••••••••••••••••••') {
            $payload['github_token'] = $token;
        }

        $this->s->setMany($payload);
        return redirect()->to('/admin/settings/updates')->with('flash_success', 'GitHub auto-updater settings saved.');
    }

    public function checkGithubUpdates(): \CodeIgniter\HTTP\ResponseInterface
    {
        $repo   = $this->s->get('github_repo', 'Livezen-Technologies/saloncms');
        $token  = $this->s->get('github_token');
        $branch = $this->s->get('github_branch', 'main');

        if (empty($repo)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Please configure the repository first.']);
        }

        $url = "https://api.github.com/repos/{$repo}/commits/{$branch}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SalonCMS-Updater');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        $headers = [
            'Accept: application/vnd.github.v3+json'
        ];
        if (!empty($token)) {
            $headers[] = "Authorization: Bearer {$token}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $msg = 'Failed to fetch update info from GitHub (HTTP ' . $httpCode . ')';
            if ($httpCode === 404) {
                $msg = 'GitHub returned 404 (Not Found). For private repositories, verify your Personal Access Token (PAT) has "repo" scope (classic) or "Repository contents: Read-only" permission.';
            } elseif ($httpCode === 401 || $httpCode === 403) {
                $msg .= '. Check your Personal Access Token and rate limits.';
            }
            return $this->response->setJSON(['success' => false, 'message' => $msg]);
        }
        
        $data = json_decode($response, true);
        if (empty($data) || !isset($data['sha'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid response from GitHub.']);
        }
        
        $latestSha = $data['sha'];
        $currentSha = $this->s->get('github_version');
        
        $upToDate = ($latestSha === $currentSha);
        
        $commitMsg = $data['commit']['message'] ?? '';
        $commitDate = $data['commit']['author']['date'] ?? '';
        $author = $data['commit']['author']['name'] ?? '';
        
        return $this->response->setJSON([
            'success' => true,
            'up_to_date' => $upToDate,
            'latest_sha' => $latestSha,
            'latest_sha_short' => substr($latestSha, 0, 7),
            'current_sha' => $currentSha,
            'current_sha_short' => $currentSha ? substr($currentSha, 0, 7) : 'None',
            'message' => $upToDate ? 'Your application is up to date.' : 'A new update is available!',
            'commit' => [
                'message' => $commitMsg,
                'date' => $commitDate ? date('Y-m-d H:i:s', strtotime($commitDate)) : '',
                'author' => $author
            ]
        ]);
    }

    public function applyGithubUpdate(): \CodeIgniter\HTTP\ResponseInterface
    {
        @set_time_limit(300);

        $repo   = $this->s->get('github_repo', 'Livezen-Technologies/saloncms');
        $token  = $this->s->get('github_token');
        $branch = $this->s->get('github_branch', 'main');

        $body = $this->request->getJSON(true);
        $latestSha = $body['sha'] ?? '';

        if (empty($latestSha)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Commit SHA is required.']);
        }

        $logs = "";
        $uploadsDir = WRITEPATH . 'uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        $zipFile = $uploadsDir . 'update.zip';

        $zipUrl = "https://api.github.com/repos/{$repo}/zipball/{$latestSha}";
        $logs .= "Downloading ZIP archive from GitHub...\n";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $zipUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SalonCMS-Updater');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $headers = [
            'Accept: application/vnd.github.v3+json'
        ];
        if (!empty($token)) {
            $headers[] = "Authorization: Bearer {$token}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $zipData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($zipData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to download update archive (HTTP ' . $httpCode . ').',
                'logs' => $logs
            ]);
        }

        file_put_contents($zipFile, $zipData);
        $logs .= "ZIP archive downloaded successfully (" . number_format(strlen($zipData)) . " bytes).\n";

        $logs .= "Extracting ZIP archive...\n";
        $extractPath = $uploadsDir . 'extracted_update/';
        if (is_dir($extractPath)) {
            $this->rmdirRecursive($extractPath);
        }
        mkdir($extractPath, 0755, true);

        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            @unlink($zipFile);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to open downloaded ZIP file.',
                'logs' => $logs
            ]);
        }

        $zip->extractTo($extractPath);
        $zip->close();
        @unlink($zipFile);

        $dirs = glob($extractPath . '*', GLOB_ONLYDIR);
        if (empty($dirs)) {
            $this->rmdirRecursive($extractPath);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to find root update directory in ZIP.',
                'logs' => $logs
            ]);
        }

        $repoFolder = $dirs[0];
        $logs .= "Extracted to: " . basename($repoFolder) . "\n";

        $logs .= "Copying update files to application root...\n";
        $exclude = ['.git', '.env', 'writable', 'vendor', 'node_modules'];
        try {
            $this->copyRecursive($repoFolder, ROOTPATH, $exclude);
        } catch (\Throwable $e) {
            $this->rmdirRecursive($extractPath);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to copy update files: ' . $e->getMessage(),
                'logs' => $logs
            ]);
        }

        $this->rmdirRecursive($extractPath);
        $logs .= "Copy complete. Temporary files cleaned up.\n";

        $logs .= "Running database migrations...\n";
        try {
            $migrations = \Config\Services::migrations();
            $migrations->latest();
            $logs .= "Database migrations run successfully.\n";
        } catch (\Throwable $e) {
            $logs .= "Migration Warning: " . $e->getMessage() . "\n";
        }

        $logs .= "Clearing application cache...\n";
        $cacheDir = WRITEPATH . 'cache/';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*');
            foreach ($files as $f) {
                if (is_file($f) && basename($f) !== 'index.html') {
                    @unlink($f);
                }
            }
        }
        $logs .= "Cache cleared.\n";

        $this->s->set('github_version', $latestSha);
        $logs .= "Auto update completed successfully! Version set to: " . substr($latestSha, 0, 7) . "\n";

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Update applied successfully.',
            'logs' => $logs
        ]);
    }

    private function copyRecursive(string $src, string $dst, array $exclude = [])
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (in_array($file, $exclude, true)) {
                continue;
            }
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            if (is_dir($srcPath)) {
                $this->copyRecursive($srcPath, $dstPath, $exclude);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }

    private function rmdirRecursive(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->rmdirRecursive($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
