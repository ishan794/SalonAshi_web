<?php

namespace App\Modules\CustomerPortal\Controllers;

use App\Controllers\BaseController;
use App\Modules\CustomerPortal\Models\CustomerOtpModel;
use App\Modules\Customers\Models\CustomerModel;
use App\Modules\Settings\Models\SettingModel;
use App\Modules\Appointments\Models\AppointmentModel;
use App\Modules\Billing\Models\InvoiceModel;

class PortalController extends BaseController
{
    private SettingModel $s;

    public function __construct()
    {
        $this->s = new SettingModel();
    }

    /** ── Step 1: enter mobile to receive OTP ── */
    public function index()
    {
        // If already signed in, jump straight to dashboard.
        if (session()->get('portal_customer_id')) return redirect()->to('/portal/dashboard');

        return view('App\Modules\Frontend\Views\layout', [
            'title'   => 'My account — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\CustomerPortal\Views\login',
            's'       => $this->s,
            'data'    => ['email' => session()->getFlashdata('portal_email_hint')],
            'page'    => 'portal',
        ]);
    }

    /** POST /portal/request-otp — look up by mobile, email a code. */
    public function requestOtp()
    {
        $emailRaw = (string) $this->request->getPost('email');
        $email    = trim(strtolower($emailRaw));
        if ($email === '') {
            return redirect()->to('/portal')->with('flash_error', 'Enter your email address.');
        }

        $customer = (new CustomerModel())->where('email', $email)->first();

        // Blocked customers are treated like non-existent accounts (no OTP, generic message).
        if (! $customer || ($customer['status'] ?? 'active') === 'blocked') {
            // Don't leak which emails exist — show generic success and just refuse to issue OTP.
            return redirect()->to('/portal/verify')
                ->with('flash_success', 'If we have an account for that email, a code has been sent.')
                ->with('portal_email_hint', $emailRaw);
        }

        $otps = new CustomerOtpModel();
        $code = $otps->issue((int) $customer['id'], $this->request->getIPAddress());

        // Stash customer_id so /portal/verify knows who's verifying.
        session()->setFlashdata('portal_pending_customer_id', (int) $customer['id']);
        session()->setFlashdata('portal_email_hint',        $emailRaw);
        session()->set('portal_pending_customer_id_persist', (int) $customer['id']); // persists across the redirect

        // Send via configured SMTP (best-effort).
        try {
            $mailer = \Config\Services::email();
            $mailer->setFrom($this->s->get('smtp_from_email') ?: $email, $this->s->get('salon_name', 'SalonCMS'));
            $mailer->setTo($email);
            $mailer->setSubject('Your sign-in code: ' . $code);
            $mailer->setMessage("Hi {$customer['full_name']},\n\nYour one-time sign-in code is: {$code}\n\nIt expires in 10 minutes.\n\nIf you didn't request this, you can safely ignore this email.");
            $mailer->send();
        } catch (\Throwable $e) {
            log_message('error', 'Portal OTP email failed: ' . $e->getMessage());
        }

        return redirect()->to('/portal/verify')->with('flash_success', 'We emailed you a 6-digit code. Check your inbox.');
    }

    /** GET /portal/verify — enter the code form. */
    public function verifyForm()
    {
        if (session()->get('portal_customer_id')) return redirect()->to('/portal/dashboard');
        if (! session()->get('portal_pending_customer_id_persist')) return redirect()->to('/portal');

        return view('App\Modules\Frontend\Views\layout', [
            'title'   => 'Enter code — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\CustomerPortal\Views\verify',
            's'       => $this->s,
            'data'    => [],
            'page'    => 'portal',
        ]);
    }

    public function verify()
    {
        $pendingId = (int) session()->get('portal_pending_customer_id_persist');
        $code      = (string) $this->request->getPost('code');
        if (! $pendingId) return redirect()->to('/portal')->with('flash_error', 'Session expired — please start again.');

        $otps = new CustomerOtpModel();
        $row  = $otps->findValid($pendingId, $code);
        if (! $row) return redirect()->to('/portal/verify')->with('flash_error', 'Wrong or expired code. Try again.');

        $otps->consume((int) $row['id']);
        session()->set('portal_customer_id', $pendingId);
        session()->remove('portal_pending_customer_id_persist');

        return redirect()->to('/portal/dashboard');
    }

    public function logout()
    {
        session()->remove('portal_customer_id');
        return redirect()->to('/portal')->with('flash_success', 'You\'ve been signed out.');
    }

    /** ── Step 3: dashboard ── */
    public function dashboard()
    {
        $customerId = (int) session()->get('portal_customer_id');
        $customer   = (new CustomerModel())->find($customerId);
        if (! $customer) { session()->remove('portal_customer_id'); return redirect()->to('/portal'); }

        $appts = (new AppointmentModel())
            ->select('appointments.*, staff.full_name AS staff_name')
            ->join('staff', 'staff.id = appointments.staff_id', 'left')
            ->where('appointments.customer_id', $customerId)
            ->orderBy('appointments.start_at', 'DESC')
            ->findAll(50);

        $now = date('Y-m-d H:i:s');
        $upcoming = []; $past = [];
        foreach ($appts as $a) {
            if ($a['start_at'] >= $now && ! in_array($a['status'], ['cancelled','no_show'], true)) $upcoming[] = $a;
            else $past[] = $a;
        }
        // Sort upcoming chronologically (soonest first)
        usort($upcoming, fn($a, $b) => strcmp($a['start_at'], $b['start_at']));

        // Last treatment = most recent COMPLETED appt with its services
        $lastTreatment = null;
        foreach ($past as $a) {
            if (($a['status'] ?? '') === 'completed') {
                $services = db_connect()->table('appointment_services')
                    ->select('appointment_services.*, services.name AS service_name')
                    ->join('services', 'services.id = appointment_services.service_id', 'left')
                    ->where('appointment_services.appointment_id', $a['id'])
                    ->get()->getResultArray();
                $lastTreatment = ['appt' => $a, 'services' => $services];
                break;
            }
        }

        $invoices = (new InvoiceModel())
            ->where('customer_id', $customerId)
            ->orderBy('id', 'DESC')
            ->findAll(20);

        $payments = db_connect()->table('payments')
            ->select('payments.*, invoices.code AS invoice_code')
            ->join('invoices', 'invoices.id = payments.invoice_id', 'left')
            ->where('invoices.customer_id', $customerId)
            ->orderBy('payments.paid_at', 'DESC')
            ->limit(20)
            ->get()->getResultArray();

        return view('App\Modules\Frontend\Views\layout', [
            'title'   => 'My account — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\CustomerPortal\Views\dashboard',
            's'       => $this->s,
            'data'    => compact('customer', 'upcoming', 'past', 'lastTreatment', 'invoices', 'payments'),
            'page'    => 'portal',
        ]);
    }

    /** GET /portal/invoice/{id} — invoice detail with line items + payment history. */
    public function invoice(int $id)
    {
        $customerId = (int) session()->get('portal_customer_id');
        $invModel   = new InvoiceModel();
        $inv        = $invModel->where('id', $id)->where('customer_id', $customerId)->first();
        if (! $inv) return redirect()->to('/portal/dashboard')->with('flash_error', 'Invoice not found.');

        $items = db_connect()->table('invoice_items')->where('invoice_id', $id)->get()->getResultArray();
        $pays  = db_connect()->table('payments')->where('invoice_id', $id)->orderBy('paid_at', 'DESC')->get()->getResultArray();

        return view('App\Modules\Frontend\Views\layout', [
            'title'   => 'Invoice ' . $inv['code'],
            'subview' => 'App\Modules\CustomerPortal\Views\invoice',
            's'       => $this->s,
            'data'    => ['invoice' => $inv, 'items' => $items, 'payments' => $pays],
            'page'    => 'portal',
        ]);
    }

    /** GET /portal/availability — slot list for the reschedule picker (customer-scoped, read-only). */
    public function availability()
    {
        if (! session()->get('portal_customer_id')) return $this->response->setStatusCode(403)->setJSON(['ok' => false]);

        $staffId  = (int) $this->request->getGet('staff_id');
        $date     = $this->request->getGet('date') ?: date('Y-m-d');
        $duration = max(15, (int) $this->request->getGet('duration'));
        $step     = 15;

        if (! $staffId || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->response->setJSON(['ok' => false, 'slots' => []]);
        }

        $staffModel = new \App\Modules\Staff\Models\StaffModel();
        $windows = $staffModel->windowsForDate($staffId, $date);
        if ($windows === null) return $this->response->setJSON(['ok' => true, 'slots' => [], 'isOff' => true]);

        $existing = db_connect()->table('appointments')
            ->where('staff_id', $staffId)
            ->whereIn('status', ['pending','confirmed','checked_in','in_progress'])
            ->where('start_at <', $date . ' 23:59:59')
            ->where('end_at   >', $date . ' 00:00:00')
            ->get()->getResultArray();
        $busy = array_map(fn($a) => ['start' => strtotime($a['start_at']), 'end' => strtotime($a['end_at'])], $existing);

        $now = time(); $slots = []; $seen = [];
        foreach ($windows as [$startHM, $endHM]) {
            $winStart = strtotime("$date $startHM");
            $winEnd   = strtotime("$date $endHM");
            for ($t = $winStart; $t + $duration * 60 <= $winEnd; $t += $step * 60) {
                $label = date('H:i', $t);
                if (isset($seen[$label])) continue;
                $seen[$label] = true;
                if ($t + $duration * 60 <= $now) { $slots[] = ['time' => $label, 'available' => false]; continue; }
                $conflict = false;
                foreach ($busy as $b) { if ($t < $b['end'] && $t + $duration * 60 > $b['start']) { $conflict = true; break; } }
                $slots[] = ['time' => $label, 'available' => ! $conflict];
            }
        }
        usort($slots, fn($a, $b) => strcmp($a['time'], $b['time']));
        return $this->response->setJSON(['ok' => true, 'slots' => $slots]);
    }

    /** Minimum notice (hours) before an appointment that a customer may self-cancel/reschedule. */
    private function cutoffHours(): int
    {
        return max(0, (int) ($this->s->get('portal_change_cutoff_hours') ?: 4));
    }

    /** Load an appointment that belongs to the logged-in customer + is still in the future. */
    private function ownFutureAppt(int $apptId): ?array
    {
        $customerId = (int) session()->get('portal_customer_id');
        $a = (new AppointmentModel())->find($apptId);
        if (! $a || (int) $a['customer_id'] !== $customerId) return null;
        if (in_array($a['status'], ['cancelled', 'no_show', 'completed'], true)) return null;
        if ($a['start_at'] < date('Y-m-d H:i:s')) return null;
        return $a;
    }

    /** POST /portal/booking/{id}/cancel — customer cancels their own upcoming booking. */
    public function cancelBooking(int $id)
    {
        $a = $this->ownFutureAppt($id);
        if (! $a) return redirect()->to('/portal/dashboard')->with('flash_error', 'That booking can no longer be cancelled.');

        $cutoff = $this->cutoffHours();
        if (strtotime($a['start_at']) - time() < $cutoff * 3600) {
            return redirect()->to('/portal/dashboard')->with('flash_error', "Bookings can only be cancelled at least {$cutoff}h in advance. Please call us.");
        }

        (new AppointmentModel())->update($id, ['status' => 'cancelled']);
        // Record the cancellation (customer-initiated, no fee)
        try {
            (new \App\Modules\Appointments\Models\CancellationModel())->record($a, 'cancelled', 'customer',
                trim((string) $this->request->getPost('reason')) ?: 'Cancelled via customer portal', 0.0, null);
        } catch (\Throwable $e) { /* non-fatal */ }

        helper('system');
        notify_broadcast([
            'type' => 'cancellation',
            'title' => 'Customer cancelled · ' . ($a['code'] ?? ('#' . $id)),
            'body'  => date('M j, H:i', strtotime($a['start_at'])) . ' — via portal',
            'link'  => site_url('admin/appointments/' . $id),
            'icon'  => 'calendar-x', 'color' => 'red',
        ]);
        log_action('appointment.cancel', ['entity_type' => 'appointment', 'entity_id' => $id, 'description' => 'Customer self-cancelled ' . ($a['code'] ?? ''), 'severity' => 'warning']);

        return redirect()->to('/portal/dashboard')->with('flash_success', 'Your booking has been cancelled.');
    }

    /** GET /portal/booking/{id}/reschedule — show date/slot picker for the customer's booking. */
    public function rescheduleForm(int $id)
    {
        $a = $this->ownFutureAppt($id);
        if (! $a) return redirect()->to('/portal/dashboard')->with('flash_error', 'That booking can no longer be changed.');

        // total duration for availability lookup
        $duration = (int) db_connect()->table('appointment_services')
            ->selectSum('duration_min', 'd')->where('appointment_id', $id)->get()->getRow('d');
        $duration = max(15, $duration);

        return view('App\Modules\Frontend\Views\layout', [
            'title'   => 'Reschedule booking — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\CustomerPortal\Views\reschedule',
            's'       => $this->s,
            'data'    => ['appt' => $a, 'duration' => $duration],
            'page'    => 'portal',
        ]);
    }

    /** POST /portal/booking/{id}/reschedule — apply the new start time. */
    public function reschedule(int $id)
    {
        $a = $this->ownFutureAppt($id);
        if (! $a) return redirect()->to('/portal/dashboard')->with('flash_error', 'That booking can no longer be changed.');

        $cutoff = $this->cutoffHours();
        if (strtotime($a['start_at']) - time() < $cutoff * 3600) {
            return redirect()->to('/portal/dashboard')->with('flash_error', "Bookings can only be changed at least {$cutoff}h in advance. Please call us.");
        }

        $start = trim((string) $this->request->getPost('start_at'));
        if (! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $start) || strtotime($start) < time()) {
            return redirect()->back()->with('flash_error', 'Please pick a valid future time slot.');
        }

        $duration = (int) db_connect()->table('appointment_services')
            ->selectSum('duration_min', 'd')->where('appointment_id', $id)->get()->getRow('d');
        $duration = max(15, $duration);
        $end = date('Y-m-d H:i:s', strtotime($start) + $duration * 60);

        $appts = new AppointmentModel();
        // Guard against double-booking the same stylist.
        if (method_exists($appts, 'conflicts') && $appts->conflicts((int) $a['staff_id'], $start, $end, $id)) {
            return redirect()->back()->with('flash_error', 'Sorry, that slot was just taken. Please pick another.');
        }

        $appts->update($id, ['start_at' => $start, 'end_at' => $end, 'status' => 'pending', 'reminded_at' => null]);

        helper('system');
        notify_broadcast([
            'type' => 'booking',
            'title' => 'Customer rescheduled · ' . ($a['code'] ?? ('#' . $id)),
            'body'  => 'New time: ' . date('M j, H:i', strtotime($start)),
            'link'  => site_url('admin/appointments/' . $id),
            'icon'  => 'calendar-clock', 'color' => 'amber',
        ]);
        log_action('appointment.reschedule', ['entity_type' => 'appointment', 'entity_id' => $id, 'description' => 'Customer rescheduled ' . ($a['code'] ?? '') . ' to ' . $start]);

        return redirect()->to('/portal/dashboard')->with('flash_success', 'Your booking has been moved to ' . date('M j, Y \a\t H:i', strtotime($start)) . '. We\'ll reconfirm shortly.');
    }
}
