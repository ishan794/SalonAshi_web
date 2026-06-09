<?php

namespace App\Modules\Reviews\Controllers;

use App\Controllers\BaseController;
use App\Modules\Reviews\Models\ReviewModel;
use App\Modules\Settings\Models\SettingModel;

class ReviewsController extends BaseController
{
    private ReviewModel $reviews;

    public function __construct()
    {
        $this->reviews = new ReviewModel();
    }

    public function index()
    {
        $status    = $this->request->getGet('status');
        $source    = $this->request->getGet('source');
        $minRating = (int) $this->request->getGet('min_rating');
        $rows      = $this->reviews->withFilters($status ?: null, $source ?: null, $minRating ?: null);
        $summary   = $this->reviews->summary();

        return view('layout/admin', [
            'title'   => 'Reviews',
            'content' => view('App\Modules\Reviews\Views\index', compact('rows', 'summary', 'status', 'source', 'minRating')),
        ]);
    }

    public function approve(int $id)
    {
        $this->reviews->update($id, ['status' => 'approved']);
        return redirect()->back()->with('flash_success', 'Review approved.');
    }

    public function reject(int $id)
    {
        $this->reviews->update($id, ['status' => 'rejected']);
        return redirect()->back()->with('flash_success', 'Review rejected.');
    }

    public function toggleFeatured(int $id)
    {
        $r = $this->reviews->find($id);
        if (! $r) return redirect()->back();
        $this->reviews->update($id, ['is_featured' => empty($r['is_featured']) ? 1 : 0]);
        return redirect()->back();
    }

    public function destroy(int $id)
    {
        $this->reviews->delete($id);
        return redirect()->back()->with('flash_success', 'Review deleted.');
    }

    public function create()
    {
        return view('layout/admin', [
            'title'   => 'Add review',
            'content' => view('App\Modules\Reviews\Views\create'),
        ]);
    }

    public function store()
    {
        $in = $this->request->getPost();
        $rating = max(1, min(5, (int) ($in['rating'] ?? 0)));
        if (! $rating || empty($in['reviewer_name']) || empty($in['body'])) {
            return redirect()->back()->with('flash_error', 'Reviewer name, body and rating are required.');
        }
        $this->reviews->insert([
            'reviewer_name' => trim($in['reviewer_name']),
            'rating'        => $rating,
            'title'         => $in['title'] ?? null,
            'body'          => trim($in['body']),
            'source'        => 'manual',
            'status'        => 'approved',
            'is_featured'   => ! empty($in['is_featured']) ? 1 : 0,
        ]);
        return redirect()->to('/admin/reviews')->with('flash_success', 'Review added.');
    }

    /** Import latest reviews from Google Places API. */
    public function importGoogle()
    {
        $s = new SettingModel();
        $placeId = trim((string) $s->get('google_place_id'));
        $apiKey  = trim((string) $s->get('google_places_api_key'));
        if ($placeId === '' || $apiKey === '') {
            return redirect()->to('/admin/settings/integrations')->with('flash_error', 'Set Google Place ID + API key first.');
        }

        $url = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=' . urlencode($placeId)
             . '&fields=' . urlencode('reviews,user_ratings_total,rating,name,url')
             . '&key=' . urlencode($apiKey);

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $body = curl_exec($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $data = json_decode((string) $body, true);
        } catch (\Throwable $e) {
            return redirect()->back()->with('flash_error', 'Google API call failed: ' . $e->getMessage());
        }

        if (($data['status'] ?? '') !== 'OK') {
            return redirect()->back()->with('flash_error', 'Google API returned: ' . ($data['status'] ?? "HTTP {$http}") . ' — ' . ($data['error_message'] ?? ''));
        }

        $result   = $data['result']['reviews'] ?? [];
        $placeUrl = $data['result']['url'] ?? null;

        $inserted = $updated = 0;
        foreach ($result as $r) {
            $authorName = $r['author_name'] ?? 'Google user';
            $reviewId   = ($r['time'] ?? '0') . '_' . substr(md5(($r['author_name'] ?? '') . ($r['text'] ?? '')), 0, 10);
            $res = $this->reviews->upsertGoogle([
                'source_id'           => $reviewId,
                'reviewer_name'       => $authorName,
                'reviewer_avatar_url' => $r['profile_photo_url'] ?? null,
                'rating'              => max(1, min(5, (int) ($r['rating'] ?? 0))),
                'body'                => (string) ($r['text'] ?? ''),
                'source_url'          => $r['author_url'] ?? $placeUrl,
                'source_created_at'   => isset($r['time']) ? date('Y-m-d H:i:s', (int) $r['time']) : null,
            ]);
            if ($res['action'] === 'inserted') $inserted++;
            elseif ($res['action'] === 'updated') $updated++;
        }

        // Cache aggregate rating + count for display
        if (isset($data['result']['rating']))             $s->set('google_rating',       (string) $data['result']['rating']);
        if (isset($data['result']['user_ratings_total'])) $s->set('google_review_count', (string) $data['result']['user_ratings_total']);
        $s->set('google_last_import_at', date('Y-m-d H:i:s'));

        return redirect()->to('/admin/reviews')->with('flash_success', "Google import: {$inserted} new, {$updated} updated.");
    }
}
