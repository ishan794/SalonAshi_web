<?php

namespace App\Modules\Staff\Controllers;

use App\Controllers\BaseController;
use App\Modules\Staff\Models\StaffModel;
use App\Modules\Services\Models\ServiceModel;

class StaffController extends BaseController
{
    private StaffModel $staff;
    private ServiceModel $services;

    public function __construct()
    {
        $this->staff    = new StaffModel();
        $this->services = new ServiceModel();
    }

    public function index()
    {
        $rows = $this->staff->orderBy('id','desc')->findAll();
        return view('layout/admin', [
            'title'   => 'Staff',
            'content' => view('App\Modules\Staff\Views\index', ['rows' => $rows]),
        ]);
    }

    public function create()
    {
        return view('layout/admin', [
            'title'   => 'New Staff',
            'content' => view('App\Modules\Staff\Views\form', [
                'row' => null,
                'allServices' => $this->services->activeWithCategory(),
                'assigned' => [],
            ]),
        ]);
    }

    public function store()
    {
        $data = $this->validatedInput();
        if (! $data) return redirect()->back()->withInput();
        $id = $this->staff->insert($data, true);
        $this->staff->setServices((int) $id, (array) $this->request->getPost('service_ids'));
        return redirect()->to('/admin/staff')->with('flash_success', 'Staff added.');
    }

    public function edit(int $id)
    {
        $row = $this->staff->find($id);
        if (! $row) return redirect()->to('/admin/staff');
        $assigned = array_column($this->staff->servicesFor($id), 'id');

        // Date override panel — pick a date via ?date= (defaults to today)
        $pickDate = $this->request->getGet('date');
        if (! $pickDate || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $pickDate)) {
            $pickDate = date('Y-m-d');
        }

        return view('layout/admin', [
            'title'   => 'Edit Staff — ' . $row['full_name'],
            'content' => view('App\Modules\Staff\Views\form', [
                'row'         => $row,
                'allServices' => $this->services->activeWithCategory(),
                'assigned'    => $assigned,
                'schedule'    => $this->staff->getSchedule($id),
                'timeOff'     => $this->staff->getTimeOff($id, date('Y-m-d')),
                'pickDate'    => $pickDate,
                'dateWindows' => $this->staff->getDateWindows($id, $pickDate),
                'fallbackWindows' => $this->staff->windowsForDate($id, $pickDate),
            ]),
        ]);
    }

    public function update(int $id)
    {
        $data = $this->validatedInput();
        if (! $data) return redirect()->back()->withInput();
        $this->staff->update($id, $data);
        $this->staff->setServices($id, (array) $this->request->getPost('service_ids'));

        // Save weekly schedule (always sent — 7 dow rows)
        $schedule = (array) $this->request->getPost('schedule');
        if ($schedule) $this->staff->saveSchedule($id, $schedule);

        return redirect()->to('/admin/staff/' . $id . '/edit')->with('flash_success', 'Staff updated.');
    }

    public function addTimeOff(int $id)
    {
        $date = trim((string) $this->request->getPost('off_date'));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->back()->with('flash_error', 'Pick a valid date.');
        }
        $ok = $this->staff->addTimeOff($id, $date, $this->request->getPost('reason'));
        return redirect()->back()->with($ok ? 'flash_success' : 'flash_error',
            $ok ? 'Time-off added.' : 'That date is already marked off.'
        );
    }

    public function removeTimeOff(int $id, int $offId)
    {
        $this->staff->removeTimeOff($offId, $id);
        return redirect()->back()->with('flash_success', 'Time-off removed.');
    }

    public function addDateWindow(int $id)
    {
        $date  = trim((string) $this->request->getPost('on_date'));
        $start = trim((string) $this->request->getPost('start_time'));
        $end   = trim((string) $this->request->getPost('end_time'));
        $note  = $this->request->getPost('note');

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            ! preg_match('/^\d{2}:\d{2}$/', $start) ||
            ! preg_match('/^\d{2}:\d{2}$/', $end)) {
            return redirect()->back()->with('flash_error', 'Date and times are required.');
        }
        $ok = $this->staff->addDateWindow($id, $date, $start, $end, $note);
        return redirect()->to('/admin/staff/' . $id . '/edit?date=' . $date)
            ->with($ok ? 'flash_success' : 'flash_error',
                $ok ? 'Time window added.' : 'End time must be after start time.');
    }

    public function removeDateWindow(int $id, int $windowId)
    {
        $this->staff->removeDateWindow($windowId, $id);
        $date = $this->request->getGet('date') ?: date('Y-m-d');
        return redirect()->to('/admin/staff/' . $id . '/edit?date=' . $date)
            ->with('flash_success', 'Window removed.');
    }

    public function resetDateWindows(int $id)
    {
        $date = $this->request->getPost('on_date') ?: date('Y-m-d');
        $this->staff->clearDateWindows($id, $date);
        return redirect()->to('/admin/staff/' . $id . '/edit?date=' . $date)
            ->with('flash_success', 'Reverted to weekly schedule.');
    }

    public function calendar(int $id)
    {
        $row = $this->staff->find($id);
        if (! $row) return redirect()->to('/admin/staff');

        $month = $this->request->getGet('month');
        if (! $month || ! preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');
        $monthStart = $month . '-01';
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        $appts = db_connect()->table('appointments a')
            ->select('a.id, a.code, a.start_at, a.end_at, a.status, c.full_name AS customer_name')
            ->join('customers c', 'c.id = a.customer_id', 'left')
            ->where('a.staff_id', $id)
            ->where('a.start_at >=', $monthStart . ' 00:00:00')
            ->where('a.start_at <=', $monthEnd   . ' 23:59:59')
            ->orderBy('a.start_at', 'asc')
            ->get()->getResultArray();

        $apptsByDay = [];
        foreach ($appts as $a) {
            $d = date('Y-m-d', strtotime($a['start_at']));
            $apptsByDay[$d][] = [
                'id'            => (int) $a['id'],
                'code'          => $a['code'],
                'time'          => date('H:i', strtotime($a['start_at'])),
                'end'           => date('H:i', strtotime($a['end_at'])),
                'status'        => $a['status'],
                'customer_name' => $a['customer_name'] ?: 'Walk-in',
            ];
        }

        // Time-off in the same month for visual overlay
        $offDates = array_column(
            db_connect()->table('staff_time_off')
                ->where('staff_id', $id)
                ->where('off_date >=', $monthStart)
                ->where('off_date <=', $monthEnd)
                ->get()->getResultArray(),
            'off_date'
        );

        return view('layout/admin', [
            'title'   => $row['full_name'] . ' — Calendar',
            'content' => view('App\Modules\Staff\Views\calendar', [
                'staff'      => $row,
                'month'      => $month,
                'monthStart' => $monthStart,
                'apptsByDay' => $apptsByDay,
                'offDates'   => $offDates,
                'totalMonth' => count($appts),
            ]),
        ]);
    }

    public function destroy(int $id)
    {
        $this->staff->delete($id);
        return redirect()->to('/admin/staff')->with('flash_success', 'Staff removed.');
    }

    /** Revenue tab — date-range KPIs + paid services list. */
    public function revenue(int $id)
    {
        $staff = $this->staff->find($id);
        if (! $staff) return redirect()->to('/admin/staff')->with('flash_error', 'Stylist not found.');

        [$from, $to] = $this->dateRange();
        [$f, $t]     = [$from . ' 00:00:00', $to . ' 23:59:59'];
        $db          = db_connect();

        // Paid services for this stylist (revenue source-of-truth = payments).
        $lines = $db->table('payments p')
            ->select('p.paid_at, p.amount, p.method, i.invoice_no, c.full_name AS customer_name')
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('customers c', 'c.id = i.customer_id', 'left')
            ->join('appointments ia', 'ia.id = i.appointment_id', 'left')
            ->where('p.status', 'success')
            ->where('p.paid_at >=', $f)->where('p.paid_at <=', $t)
            ->groupStart()->where('i.staff_id', $id)->orWhere('ia.staff_id', $id)->groupEnd()
            ->orderBy('p.paid_at', 'DESC')
            ->get()->getResultArray();

        // Appointment counts in same range
        $apptStats = $db->table('appointments')
            ->select("COUNT(*) total,
                      SUM(status='completed') completed,
                      SUM(status='no_show') no_shows")
            ->where('staff_id', $id)
            ->where('start_at >=', $f)->where('start_at <=', $t)
            ->get()->getRowArray();

        $revenue = array_sum(array_map(fn($l) => (float) $l['amount'], $lines));
        $avgTicket = ! empty($lines) ? $revenue / count($lines) : 0;

        return view('layout/admin', [
            'title'   => $staff['full_name'] . ' — Revenue',
            'content' => view('App\Modules\Staff\Views\revenue', [
                'staff' => $staff, 'lines' => $lines,
                'from' => $from, 'to' => $to,
                'revenue' => $revenue, 'avgTicket' => $avgTicket,
                'apptStats' => $apptStats,
            ]),
        ]);
    }

    /** Payouts tab — commission summary + PDF download link. */
    public function payouts(int $id)
    {
        $staff = $this->staff->find($id);
        if (! $staff) return redirect()->to('/admin/staff')->with('flash_error', 'Stylist not found.');

        [$from, $to] = $this->dateRange();
        [$f, $t]     = [$from . ' 00:00:00', $to . ' 23:59:59'];
        $db          = db_connect();

        // Group payments by month for the payout history view
        $rows = $db->table('payments p')
            ->select("DATE_FORMAT(p.paid_at, '%Y-%m') AS period,
                      COUNT(p.id) AS payments_n,
                      SUM(p.amount) AS revenue")
            ->join('invoices i', 'i.id = p.invoice_id')
            ->join('appointments ia', 'ia.id = i.appointment_id', 'left')
            ->where('p.status', 'success')
            ->where('p.paid_at >=', $f)->where('p.paid_at <=', $t)
            ->groupStart()->where('i.staff_id', $id)->orWhere('ia.staff_id', $id)->groupEnd()
            ->groupBy("DATE_FORMAT(p.paid_at, '%Y-%m')")
            ->orderBy('period', 'DESC')
            ->get()->getResultArray();

        $pct = (float) ($staff['commission_pct'] ?? 0);
        foreach ($rows as &$r) {
            $r['commission'] = round((float) $r['revenue'] * $pct / 100, 2);
        }
        unset($r);

        $totalRevenue    = array_sum(array_column($rows, 'revenue'));
        $totalCommission = array_sum(array_column($rows, 'commission'));

        // Recorded payouts (actual payments made to this stylist).
        $payouts = (new \App\Modules\Staff\Models\StaffPayoutModel())->forStaff($id);

        return view('layout/admin', [
            'title'   => $staff['full_name'] . ' — Payouts',
            'content' => view('App\Modules\Staff\Views\payouts', [
                'staff' => $staff, 'rows' => $rows,
                'from' => $from, 'to' => $to,
                'totalRevenue' => $totalRevenue, 'totalCommission' => $totalCommission,
                'pct' => $pct,
                'payouts' => $payouts,
            ]),
        ]);
    }

    /** Move/validate an uploaded payout slip → returns stored filename or null. */
    private function handleSlipUpload(int $staffId): ?string
    {
        $slip = $this->request->getFile('slip');
        if (! $slip || ! $slip->isValid() || $slip->hasMoved()) return null;
        $ext = strtolower($slip->getExtension() ?: pathinfo($slip->getClientName(), PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg','jpeg','png','gif','webp','pdf'], true)) {
            throw new \RuntimeException('Slip must be an image or PDF.');
        }
        if ($slip->getSize() > 5 * 1024 * 1024) {
            throw new \RuntimeException('Slip must be under 5 MB.');
        }
        $name = 'payout_' . $staffId . '_' . time() . '.' . $ext;
        $slip->move(FCPATH . 'uploads', $name, true);
        return $name;
    }

    /** Create a payout record for a stylist (optionally with a slip + mark paid). */
    public function generatePayout(int $id)
    {
        $staff = $this->staff->find($id);
        if (! $staff) return redirect()->to('/admin/staff')->with('flash_error', 'Stylist not found.');

        $amount = (float) $this->request->getPost('amount');
        if ($amount <= 0) {
            return redirect()->back()->with('flash_error', 'Enter a payout amount greater than zero.');
        }

        $dateOk = fn($v) => $v && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;

        try {
            $slipName = $this->handleSlipUpload($id);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('flash_error', $e->getMessage());
        }

        $markPaid = ! empty($this->request->getPost('mark_paid'));

        $payoutId = (new \App\Modules\Staff\Models\StaffPayoutModel())->insert([
            'staff_id'       => $id,
            'period_from'    => $dateOk($this->request->getPost('period_from')),
            'period_to'      => $dateOk($this->request->getPost('period_to')),
            'gross_revenue'  => (float) $this->request->getPost('gross_revenue') ?: 0,
            'commission_pct' => (float) ($staff['commission_pct'] ?? 0),
            'amount'         => $amount,
            'method'         => $this->request->getPost('method') ?: ($staff['payout_method'] ?? null),
            'reference'      => trim((string) $this->request->getPost('reference')) ?: null,
            'slip_path'      => $slipName,
            'notes'          => trim((string) $this->request->getPost('notes')) ?: null,
            'status'         => $markPaid ? 'paid' : 'pending',
            'paid_at'        => $markPaid ? date('Y-m-d H:i:s') : null,
            'created_by'     => session('user.id'),
        ], true);

        helper('system');
        log_action('staff.payout.create', [
            'entity_type' => 'staff', 'entity_id' => $id,
            'description' => 'Generated payout of LKR ' . number_format($amount, 2) . ' for ' . $staff['full_name'],
        ]);

        return redirect()->to('/admin/staff/' . $id . '/payouts')
            ->with('flash_success', 'Payout of LKR ' . number_format($amount, 2) . ' recorded.');
    }

    /** Attach / replace the bank slip on an existing payout. */
    public function uploadSlip(int $id, int $payoutId)
    {
        $model  = new \App\Modules\Staff\Models\StaffPayoutModel();
        $payout = $model->find($payoutId);
        if (! $payout || (int) $payout['staff_id'] !== $id) {
            return redirect()->to('/admin/staff/' . $id . '/payouts')->with('flash_error', 'Payout not found.');
        }
        try {
            $slipName = $this->handleSlipUpload($id);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('flash_error', $e->getMessage());
        }
        if (! $slipName) return redirect()->back()->with('flash_error', 'Pick a slip file to upload.');

        if (! empty($payout['slip_path']) && is_file(FCPATH . 'uploads/' . $payout['slip_path'])) {
            @unlink(FCPATH . 'uploads/' . $payout['slip_path']);
        }
        $model->update($payoutId, ['slip_path' => $slipName]);
        return redirect()->to('/admin/staff/' . $id . '/payouts')->with('flash_success', 'Payout slip uploaded.');
    }

    /** Notify the stylist (in-app + email) with their payout summary. */
    public function notifyPayout(int $id, int $payoutId)
    {
        $staff  = $this->staff->find($id);
        $model  = new \App\Modules\Staff\Models\StaffPayoutModel();
        $payout = $model->find($payoutId);
        if (! $staff || ! $payout || (int) $payout['staff_id'] !== $id) {
            return redirect()->to('/admin/staff/' . $id . '/payouts')->with('flash_error', 'Payout not found.');
        }

        helper('system');
        $s        = new \App\Modules\Settings\Models\SettingModel();
        $currency = $s->get('salon_currency', 'LKR');
        $salon    = $s->get('salon_name', 'SalonCMS');
        $amountTxt = $currency . ' ' . number_format((float) $payout['amount'], 2);
        $periodTxt = ($payout['period_from'] && $payout['period_to'])
            ? date('M j', strtotime($payout['period_from'])) . ' – ' . date('M j, Y', strtotime($payout['period_to']))
            : 'recent period';

        // In-app notification (only if the stylist has a linked user account).
        if (! empty($staff['user_id'])) {
            notify([
                'user_id' => (int) $staff['user_id'],
                'type'    => 'payout',
                'title'   => 'Payout processed · ' . $amountTxt,
                'body'    => 'Your payout for ' . $periodTxt . ' has been ' . ($payout['status'] === 'paid' ? 'paid' : 'prepared') . '.',
                'icon'    => 'hand-coins',
                'color'   => 'green',
            ]);
        }

        // Email summary (best-effort) if the stylist has an email and SMTP is configured.
        $emailed = false;
        $cfg = $s->group('smtp_');
        if (! empty($staff['email']) && filter_var($staff['email'], FILTER_VALIDATE_EMAIL)
            && ! empty($cfg['host']) && ! empty($cfg['from_email'])) {
            $body = '<p>Hi ' . esc($staff['full_name']) . ',</p>'
                  . '<p>Here is your payout summary from <strong>' . esc($salon) . '</strong>:</p>'
                  . '<table cellpadding="6" style="border-collapse:collapse">'
                  . '<tr><td>Period</td><td><strong>' . esc($periodTxt) . '</strong></td></tr>'
                  . '<tr><td>Gross revenue</td><td>' . esc($currency) . ' ' . number_format((float)$payout['gross_revenue'], 2) . '</td></tr>'
                  . '<tr><td>Commission rate</td><td>' . number_format((float)$payout['commission_pct'], 1) . '%</td></tr>'
                  . '<tr><td>Payout amount</td><td><strong>' . esc($amountTxt) . '</strong></td></tr>'
                  . '<tr><td>Method</td><td>' . esc(str_replace('_', ' ', (string)($payout['method'] ?? '—'))) . '</td></tr>'
                  . ($payout['reference'] ? '<tr><td>Reference</td><td>' . esc($payout['reference']) . '</td></tr>' : '')
                  . '<tr><td>Status</td><td>' . esc(ucfirst($payout['status'])) . '</td></tr>'
                  . '</table>'
                  . ($payout['notes'] ? '<p>' . esc($payout['notes']) . '</p>' : '')
                  . '<p>Thank you,<br>— ' . esc($salon) . '</p>';

            $email = service('email');
            $email->initialize([
                'protocol' => 'smtp', 'SMTPHost' => $cfg['host'], 'SMTPPort' => (int)($cfg['port'] ?? 587),
                'SMTPUser' => $cfg['user'] ?? '', 'SMTPPass' => $cfg['pass'] ?? '',
                'SMTPCrypto' => $cfg['encryption'] ?? '', 'mailType' => 'html', 'charset' => 'utf-8', 'wordWrap' => true,
            ]);
            $email->setFrom($cfg['from_email'], $cfg['from_name'] ?? $salon);
            $email->setTo($staff['email']);
            $email->setSubject('Your payout summary · ' . $amountTxt);
            $email->setMessage($body);

            // Attach slip if present.
            if (! empty($payout['slip_path']) && is_file(FCPATH . 'uploads/' . $payout['slip_path'])) {
                $email->attach(FCPATH . 'uploads/' . $payout['slip_path']);
            }
            $emailed = (bool) $email->send();
        }

        $model->update($payoutId, ['notified_at' => date('Y-m-d H:i:s')]);
        log_action('staff.payout.notify', [
            'entity_type' => 'staff', 'entity_id' => $id,
            'description' => 'Notified ' . $staff['full_name'] . ' of payout ' . $amountTxt,
        ]);

        $msg = 'Stylist notified.' . ($emailed ? ' Email sent to ' . esc($staff['email']) . '.' : '');
        if (! $emailed && ! empty($staff['email'])) $msg .= ' (Email not sent — check SMTP settings.)';
        if (empty($staff['user_id']) && empty($staff['email'])) {
            $msg = 'Marked as notified, but this stylist has no linked user account or email to reach.';
        }
        return redirect()->to('/admin/staff/' . $id . '/payouts')->with('flash_success', $msg);
    }

    /** Delete a payout record (and its slip file). */
    public function deletePayout(int $id, int $payoutId)
    {
        $model  = new \App\Modules\Staff\Models\StaffPayoutModel();
        $payout = $model->find($payoutId);
        if ($payout && (int) $payout['staff_id'] === $id) {
            if (! empty($payout['slip_path']) && is_file(FCPATH . 'uploads/' . $payout['slip_path'])) {
                @unlink(FCPATH . 'uploads/' . $payout['slip_path']);
            }
            $model->delete($payoutId);
            helper('system');
            log_action('staff.payout.delete', ['entity_type' => 'staff', 'entity_id' => $id, 'description' => 'Deleted a payout record']);
        }
        return redirect()->to('/admin/staff/' . $id . '/payouts')->with('flash_success', 'Payout record removed.');
    }

    /** Shared date-range helper (?preset=… or ?from=&to=). Default 30 days. */
    private function dateRange(): array
    {
        $preset = $this->request->getGet('preset') ?: '30d';
        $from   = $this->request->getGet('from');
        $to     = $this->request->getGet('to');

        if ($preset === 'custom' && $from && $to) return [$from, $to];

        return match ($preset) {
            'today' => [date('Y-m-d'), date('Y-m-d')],
            '7d'    => [date('Y-m-d', strtotime('-6 days')), date('Y-m-d')],
            'mtd'   => [date('Y-m-01'), date('Y-m-d')],
            'ytd'   => [date('Y-01-01'), date('Y-m-d')],
            default => [date('Y-m-d', strtotime('-29 days')), date('Y-m-d')],
        };
    }

    private function validatedInput(): ?array
    {
        $rules = [
            'full_name' => 'required|max_length[150]',
            'commission_pct' => 'permit_empty|decimal',
        ];
        if (! $this->validate($rules)) return null;
        $in = $this->request->getPost();

        $payoutMethods = ['', 'bank_transfer', 'cash', 'cheque', 'mobile_wallet'];
        $payoutFreqs   = ['', 'weekly', 'biweekly', 'monthly'];
        $method = in_array(($in['payout_method'] ?? ''), $payoutMethods, true) ? ($in['payout_method'] ?: null) : null;
        $freq   = in_array(($in['payout_frequency'] ?? ''), $payoutFreqs, true) ? ($in['payout_frequency'] ?: null) : null;

        return [
            'branch_id'      => session('user.branch_id') ?: 1,
            'full_name'      => $in['full_name'],
            'role'           => $in['role'] ?? null,
            'mobile'         => $in['mobile'] ?? null,
            'email'          => $in['email'] ?? null,
            'commission_pct' => isset($in['commission_pct']) ? (float) $in['commission_pct'] : 0,
            'working_hours'  => $in['working_hours'] ?? null,
            'is_active'      => !empty($in['is_active']) ? 1 : 0,
            // Payout & bank details
            'bank_name'         => trim((string)($in['bank_name'] ?? '')) ?: null,
            'bank_account_name' => trim((string)($in['bank_account_name'] ?? '')) ?: null,
            'bank_account_no'   => trim((string)($in['bank_account_no'] ?? '')) ?: null,
            'bank_branch'       => trim((string)($in['bank_branch'] ?? '')) ?: null,
            'bank_code'         => trim((string)($in['bank_code'] ?? '')) ?: null,
            'payout_method'     => $method,
            'payout_frequency'  => $freq,
            'payout_notes'      => trim((string)($in['payout_notes'] ?? '')) ?: null,
        ];
    }
}
