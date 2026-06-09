<?php

namespace App\Modules\Frontend\Controllers;

use App\Controllers\BaseController;
use App\Modules\Services\Models\ServiceModel;
use App\Modules\Services\Models\ServiceCategoryModel;
use App\Modules\Staff\Models\StaffModel;
use App\Modules\Settings\Models\SettingModel;
use App\Modules\Reviews\Models\ReviewModel;

class SiteController extends BaseController
{
    private SettingModel $s;

    public function __construct()
    {
        $this->s = new SettingModel();
    }

    public function home()
    {
        $services = (new ServiceModel())->where('is_active', 1)->orderBy('category_id')->orderBy('name')->findAll(12);
        $staff    = (new StaffModel())->where('is_active', 1)->orderBy('full_name')->findAll(8);
        $reviews  = (new ReviewModel())->latestApproved(6);
        $googleRating       = (float) $this->s->get('google_rating');
        $googleReviewCount  = (int)   $this->s->get('google_review_count');

        return view('App\Modules\Frontend\Views\layout', [
            'title'    => $this->s->get('salon_name', 'SalonCMS') . ' — Book your appointment online',
            'subview'  => 'App\Modules\Frontend\Views\home',
            's'        => $this->s,
            'data'     => compact('services', 'staff', 'reviews', 'googleRating', 'googleReviewCount'),
            'page'     => 'home',
        ]);
    }

    public function services()
    {
        $categories = (new ServiceCategoryModel())->where('is_active', 1)->orderBy('sort_order')->findAll();
        $query = (new ServiceModel())->where('is_active', 1);
        
        $q = $this->request->getGet('q');
        if (!empty($q)) {
            $query->like('name', $q);
        }
        $services = $query->orderBy('name')->findAll();
        
        $byCategory = [];
        foreach ($services as $svc) $byCategory[(int) $svc['category_id']][] = $svc;

        return view('App\Modules\Frontend\Views\layout', [
            'title'   => 'Services — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\Frontend\Views\services',
            's'       => $this->s,
            'data'    => compact('categories', 'byCategory'),
            'page'    => 'services',
        ]);
    }
}
