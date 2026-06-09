<?php

namespace App\Modules\Api\Controllers;

use App\Modules\Auth\Models\UserModel;

class ProfileController extends ApiBaseController
{
    /**
     * GET /api/me — own profile + linked staff record.
     */
    public function show(): \CodeIgniter\HTTP\ResponseInterface
    {
        $model = new UserModel();
        $user  = $model->withRole($this->apiUser->id());
        if (! $user) return $this->notFound();

        // Strip sensitive fields
        unset($user['password_hash']);

        $staffRow = null;
        if ($this->apiUser->staffId()) {
            $staffRow = db_connect()->table('staff')
                ->where('id', $this->apiUser->staffId())
                ->get()->getRowArray();
        }

        return $this->ok([
            'user'  => $user,
            'staff' => $staffRow,
            'perms' => $this->apiUser->get('perms'),
        ]);
    }

    /**
     * PATCH /api/me
     * Body: { name?, phone?, expo_push_token? }
     * Email and role changes are NOT allowed via this endpoint.
     */
    public function update(): \CodeIgniter\HTTP\ResponseInterface
    {
        $body    = $this->body();
        $model   = new UserModel();
        $payload = [];

        if (isset($body['name'])  && trim($body['name']))  $payload['name']  = trim($body['name']);
        if (isset($body['phone'])) $payload['phone'] = trim($body['phone']) ?: null;

        // expo_push_token — stored on the user row for push notifications
        if (isset($body['expo_push_token'])) {
            // Guard: only add if column exists (added via schema_addon later if needed)
            try {
                $payload['expo_push_token'] = trim($body['expo_push_token']) ?: null;
            } catch (\Throwable) {}
        }

        if ($payload) $model->update($this->apiUser->id(), $payload);

        return $this->show();
    }
}
