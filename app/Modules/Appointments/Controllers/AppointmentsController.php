<?php

namespace App\Modules\Appointments\Controllers;

use App\Controllers\BaseController;
use App\Modules\Appointments\Models\AppointmentModel;
use App\Modules\Customers\Models\CustomerModel;
use App\Modules\Staff\Models\StaffModel;
use App\Modules\Services\Models\ServiceModel;

class AppointmentsController extends BaseController
{
    private AppointmentModel $appts;

    public function __construct()
    {
        $this->appts = new AppointmentModel();
    }

    public function index()
    {
        $date = $this->request->getGet('date') ?: date('Y-m-d');
        $view = $this->request->getGet('view') ?: 'day'; // day | week

        if ($view === 'week') {
            $start = date('Y-m-d', strtotime('monday this week', strtotime($date)));
            $end   = date('Y-m-d', strtotime($start . ' +6 days'));
        } else {
            $start = $end = $date;
        }

        $rows = $this->appts->forRange($start . ' 00:00:00', $end . ' 23:59:59');

        // Summary stats for the selected range (IMP_008)
        $total     = count($rows);
        $completed = 0; $pending = 0; $revenue = 0.0;
        foreach ($rows as $a) {
            if (($a['status'] ?? '') === 'completed') { $completed++; $revenue += (float) ($a['subtotal'] ?? 0); }
            elseif (! in_array($a['status'] ?? '', ['cancelled', 'no_show', 'completed'], true)) $pending++;
        }
        $stats = ['total' => $total, 'completed' => $completed, 'pending' => $pending, 'revenue' => $revenue];

        return view('layout/admin', [
            'title'   => 'Appointments',
            'content' => view('App\Modules\Appointments\Views\index', [
                'rows' => $rows, 'date' => $date, 'view' => $view,
                'rangeStart' => $start, 'rangeEnd' => $end, 'stats' => $stats,
            ]),
        ]);
    }

    public function create()
    {
        $customers    = (new CustomerModel())->orderBy('full_name')->findAll();
        $staff        = (new StaffModel())->where('is_active', 1)->orderBy('full_name')->findAll();
        $services     = (new ServiceModel())->where('is_active', 1)->orderBy('name')->findAll();
        $serviceTypes = (new \App\Modules\Services\Models\ServiceTypeModel())->active();
        $preselectCustomer = (int) $this->request->getGet('customer_id');

        return view('layout/admin', [
            'title'   => 'New Appointment',
            'content' => view('App\Modules\Appointments\Views\form', [
                'row' => null, 'customers' => $customers, 'staff' => $staff,
                'services' => $services, 'serviceTypes' => $serviceTypes,
                'assignedServices' => [],
                'preselectCustomer' => $preselectCustomer,
            ]),
        ]);
    }

    public function store()
    {
        return $this->save();
    }

    public function edit(int $id)
    {
        $row = $this->appts->withDetail($id);
        if (! $row) return redirect()->to('/admin/appointments');
        $customers    = (new CustomerModel())->orderBy('full_name')->findAll();
        $staff        = (new StaffModel())->where('is_active', 1)->orderBy('full_name')->findAll();
        $services     = (new ServiceModel())->where('is_active', 1)->orderBy('name')->findAll();
        $serviceTypes = (new \App\Modules\Services\Models\ServiceTypeModel())->active();

        return view('layout/admin', [
            'title'   => 'Edit Appointment',
            'content' => view('App\Modules\Appointments\Views\form', [
                'row' => $row, 'customers' => $customers, 'staff' => $staff,
                'services' => $services, 'serviceTypes' => $serviceTypes,
                'assignedServices' => array_column($row['services'], 'service_id'),
                'preselectCustomer' => null,
            ]),
        ]);
    }

    public function update(int $id)
    {
        return $this->save($id);
    }

    public function show(int $id)
    {
        $row = $this->appts->withDetail($id);
        if (! $row) return redirect()->to('/admin/appointments');
        return view('layout/admin', [
            'title'   => 'Appointment ' . $row['code'],
            'content' => view('App\Modules\Appointments\Views\show', ['row' => $row]),
        ]);
    }

    public function setStatus(int $id)
    {
        $status = $this->request->getPost('status');
        $allowed = ['pending','confirmed','checked_in','in_progress','completed','cancelled','no_show'];
        if (! in_array($status, $allowed, true)) {
            return redirect()->back()->with('flash_error', 'Invalid status.');
        }
        $row = $this->appts->find($id);
        if (! $row) return redirect()->back()->with('flash_error', 'Appointment not found.');

        $this->appts->update($id, ['status' => $status]);

        helper('system');
        log_action('appointment.status', [
            'entity_type' => 'appointment',
            'entity_id'   => $id,
            'description' => $row['code'] . ' → ' . $status,
            'severity'    => in_array($status, ['cancelled','no_show'], true) ? 'warning' : 'info',
        ]);
        if (in_array($status, ['cancelled','no_show'], true)) {
            notify_broadcast([
                'type'  => 'cancellation',
                'title' => 'Booking ' . str_replace('_',' ',$status) . ': ' . $row['code'],
                'body'  => 'Was scheduled ' . date('M j, H:i', strtotime($row['start_at'])),
                'link'  => site_url('admin/appointments/' . $id),
                'icon'  => 'calendar-x',
                'color' => 'red',
            ]);
        }

        // Auto-record service history when an appointment is completed.
        if ($status === 'completed') {
            $detail = $this->appts->withDetail($id) ?: $row;
            (new \App\Modules\Customers\Models\CustomerHistoryModel())->recordFromAppointment($detail);
        }

        // Auto-record a cancellation/no_show row (only if one doesn't already exist for this appt)
        if (in_array($status, ['cancelled', 'no_show'], true)) {
            $exists = (int) db_connect()->table('appointment_cancellations')
                ->where('appointment_id', $id)
                ->countAllResults();
            if ($exists === 0) {
                (new \App\Modules\Appointments\Models\CancellationModel())->record(
                    $row,
                    $status,
                    'staff',
                    $this->request->getPost('reason'),
                    (float) $this->request->getPost('fee'),
                    session('user.id')
                );
            }
        }

        helper('system');
        log_action('appointment.status', [
            'entity_type' => 'appointment', 'entity_id' => $id,
            'description' => ($row['code'] ?? ('#' . $id)) . ' → ' . $status,
            'severity'    => in_array($status, ['cancelled', 'no_show'], true) ? 'warning' : 'info',
        ]);
        if (in_array($status, ['cancelled', 'no_show'], true)) {
            notify_broadcast([
                'type'  => 'cancellation',
                'title' => ($status === 'no_show' ? 'No-show' : 'Cancellation') . ' · ' . ($row['code'] ?? ('#' . $id)),
                'body'  => date('M j, H:i', strtotime($row['start_at'] ?? 'now')),
                'link'  => site_url('admin/appointments/' . $id),
                'icon'  => 'calendar-x',
                'color' => 'red',
            ]);
        }

        return redirect()->back()->with('flash_success', 'Status updated to ' . $status . '.');
    }

    /**
     * Dedicated cancellation endpoint — used by the cancel modal which collects
     * reason + cancelled_by + fee separately from the status dropdown.
     */
    public function cancel(int $id)
    {
        $row = $this->appts->find($id);
        if (! $row) return redirect()->back()->with('flash_error', 'Appointment not found.');

        $type = $this->request->getPost('type') === 'no_show' ? 'no_show' : 'cancelled';
        $by   = $this->request->getPost('cancelled_by') ?: 'customer';
        $fee  = (float) $this->request->getPost('fee');
        $reason = trim((string) $this->request->getPost('reason'));

        if ($reason === '') {
            return redirect()->back()->with('flash_error', 'Please record a reason for the cancellation.');
        }

        $this->appts->update($id, ['status' => $type]);

        (new \App\Modules\Appointments\Models\CancellationModel())->record(
            $row, $type, $by, $reason, $fee, session('user.id')
        );

        return redirect()->back()->with('flash_success',
            ($type === 'no_show' ? 'No-show recorded.' : 'Cancellation recorded.')
        );
    }

    public function destroy(int $id)
    {
        $this->appts->delete($id);
        return redirect()->to('/admin/appointments')->with('flash_success', 'Appointment removed.');
    }

    public function cancellations()
    {
        $m         = new \App\Modules\Appointments\Models\CancellationModel();
        $recent    = $m->recent(50);
        $offenders = $m->topOffenders(20);

        $totals = [
            'cancellations'   => (int) db_connect()->table('appointment_cancellations')->where('type','cancelled')->countAllResults(),
            'no_shows'        => (int) db_connect()->table('appointment_cancellations')->where('type','no_show')->countAllResults(),
            'cancel_late'     => (int) db_connect()->table('appointment_cancellations')->where('type','cancelled')->where('notice_hours <', 24)->countAllResults(),
            'fees_collected'  => (float) (db_connect()->table('appointment_cancellations')->selectSum('fee_charged')->get()->getRow('fee_charged') ?? 0),
        ];

        return view('layout/admin', [
            'title'   => 'Cancellations & no-shows',
            'content' => view('App\Modules\Appointments\Views\cancellations', [
                'recent'    => $recent,
                'offenders' => $offenders,
                'totals'    => $totals,
            ]),
        ]);
    }

    private function save(?int $id = null)
    {
        $rules = [
            'customer_id'  => 'required|integer',
            'staff_id'     => 'required|integer',
            'start_at'     => 'required',
            'service_ids'  => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('flash_error', 'Please complete the booking form.');
        }

        $serviceIds = array_filter(array_map('intval', (array) $this->request->getPost('service_ids')));
        if (! $serviceIds) {
            return redirect()->back()->withInput()->with('flash_error', 'Select at least one service.');
        }

        // Resolve service type (single type per booking — simpler UX than per-line)
        $typeModel = new \App\Modules\Services\Models\ServiceTypeModel();
        $typeId    = (int) ($this->request->getPost('service_type_id') ?: 0);
        $type      = $typeId ? $typeModel->find($typeId) : $typeModel->defaultType();
        if (! $type) $type = ['id' => null, 'multiplier' => 1.0];
        $multiplier = (float) $type['multiplier'];

        $services      = (new ServiceModel())->whereIn('id', $serviceIds)->findAll();
        $totalDuration = array_sum(array_column($services, 'duration_min'));
        $subtotal      = round(array_sum(array_column($services, 'price')) * $multiplier, 2);

        $startAt = date('Y-m-d H:i:s', strtotime($this->request->getPost('start_at')));
        $endAt   = date('Y-m-d H:i:s', strtotime($startAt . ' +' . $totalDuration . ' minutes'));

        $staffId = (int) $this->request->getPost('staff_id');
        if ($this->appts->conflicts($staffId, $startAt, $endAt, $id)) {
            return redirect()->back()->withInput()->with('flash_error', 'This staff member is already booked for that time slot.');
        }

        $data = [
            'customer_id' => (int) $this->request->getPost('customer_id'),
            'staff_id'    => $staffId,
            'branch_id'   => session('user.branch_id') ?: 1,
            'start_at'    => $startAt,
            'end_at'      => $endAt,
            'status'      => $this->request->getPost('status') ?: 'confirmed',
            'subtotal'    => $subtotal,
            'notes'       => $this->request->getPost('notes'),
            'created_by'  => session('user.id'),
        ];

        if ($id) {
            $this->appts->update($id, $data);
        } else {
            $data['code'] = 'APT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $id = $this->appts->insert($data, true);
        }

        $db = db_connect();
        $db->table('appointment_services')->where('appointment_id', $id)->delete();
        $rows = array_map(fn($s) => [
            'appointment_id'  => $id,
            'service_id'      => (int) $s['id'],
            'service_type_id' => $type['id'],
            'service_name'    => $s['name'],
            'duration_min'    => (int) $s['duration_min'],
            'price'           => round((float) $s['price'] * $multiplier, 2),
        ], $services);
        $db->table('appointment_services')->insertBatch($rows);

        return redirect()->to('/admin/appointments/' . $id)->with('flash_success', 'Appointment saved.');
    }
}
