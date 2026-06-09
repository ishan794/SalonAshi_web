<?php

namespace App\Modules\Frontend\Controllers;

use App\Controllers\BaseController;
use App\Modules\Reviews\Models\ReviewModel;
use App\Modules\Settings\Models\SettingModel;
use App\Modules\Appointments\Models\AppointmentModel;

class PublicReviewController extends BaseController
{
    private SettingModel $s;

    public function __construct()
    {
        $this->s = new SettingModel();
    }

    /** GET /review or /review/{appointment-code} — review submission form */
    public function form(?string $code = null)
    {
        $appt    = null;
        $hasReviewed = false;
        if ($code) {
            $appt = (new AppointmentModel())->where('code', $code)->first();
            if ($appt) {
                $hasReviewed = (bool) (new ReviewModel())
                    ->where('appointment_id', $appt['id'])
                    ->where('source', 'in-app')
                    ->first();
            }
        }

        return view('App\Modules\Frontend\Views\layout', [
            'title'   => 'Leave a review — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\Frontend\Views\review',
            's'       => $this->s,
            'data'    => compact('appt', 'code', 'hasReviewed'),
            'page'    => 'review',
        ]);
    }

    /** POST /review — store the submitted review (status defaults to pending). */
    public function submit()
    {
        $in       = $this->request->getPost();
        $rating   = max(1, min(5, (int) ($in['rating']  ?? 0)));
        $name     = trim((string) ($in['name']  ?? ''));
        $body     = trim((string) ($in['body']  ?? ''));
        $title    = trim((string) ($in['title'] ?? ''));
        $code     = trim((string) ($in['appointment_code'] ?? ''));

        if (! $rating || $name === '' || $body === '') {
            return redirect()->back()->with('flash_error', 'Name, rating and review text are required.');
        }

        $apptId = null; $customerId = null; $staffId = null;
        if ($code) {
            $appt = (new AppointmentModel())->where('code', $code)->first();
            if ($appt) {
                $apptId     = (int) $appt['id'];
                $customerId = (int) ($appt['customer_id'] ?? 0) ?: null;
                $staffId    = (int) ($appt['staff_id']    ?? 0) ?: null;
            }
        }

        // Auto-approve if admin opted-in via setting, else queue as pending for moderation.
        $autoApprove = $this->s->get('reviews_auto_approve') === '1';

        $reviewId = (new ReviewModel())->insert([
            'customer_id'    => $customerId,
            'appointment_id' => $apptId,
            'staff_id'       => $staffId,
            'reviewer_name'  => $name,
            'rating'         => $rating,
            'title'          => $title ?: null,
            'body'           => $body,
            'source'         => 'in-app',
            'status'         => $autoApprove ? 'approved' : 'pending',
        ], true);

        helper('system');
        notify_broadcast([
            'type'  => 'review',
            'title' => $rating . '★ review from ' . $name,
            'body'  => $title ?: mb_substr($body, 0, 100) . (mb_strlen($body) > 100 ? '…' : ''),
            'link'  => site_url('admin/reviews'),
            'icon'  => 'star',
            'color' => $rating >= 4 ? 'green' : ($rating >= 3 ? 'amber' : 'red'),
        ]);
        log_action('review.create', [
            'entity_type' => 'review',
            'entity_id'   => (int) $reviewId,
            'description' => $rating . '★ from ' . $name . ($autoApprove ? ' (auto-approved)' : ' (pending)'),
        ]);

        return redirect()->to($code ? "review/{$code}" : 'review')
            ->with('flash_success', 'Thanks for your feedback! Your review has been submitted.');
    }
}
