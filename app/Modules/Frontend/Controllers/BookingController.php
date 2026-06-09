<?php

namespace App\Modules\Frontend\Controllers;

use App\Controllers\BaseController;
use App\Modules\Services\Models\ServiceModel;
use App\Modules\Services\Models\ServiceCategoryModel;
use App\Modules\Staff\Models\StaffModel;
use App\Modules\Customers\Models\CustomerModel;
use App\Modules\Appointments\Models\AppointmentModel;
use App\Modules\Settings\Models\SettingModel;

class BookingController extends BaseController
{
    private SettingModel $s;

    public function __construct()
    {
        $this->s = new SettingModel();
    }

    /** Booking wizard landing page */
    public function index()
    {
        $categories = (new ServiceCategoryModel())->where('is_active', 1)->orderBy('sort_order')->findAll();
        $services   = (new ServiceModel())->where('is_active', 1)->orderBy('name')->findAll();
        $staff      = (new StaffModel())->where('is_active', 1)->orderBy('full_name')->findAll();

        return view('App\Modules\Frontend\Views\layout', [
            'title'   => 'Book Appointment — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\Frontend\Views\book',
            's'       => $this->s,
            'data'    => compact('categories', 'services', 'staff'),
            'page'    => 'book',
        ]);
    }

    /** Find or create a customer by mobile, then create a 'pending' appointment */
    public function store()
    {
        $rules = [
            'full_name'   => 'required|min_length[2]|max_length[150]',
            'mobile'      => 'required|min_length[6]|max_length[30]',
            'email'       => 'permit_empty|valid_email',
            'staff_id'    => 'required|integer',
            'start_at'    => 'required',
            'service_ids' => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('flash_error', 'Please complete every required field.');
        }

        $mobile = trim((string) $this->request->getPost('mobile'));
        $name   = trim((string) $this->request->getPost('full_name'));
        $email  = trim((string) $this->request->getPost('email'));

        $customers = new CustomerModel();
        $existing  = $customers->where('mobile', $mobile)->first();
        if ($existing) {
            $customerId = (int) $existing['id'];
            $patch = [];
            if ($existing['full_name'] !== $name) $patch['full_name'] = $name;
            if ($email && empty($existing['email'])) $patch['email'] = $email;
            if ($patch) $customers->update($customerId, $patch);
        } else {
            $customerId = (int) $customers->insert([
                'branch_id' => 1,
                'full_name' => $name,
                'mobile'    => $mobile,
                'email'     => $email ?: null,
            ], true);
        }

        $serviceIds = array_filter(array_map('intval', (array) $this->request->getPost('service_ids')));
        $services   = (new ServiceModel())->whereIn('id', $serviceIds)->findAll();
        if (! $services) return redirect()->back()->withInput()->with('flash_error', 'Pick at least one service.');

        $duration = array_sum(array_column($services, 'duration_min'));
        $subtotal = array_sum(array_column($services, 'price'));
        $startAt  = date('Y-m-d H:i:s', strtotime((string) $this->request->getPost('start_at')));
        $endAt    = date('Y-m-d H:i:s', strtotime($startAt . ' +' . $duration . ' minutes'));
        $staffId  = (int) $this->request->getPost('staff_id');

        $appts = new AppointmentModel();
        if ($appts->conflicts($staffId, $startAt, $endAt)) {
            return redirect()->back()->withInput()->with('flash_error', 'That slot was just taken — please pick another time.');
        }

        $code   = 'APT-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $apptId = $appts->insert([
            'code'        => $code,
            'customer_id' => $customerId,
            'staff_id'    => $staffId,
            'branch_id'   => 1,
            'start_at'    => $startAt,
            'end_at'      => $endAt,
            'status'      => 'pending',
            'subtotal'    => $subtotal,
            'notes'       => 'Online booking request',
        ], true);

        $rows = array_map(fn($svc) => [
            'appointment_id' => $apptId,
            'service_id'     => (int) $svc['id'],
            'service_name'   => $svc['name'],
            'duration_min'   => (int) $svc['duration_min'],
            'price'          => (float) $svc['price'],
        ], $services);
        db_connect()->table('appointment_services')->insertBatch($rows);

        // Notify admins + log
        helper('system');
        notify_broadcast([
            'type'  => 'booking',
            'title' => 'New booking from ' . ($name ?: 'a customer'),
            'body'  => count($services) . ' service(s) · ' . date('M j, H:i', strtotime($startAt)),
            'link'  => site_url('admin/appointments/' . (int) $apptId),
            'icon'  => 'calendar-plus',
            'color' => 'green',
        ]);
        log_action('appointment.create', [
            'entity_type' => 'appointment',
            'entity_id'   => (int) $apptId,
            'description' => 'Public booking ' . $code . ' for ' . ($name ?: '—'),
        ]);

        return redirect()->to(site_url('book/confirm/' . $code));
    }

    public function confirm(string $code)
    {
        $appt = db_connect()->table('appointments a')
            ->select('a.*, c.full_name AS customer_name, c.email AS customer_email, c.mobile AS customer_mobile, s.full_name AS staff_name')
            ->join('customers c', 'c.id = a.customer_id', 'left')
            ->join('staff s',     's.id = a.staff_id',    'left')
            ->where('a.code', $code)
            ->get()->getRowArray();
        if (! $appt) return redirect()->to('/')->with('flash_error', 'Booking not found.');

        $items = db_connect()->table('appointment_services')->where('appointment_id', $appt['id'])->get()->getResultArray();

        return view('App\Modules\Frontend\Views\layout', [
            'title'   => 'Booking confirmed — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\Frontend\Views\confirm',
            's'       => $this->s,
            'data'    => compact('appt', 'items'),
            'page'    => 'confirm',
        ]);
    }
}
