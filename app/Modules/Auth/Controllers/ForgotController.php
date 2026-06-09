<?php

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\Auth\Models\UserModel;
use App\Modules\Settings\Models\SettingModel;

class ForgotController extends BaseController
{
    /** GET /forgot — email entry form */
    public function index()
    {
        return view('layout/auth', [
            'title'   => 'Reset password — SalonCMS',
            'content' => view('App\Modules\Auth\Views\forgot', [], ['saveData' => true]),
        ]);
    }

    /** POST /forgot — issue a reset link via email (generic success either way) */
    public function send()
    {
        $email = trim((string) $this->request->getPost('email'));
        $generic = 'If an account exists for that email, a reset link has been sent.';

        if ($email === '') {
            return redirect()->back()->with('flash_error', 'Please enter your email.');
        }

        $user = (new UserModel())->findByEmail($email);
        if ($user) {
            $db    = db_connect();
            $token = bin2hex(random_bytes(32));
            // Invalidate prior unused tokens for this user
            $db->table('password_resets')->where('user_id', (int) $user['id'])->where('used_at IS NULL', null, false)
               ->update(['used_at' => date('Y-m-d H:i:s')]);
            $db->table('password_resets')->insert([
                'user_id'    => (int) $user['id'],
                'token'      => $token,
                'expires_at' => date('Y-m-d H:i:s', time() + 3600), // 1 hour
                'ip_address' => $this->request->getIPAddress(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $s    = new SettingModel();
            $link = site_url('reset/' . $token);
            try {
                $mailer = \Config\Services::email();
                $mailer->setFrom($s->get('smtp_from_email') ?: $email, $s->get('salon_name', 'SalonCMS'));
                $mailer->setTo($email);
                $mailer->setSubject('Reset your ' . $s->get('salon_name', 'SalonCMS') . ' password');
                $mailer->setMessage(
                    "Hi {$user['name']},\n\n" .
                    "We received a request to reset your password. Click the link below (valid for 1 hour):\n\n" .
                    "{$link}\n\n" .
                    "If you didn't request this, you can safely ignore this email."
                );
                $mailer->send();
            } catch (\Throwable $e) {
                log_message('error', 'Password reset email failed: ' . $e->getMessage());
            }
            helper('system');
            log_action('password.reset_requested', ['entity_type' => 'user', 'entity_id' => (int) $user['id'], 'description' => 'Reset link requested for ' . $email, 'severity' => 'warning']);
        }

        return redirect()->to('/login')->with('flash_success', $generic);
    }

    /** GET /reset/{token} — new-password form */
    public function reset(string $token)
    {
        $row = $this->validToken($token);
        if (! $row) {
            return redirect()->to('/login')->with('flash_error', 'That reset link is invalid or has expired. Please request a new one.');
        }
        return view('layout/auth', [
            'title'   => 'Choose a new password — SalonCMS',
            'content' => view('App\Modules\Auth\Views\reset', ['token' => $token], ['saveData' => true]),
        ]);
    }

    /** POST /reset — set the new password */
    public function doReset()
    {
        $token = (string) $this->request->getPost('token');
        $pass  = (string) $this->request->getPost('password');
        $conf  = (string) $this->request->getPost('password_confirm');

        $row = $this->validToken($token);
        if (! $row) return redirect()->to('/login')->with('flash_error', 'That reset link is invalid or has expired.');

        if (strlen($pass) < 6)  return redirect()->back()->with('flash_error', 'Password must be at least 6 characters.');
        if ($pass !== $conf)    return redirect()->back()->with('flash_error', 'Passwords do not match.');

        (new UserModel())->update((int) $row['user_id'], ['password_hash' => password_hash($pass, PASSWORD_BCRYPT)]);
        db_connect()->table('password_resets')->where('id', (int) $row['id'])->update(['used_at' => date('Y-m-d H:i:s')]);

        helper('system');
        log_action('password.reset_done', ['entity_type' => 'user', 'entity_id' => (int) $row['user_id'], 'description' => 'Password reset completed', 'severity' => 'warning']);

        return redirect()->to('/login')->with('flash_success', 'Your password has been reset. Please sign in.');
    }

    private function validToken(string $token): ?array
    {
        if ($token === '') return null;
        $row = db_connect()->table('password_resets')
            ->where('token', $token)
            ->where('used_at IS NULL', null, false)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray();
        return $row ?: null;
    }
}
