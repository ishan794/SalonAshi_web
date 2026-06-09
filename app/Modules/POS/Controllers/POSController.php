<?php

namespace App\Modules\POS\Controllers;

use App\Controllers\BaseController;
use App\Modules\Appointments\Models\AppointmentModel;
use App\Modules\Billing\Models\InvoiceModel;
use App\Modules\Customers\Models\CustomerModel;
use App\Modules\Services\Models\ServiceModel;
use App\Modules\Services\Models\ServiceCategoryModel;
use App\Modules\Staff\Models\StaffModel;

class POSController extends BaseController
{
    public function index()
    {
        $services   = (new ServiceModel())->where('is_active', 1)->orderBy('name')->findAll();
        $categories = (new ServiceCategoryModel())->where('is_active', 1)->orderBy('sort_order')->findAll();
        $customers  = (new CustomerModel())->orderBy('full_name')->findAll(200);
        $staff      = (new StaffModel())->where('is_active', 1)->orderBy('full_name')->findAll();

        return view('layout/admin', [
            'title'   => 'POS Dashboard',
            'content' => view('App\Modules\POS\Views\dashboard', [
                'services'   => $services,
                'categories' => $categories,
                'customers'  => $customers,
                'staff'      => $staff,
            ]),
        ]);
    }

    /** Create an appointment from the POS */
    public function quickBook()
    {
        $customerId = (int) $this->request->getPost('customer_id');
        $staffId    = (int) $this->request->getPost('staff_id');
        $serviceIds = array_filter(array_map('intval', (array) $this->request->getPost('service_ids')));
        $startAt    = $this->request->getPost('start_at') ?: date('Y-m-d H:i:s', strtotime('+15 minutes'));

        if (! $customerId || ! $staffId || ! $serviceIds) {
            return $this->fail('Need a customer, a staff member, and at least one service.');
        }

        $services = (new ServiceModel())->whereIn('id', $serviceIds)->findAll();
        $duration = array_sum(array_column($services, 'duration_min'));
        $subtotal = array_sum(array_column($services, 'price'));
        $startAt  = date('Y-m-d H:i:s', strtotime($startAt));
        $endAt    = date('Y-m-d H:i:s', strtotime($startAt . ' +' . $duration . ' minutes'));

        $appts = new AppointmentModel();
        if ($appts->conflicts($staffId, $startAt, $endAt)) {
            return $this->fail('Staff is already booked for that time slot.');
        }

        $code = 'APT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $apptId = $appts->insert([
            'code'        => $code,
            'customer_id' => $customerId,
            'staff_id'    => $staffId,
            'branch_id'   => session('user.branch_id') ?: 1,
            'start_at'    => $startAt,
            'end_at'      => $endAt,
            'status'      => 'confirmed',
            'subtotal'    => $subtotal,
            'created_by'  => session('user.id'),
        ], true);

        $rows = array_map(fn($s) => [
            'appointment_id' => $apptId,
            'service_id'     => (int) $s['id'],
            'service_name'   => $s['name'],
            'duration_min'   => (int) $s['duration_min'],
            'price'          => (float) $s['price'],
        ], $services);
        db_connect()->table('appointment_services')->insertBatch($rows);

        helper('system');
        log_action('appointment.create', ['entity_type' => 'appointment', 'entity_id' => (int) $apptId, 'description' => 'POS booking ' . $code]);
        notify_broadcast([
            'type'  => 'booking',
            'title' => 'New POS booking · ' . $code,
            'body'  => count($services) . ' service(s) · ' . date('M j, H:i', strtotime($startAt)),
            'link'  => site_url('admin/appointments/' . (int) $apptId),
            'icon'  => 'calendar-plus',
            'color' => 'green',
        ]);

        return $this->ok([
            'appointment_id' => $apptId,
            'code'           => $code,
            'redirect'       => site_url('admin/appointments/' . $apptId),
        ], 'Booked! ' . $code);
    }

    /** Create an invoice + record payment in one go from POS */
    public function quickBill()
    {
        $customerId = (int) $this->request->getPost('customer_id');
        $staffId    = (int) $this->request->getPost('staff_id') ?: null;
        $serviceIds = array_filter(array_map('intval', (array) $this->request->getPost('service_ids')));
        $discount   = (float) $this->request->getPost('discount');
        $method     = $this->request->getPost('method') ?: 'cash';
        $amount     = $this->request->getPost('amount'); // optional — defaults to total

        if (! $customerId || ! $serviceIds) {
            return $this->fail('Need a customer and at least one service.');
        }

        $services = (new ServiceModel())->whereIn('id', $serviceIds)->findAll();

        $invoices  = new InvoiceModel();
        $invoiceNo = 'INV-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $invoiceId = $invoices->insert([
            'invoice_no'  => $invoiceNo,
            'customer_id' => $customerId,
            'staff_id'    => $staffId,
            'branch_id'   => session('user.branch_id') ?: 1,
            'discount'    => $discount,
            'subtotal' => 0,'tax' => 0,'total' => 0,'paid' => 0,'balance' => 0,
            'status'      => 'unpaid',
            'created_by'  => session('user.id'),
        ], true);

        $db = db_connect();
        foreach ($services as $s) {
            $price = (float) $s['price'];
            $db->table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'item_type'  => 'service',
                'ref_id'     => $s['id'],
                'name'       => $s['name'],
                'qty'        => 1,
                'unit_price' => $price,
                'tax_pct'    => (float) $s['tax_pct'],
                'line_total' => $price,
            ]);
        }
        $invoices->recalcTotals($invoiceId);

        // Record payment
        $inv = $invoices->find($invoiceId);
        $payAmount = $amount !== null && $amount !== '' ? (float) $amount : (float) $inv['total'];
        $db->table('payments')->insert([
            'invoice_id'  => $invoiceId,
            'method'      => $method,
            'amount'      => $payAmount,
            'status'      => 'success',
            'received_by' => session('user.id'),
            'paid_at'     => date('Y-m-d H:i:s'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $invoices->recalcTotals($invoiceId);

        // Auto-earn loyalty if fully paid
        $invFinal = $invoices->find($invoiceId);
        if ($invFinal && (float)$invFinal['balance'] <= 0 && (float)$invFinal['total'] > 0) {
            (new \App\Modules\Customers\Services\LoyaltyService())
                ->earn($customerId, $invoiceId, (float)$invFinal['total']);
        }

        helper('system');
        log_action('invoice.payment', ['entity_type' => 'invoice', 'entity_id' => (int) $invoiceId, 'description' => 'POS sale ' . $invoiceNo . ' · LKR ' . number_format($payAmount, 2) . ' via ' . $method]);
        notify_broadcast([
            'type'  => 'payment',
            'title' => 'POS sale · LKR ' . number_format($payAmount, 2),
            'body'  => $invoiceNo . ' · ' . $method,
            'link'  => site_url('admin/billing/invoices/' . $invoiceId),
            'icon'  => 'banknote',
            'color' => 'green',
        ]);

        return $this->ok([
            'invoice_id' => $invoiceId,
            'invoice_no' => $invoiceNo,
            'redirect'   => site_url('admin/billing/invoices/' . $invoiceId),
        ], 'Billed! ' . $invoiceNo);
    }

    /**
     * Return available time slots for (staff, date, duration).
     * JSON: { date, slots:[{time, available, reason?}], workingHours }
     */
    public function availability()
    {
        $settings = new \App\Modules\Settings\Models\SettingModel();
        $defaultDur = max(5, (int) $settings->get('appt_default_duration', 30));
        $stepMin    = max(5, (int) $settings->get('appt_slot_interval', 15)); // slot granularity from settings
        $leadMin    = max(0, (int) $settings->get('appt_lead_min', 0));        // min notice before a bookable slot

        $staffId  = (int) $this->request->getGet('staff_id');
        $date     = $this->request->getGet('date') ?: date('Y-m-d');
        // Fall back to the configured default duration when the caller doesn't pass one.
        $duration = (int) $this->request->getGet('duration');
        if ($duration < 5) $duration = $defaultDur;

        if (! $staffId) return $this->fail('staff_id required');
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return $this->fail('Bad date');

        $staffModel = new \App\Modules\Staff\Models\StaffModel();
        $staff = $staffModel->find($staffId);
        if (! $staff) return $this->fail('Staff not found', 404);

        // Get all working windows for the date — may be 1 or more.
        // NULL means off (full-day time-off override).
        $windows = $staffModel->windowsForDate($staffId, $date);
        if ($windows === null) {
            return $this->ok([
                'date'         => $date,
                'staff_name'   => $staff['full_name'],
                'workingHours' => 'Off',
                'duration'     => $duration,
                'slots'        => [],
                'isOff'        => true,
            ]);
        }

        // Pull staff's existing appointments for the day (used to mark "busy" slots)
        $existing = db_connect()->table('appointments')
            ->where('staff_id', $staffId)
            ->whereIn('status', ['pending','confirmed','checked_in','in_progress'])
            ->where('start_at <', $date . ' 23:59:59')
            ->where('end_at   >', $date . ' 00:00:00')
            ->orderBy('start_at','asc')
            ->get()->getResultArray();

        $busy = array_map(fn($a) => [
            'start' => strtotime($a['start_at']),
            'end'   => strtotime($a['end_at']),
        ], $existing);

        // Slots must start at least $leadMin minutes from now (configurable booking lead time).
        $now    = time() + $leadMin * 60;
        $slots  = [];
        $labels = [];

        // Iterate every working window and emit slots within each
        foreach ($windows as [$startHM, $endHM]) {
            $labels[]  = $startHM . '–' . $endHM;
            $winStart  = strtotime("$date $startHM");
            $winEnd    = strtotime("$date $endHM");

            for ($t = $winStart; $t + $duration * 60 <= $winEnd; $t += $stepMin * 60) {
                $slotStart = $t;
                $slotEnd   = $t + $duration * 60;

                if ($slotEnd <= $now) {
                    $slots[] = ['time' => date('H:i', $slotStart), 'available' => false, 'reason' => 'past'];
                    continue;
                }

                $conflict = false;
                foreach ($busy as $b) {
                    if ($slotStart < $b['end'] && $slotEnd > $b['start']) { $conflict = true; break; }
                }
                $slots[] = [
                    'time'      => date('H:i', $slotStart),
                    'available' => ! $conflict,
                    'reason'    => $conflict ? 'busy' : null,
                ];
            }
        }

        // Dedupe (overlapping windows shouldn't happen, but be safe)
        $seen = []; $unique = [];
        foreach ($slots as $s) {
            if (isset($seen[$s['time']])) continue;
            $seen[$s['time']] = true;
            $unique[] = $s;
        }
        usort($unique, fn($a, $b) => strcmp($a['time'], $b['time']));

        return $this->ok([
            'date'         => $date,
            'staff_name'   => $staff['full_name'],
            'workingHours' => implode(', ', $labels),
            'windowCount'  => count($windows),
            'duration'     => $duration,
            'slots'        => $unique,
        ]);
    }

    /** Quick-create a new customer from the POS */
    public function quickCustomer()
    {
        $name   = trim((string) $this->request->getPost('full_name'));
        $mobile = trim((string) $this->request->getPost('mobile'));
        if ($name === '' || $mobile === '') return $this->fail('Name and mobile required.');

        $id = (new CustomerModel())->insert([
            'branch_id' => session('user.branch_id') ?: 1,
            'full_name' => $name,
            'mobile'    => $mobile,
            'email'     => $this->request->getPost('email') ?: null,
        ], true);

        return $this->ok([
            'id'     => (int) $id,
            'label'  => $name,
            'desc'   => phone_local($mobile),
        ], 'Customer added.');
    }

    private function ok(array $data, string $msg = 'OK')
    {
        return $this->response->setJSON(['ok' => true, 'msg' => $msg] + $data);
    }
    private function fail(string $msg, int $code = 422)
    {
        return $this->response->setStatusCode($code)->setJSON(['ok' => false, 'msg' => $msg]);
    }
}
