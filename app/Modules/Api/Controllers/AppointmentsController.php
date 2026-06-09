<?php

namespace App\Modules\Api\Controllers;

use App\Modules\Appointments\Models\AppointmentModel;
use App\Modules\Staff\Models\StaffModel;
use App\Modules\Settings\Models\SettingModel;
use App\Modules\Billing\Models\InvoiceModel;

class AppointmentsController extends ApiBaseController
{
    /**
     * GET /api/appointments?date=YYYY-MM-DD&from=&to=&status=&staff_id=&page=&per_page=
     * Stylists auto-scoped to own staff_id.
     */
    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $db       = db_connect();
        $date     = $this->request->getGet('date');
        $from     = $this->request->getGet('from') ?: ($date ? $date : date('Y-m-d', strtotime('-6 days')));
        $to       = $this->request->getGet('to')   ?: ($date ? $date : date('Y-m-d'));
        $status   = $this->request->getGet('status');
        $q        = trim((string) ($this->request->getGet('q') ?: ''));
        $staffId  = $this->resolveStaffId((int) $this->request->getGet('staff_id') ?: null);
        $perPage  = max(5, min(100, (int) ($this->request->getGet('per_page') ?: 50)));
        $page     = max(1, (int) ($this->request->getGet('page') ?: 1));

        $b = $db->table('appointments a')
            ->select('a.*, c.full_name AS customer_name, c.mobile AS customer_mobile, s.full_name AS staff_name')
            ->join('customers c', 'c.id = a.customer_id', 'left')
            ->join('staff s', 's.id = a.staff_id', 'left')
            ->where('a.start_at >=', $from . ' 00:00:00')
            ->where('a.start_at <=', $to . ' 23:59:59')
            ->orderBy('a.start_at', 'asc');

        if ($staffId)  $b->where('a.staff_id', $staffId);
        if ($status)   $b->where('a.status', $status);

        // Free-text search across client name, mobile (digit-normalised) and appointment code.
        if ($q !== '') {
            $digits = preg_replace('/\D+/', '', $q);
            $core   = strlen($digits) >= 9 ? substr($digits, -9) : ltrim($digits, '0');
            $b->groupStart()
                ->like('c.full_name', $q)
                ->orLike('a.code', $q)
                ->orLike('c.mobile', $q);
            if ($core !== '') {
                $b->orWhere("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(c.mobile,' ',''),'-',''),'(',''),')',''),'+','') LIKE '%{$core}%'", null, false);
            }
            $b->groupEnd();
        }

        $total = (clone $b)->countAllResults(false);
        $rows  = $b->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        $totalPages = max(1, (int) ceil($total / $perPage));

        return $this->ok($rows, [
            'page' => $page, 'per_page' => $perPage,
            'total' => $total, 'total_pages' => $totalPages,
        ]);
    }

    /**
     * POST /api/appointments
     * Body: { customer_id, staff_id, service_ids[], start_at, notes? }
     */
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($r = $this->requirePerm('appointments.create')) return $r;
        $body = $this->body();

        $customerId = (int) ($body['customer_id'] ?? 0);
        $staffId    = (int) ($body['staff_id']    ?? 0);
        $serviceIds = array_filter(array_map('intval', (array) ($body['service_ids'] ?? [])));
        $startAt    = trim((string) ($body['start_at'] ?? ''));

        if (! $customerId || ! $staffId || ! $serviceIds || ! $startAt) {
            return $this->fail('customer_id, staff_id, service_ids, and start_at are required.', 400);
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $startAt)) {
            return $this->fail('start_at must be YYYY-MM-DD HH:MM format.', 400);
        }

        $s   = new SettingModel();
        $dur = (int) ($s->get('appt_default_duration') ?: 60);
        $endAt = date('Y-m-d H:i:s', strtotime($startAt) + $dur * 60);

        $apptModel = new AppointmentModel();
        if ($apptModel->conflicts($staffId, $startAt, $endAt)) {
            return $this->fail('That time slot is already taken for this stylist.', 409);
        }

        $code = 'APT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $apptId = $apptModel->insert([
            'code'        => $code,
            'customer_id' => $customerId,
            'staff_id'    => $staffId,
            'start_at'    => $startAt,
            'end_at'      => $endAt,
            'status'      => 'pending',
            'notes'       => $body['notes'] ?? null,
            'created_by'  => $this->apiUser->id(),
        ], true);

        $db = db_connect();
        foreach ($serviceIds as $sid) {
            $svc = $db->table('services')->where('id', $sid)->get()->getRowArray();
            if ($svc) {
                $db->table('appointment_services')->insert([
                    'appointment_id' => $apptId,
                    'service_id'     => $sid,
                    'service_name'   => $svc['name'],
                    'price'          => $svc['price'],
                ]);
            }
        }

        // Booking confirmation notifications (in-app + push).
        helper('system');
        $cust = $db->table('customers')->select('full_name')->where('id', $customerId)->get()->getRowArray();
        $staffRow = $db->table('staff')->select('user_id, full_name')->where('id', $staffId)->get()->getRowArray();
        $custName = $cust['full_name'] ?? 'A customer';
        $whenTxt  = date('M j, g:i A', strtotime($startAt));
        if (! empty($staffRow['user_id'])) {
            notify([
                'user_id' => (int) $staffRow['user_id'],
                'type'    => 'appointment',
                'title'   => 'New appointment booked',
                'body'    => $custName . ' · ' . $whenTxt,
                'icon'    => 'calendar',
                'color'   => 'brand',
                'link'    => '/appointments/' . $apptId,
            ]);
        }
        notify_broadcast([
            'type'  => 'appointment',
            'title' => 'New appointment',
            'body'  => $custName . ' with ' . ($staffRow['full_name'] ?? 'staff') . ' · ' . $whenTxt,
            'icon'  => 'calendar',
            'color' => 'brand',
            'link'  => '/appointments/' . $apptId,
        ]);

        return $this->ok(['appointment_id' => $apptId, 'code' => $code], [], 201);
    }

    /**
     * GET /api/appointments/{id}
     */
    public function show(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $apptModel = new AppointmentModel();
        $appt = $apptModel->withDetail($id);
        if (! $appt) return $this->notFound('Appointment not found.');

        // Stylists can only see own appointments
        if ($this->apiUser->isStylist() && (int) $appt['staff_id'] !== $this->apiUser->staffId()) {
            return $this->forbidden();
        }
        return $this->ok($appt);
    }

    /**
     * PATCH /api/appointments/{id}/status
     * Body: { status: 'confirmed'|'checked_in'|'in_progress'|'completed'|'no_show'|'cancelled' }
     */
    public function setStatus(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($r = $this->requirePerm('appointments.edit')) return $r;

        $allowed = ['pending','confirmed','checked_in','in_progress','completed','cancelled','no_show'];
        $status  = trim((string) ($this->body()['status'] ?? ''));
        if (! in_array($status, $allowed, true)) {
            return $this->fail('Invalid status. Allowed: ' . implode(', ', $allowed), 400);
        }

        $apptModel = new AppointmentModel();
        $appt = $apptModel->find($id);
        if (! $appt) return $this->notFound('Appointment not found.');

        $apptModel->update($id, ['status' => $status]);
        return $this->ok(['id' => $id, 'status' => $status]);
    }

    /**
     * GET /api/appointments/availability?staff_id=&date=YYYY-MM-DD&duration=
     * Reuses the same slot-generation logic as POS.
     */
    public function availability(): \CodeIgniter\HTTP\ResponseInterface
    {
        $staffId  = (int) $this->request->getGet('staff_id');
        $date     = $this->request->getGet('date') ?: date('Y-m-d');
        $duration = (int) ($this->request->getGet('duration') ?: 0);

        if (! $staffId) return $this->fail('staff_id is required.', 400);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->fail('date must be YYYY-MM-DD.', 400);
        }

        $staffModel = new StaffModel();
        $staff = $staffModel->find($staffId);
        if (! $staff) return $this->notFound('Stylist not found.');

        $s        = new SettingModel();
        $interval = (int) ($s->get('appt_slot_interval') ?: 30);
        $defDur   = (int) ($s->get('appt_default_duration') ?: 60);
        $leadMin  = (int) ($s->get('appt_lead_min') ?: 0);
        if ($duration < 1) $duration = $defDur;

        // Check time-off
        $timeOff = $staffModel->getTimeOff($staffId, $date);
        $offDates = array_column($timeOff, 'off_date');
        if (in_array($date, $offDates, true)) {
            return $this->ok(['date' => $date, 'is_off' => true, 'slots' => []]);
        }

        // Get working windows
        $windows = $staffModel->getDateWindows($staffId, $date);
        if (empty($windows)) {
            $dow = (int) date('w', strtotime($date));
            $schedule = $staffModel->getSchedule($staffId);
            $daySchedule = $schedule[$dow] ?? null;
            if (! $daySchedule || ! empty($daySchedule['is_off'])) {
                return $this->ok(['date' => $date, 'is_off' => true, 'slots' => []]);
            }
            $windows = [['start_time' => $daySchedule['start_time'], 'end_time' => $daySchedule['end_time']]];
        }

        // Existing bookings for this date
        $db = db_connect();
        $booked = $db->table('appointments')
            ->select('start_at, end_at')
            ->where('staff_id', $staffId)
            ->where('DATE(start_at)', $date)
            ->whereNotIn('status', ['cancelled'])
            ->get()->getResultArray();

        $nowTs      = time() + ($leadMin * 60);
        $slots      = [];
        $datePrefix = $date . ' ';

        foreach ($windows as $w) {
            $winStart = strtotime($datePrefix . $w['start_time']);
            $winEnd   = strtotime($datePrefix . $w['end_time']);
            for ($ts = $winStart; $ts + ($duration * 60) <= $winEnd; $ts += $interval * 60) {
                $slotEnd  = $ts + ($duration * 60);
                $available = $ts >= $nowTs;
                if ($available) {
                    foreach ($booked as $b) {
                        $bs = strtotime($b['start_at']);
                        $be = strtotime($b['end_at']);
                        if ($ts < $be && $slotEnd > $bs) { $available = false; break; }
                    }
                }
                $slots[] = [
                    'time'      => date('H:i', $ts),
                    'end'       => date('H:i', $slotEnd),
                    'available' => $available,
                ];
            }
        }

        return $this->ok([
            'date'       => $date,
            'is_off'     => false,
            'staff_name' => $staff['full_name'],
            'duration'   => $duration,
            'slots'      => $slots,
        ]);
    }

    /**
     * POST /api/appointments/{id}/invoice — convert an appointment into an invoice.
     * Returns the existing invoice if one is already linked.
     */
    public function convertToInvoice(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($r = $this->requirePerm('invoices.create')) return $r;

        $appt = (new AppointmentModel())->withDetail($id);
        if (! $appt) return $this->notFound('Appointment not found.');

        $db = db_connect();
        $existing = $db->table('invoices')->where('appointment_id', $id)->get()->getRowArray();
        if ($existing) {
            return $this->ok(['invoice_id' => (int) $existing['id'], 'invoice_no' => $existing['invoice_no'], 'existing' => true]);
        }

        $invModel  = new InvoiceModel();
        $invoiceNo = 'INV-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $invoiceId = $invModel->insert([
            'invoice_no'     => $invoiceNo,
            'appointment_id' => $appt['id'],
            'customer_id'    => $appt['customer_id'],
            'staff_id'       => $appt['staff_id'],
            'branch_id'      => $appt['branch_id'] ?? ($this->apiUser->branchId() ?? 1),
            'subtotal' => 0, 'discount' => 0, 'tax' => 0, 'total' => 0, 'paid' => 0, 'balance' => 0,
            'status'         => 'unpaid',
            'created_by'     => $this->apiUser->id(),
        ], true);

        foreach (($appt['services'] ?? []) as $s) {
            $line = (float) $s['price'];
            $db->table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'item_type'  => 'service',
                'ref_id'     => $s['service_id'] ?? null,
                'name'       => $s['service_name'],
                'qty'        => 1,
                'unit_price' => $line,
                'tax_pct'    => 0,
                'line_total' => $line,
            ]);
        }
        $invModel->recalcTotals($invoiceId);

        return $this->ok([
            'invoice_id' => $invoiceId,
            'invoice_no' => $invoiceNo,
            'invoice'    => $invModel->withCustomer($invoiceId),
        ], [], 201);
    }

    /**
     * PATCH /api/appointments/{id}/reschedule — change date/time.
     * Body: { start_at: 'YYYY-MM-DD HH:MM' }
     */
    public function reschedule(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($r = $this->requirePerm('appointments.edit')) return $r;

        $startAt = trim((string) ($this->body()['start_at'] ?? ''));
        if (! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $startAt)) {
            return $this->fail('start_at must be YYYY-MM-DD HH:MM.', 400);
        }
        $m = new AppointmentModel();
        $appt = $m->find($id);
        if (! $appt) return $this->notFound('Appointment not found.');

        $dur = (int) ((strtotime($appt['end_at']) - strtotime($appt['start_at'])) / 60);
        if ($dur < 1) $dur = (int) ((new SettingModel())->get('appt_default_duration') ?: 60);
        $endAt = date('Y-m-d H:i:s', strtotime($startAt) + $dur * 60);

        if ($m->conflicts((int) $appt['staff_id'], $startAt, $endAt, $id)) {
            return $this->fail('That time slot is already taken.', 409);
        }
        $m->update($id, ['start_at' => $startAt, 'end_at' => $endAt]);
        return $this->ok(['id' => $id, 'start_at' => $startAt, 'end_at' => $endAt]);
    }

    /**
     * GET /api/appointments/{id}/activity — timeline / activity log entries.
     */
    public function activity(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $rows = db_connect()->table('system_logs')
            ->select('id, action, description, user_name, severity, created_at')
            ->where('entity_type', 'appointment')
            ->where('entity_id', $id)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get()->getResultArray();
        return $this->ok($rows);
    }
}
