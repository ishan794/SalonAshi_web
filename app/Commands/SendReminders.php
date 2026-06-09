<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Modules\Settings\Models\SettingModel;

/**
 * Sends appointment reminders for bookings starting within the configured lead window.
 *
 * Run from cron, e.g. every 15 minutes:
 *   star/15 * * * *  cd /var/www/saloncms && php spark reminders:send >> writable/logs/reminders.log 2>&1
 */
class SendReminders extends BaseCommand
{
    protected $group       = 'SalonCMS';
    protected $name        = 'reminders:send';
    protected $description = 'Email appointment reminders for upcoming bookings (cron).';

    public function run(array $params)
    {
        $s = new SettingModel();
        if ($s->get('reminders_enabled') !== '1') {
            CLI::write('Reminders are disabled in Settings → Cron. Nothing to do.', 'yellow');
            return;
        }

        $leadHours = max(1, (int) ($s->get('reminders_lead_hours') ?: 24));
        $salon     = $s->get('salon_name', 'SalonCMS');
        $fromEmail = $s->get('smtp_from_email') ?: $s->get('biz_email');
        $phone     = $s->get('biz_phone');

        $now    = date('Y-m-d H:i:s');
        $windowEnd = date('Y-m-d H:i:s', time() + $leadHours * 3600);

        // Upcoming, not-yet-reminded, confirmed/pending appts inside the lead window, with a customer email.
        $rows = db_connect()->table('appointments a')
            ->select('a.id, a.code, a.start_at, a.end_at, c.full_name, c.email, c.mobile, st.full_name AS staff_name')
            ->join('customers c', 'c.id = a.customer_id', 'left')
            ->join('staff st', 'st.id = a.staff_id', 'left')
            ->whereIn('a.status', ['pending', 'confirmed'])
            ->where('a.reminded_at', null)
            ->where('a.start_at >=', $now)
            ->where('a.start_at <=', $windowEnd)
            ->get()->getResultArray();

        if (! $rows) {
            CLI::write("No appointments needing reminders in the next {$leadHours}h.", 'green');
            return;
        }

        $sent = 0; $skipped = 0;
        $mailer = \Config\Services::email();
        foreach ($rows as $a) {
            if (empty($a['email'])) { $skipped++; continue; }
            try {
                $when = date('l, F j \a\t H:i', strtotime($a['start_at']));
                $mailer->clear();
                $mailer->setFrom($fromEmail ?: $a['email'], $salon);
                $mailer->setTo($a['email']);
                $mailer->setSubject('Reminder: your appointment at ' . $salon . ' — ' . date('M j, H:i', strtotime($a['start_at'])));
                $mailer->setMessage(
                    "Hi {$a['full_name']},\n\n" .
                    "This is a friendly reminder of your upcoming appointment:\n\n" .
                    "  Booking:  {$a['code']}\n" .
                    "  When:     {$when}\n" .
                    ($a['staff_name'] ? "  Stylist:  {$a['staff_name']}\n" : '') .
                    "\nWe look forward to seeing you!" .
                    ($phone ? "\n\nNeed to change it? Call us at {$phone}" : '') .
                    "\n\n— {$salon}"
                );
                if ($mailer->send()) {
                    db_connect()->table('appointments')->where('id', $a['id'])->update(['reminded_at' => date('Y-m-d H:i:s')]);
                    $sent++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                log_message('error', 'Reminder send failed for appt ' . $a['id'] . ': ' . $e->getMessage());
                $skipped++;
            }
        }

        CLI::write("Reminders: {$sent} sent, {$skipped} skipped (no email / send failed).", 'green');
    }
}
