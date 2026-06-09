<?php

/**
 * notify(...) — create an in-app notification.
 *
 * Pass user_id = null to broadcast to all admin staff (everyone with a user account sees it).
 * Returns the new notification ID, or 0 on failure.
 */
if (! function_exists('notify')) {
    function notify(array $opts): int
    {
        try {
            $model = new \App\Modules\System\Models\NotificationModel();
            $id = (int) $model->insert([
                'user_id'    => isset($opts['user_id']) ? (int) $opts['user_id'] : null,
                'type'       => $opts['type']  ?? 'info',
                'title'      => (string) ($opts['title'] ?? ''),
                'body'       => $opts['body']  ?? null,
                'link'       => $opts['link']  ?? null,
                'icon'       => $opts['icon']  ?? null,
                'color'      => $opts['color'] ?? 'gray',
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ], true);

            // Mirror the in-app notification to mobile push (best-effort).
            push_notify(
                isset($opts['user_id']) ? (int) $opts['user_id'] : null,
                (string) ($opts['title'] ?? ''),
                (string) ($opts['body'] ?? ''),
                ['type' => $opts['type'] ?? 'info', 'link' => $opts['link'] ?? null]
            );

            return $id;
        } catch (\Throwable $e) {
            log_message('error', 'notify() failed: ' . $e->getMessage());
            return 0;
        }
    }
}

/**
 * Resolve target device tokens for a notification and send an Expo push.
 * $userId null = broadcast to all active users with a token.
 * Best-effort: never throws, short timeout, swallows errors.
 */
if (! function_exists('push_notify')) {
    function push_notify(?int $userId, string $title, string $body, array $data = []): void
    {
        try {
            $db = db_connect();
            $b  = $db->table('users')->select('expo_push_token')
                ->where('expo_push_token IS NOT NULL')
                ->where("expo_push_token != ''")
                ->where('status', 'active');
            if ($userId) $b->where('id', $userId);
            $rows = $b->get()->getResultArray();

            $tokens = array_values(array_unique(array_filter(array_column($rows, 'expo_push_token'))));
            if (! $tokens) return;

            send_expo_push($tokens, $title, $body, $data);
        } catch (\Throwable $e) {
            log_message('error', 'push_notify() failed: ' . $e->getMessage());
        }
    }
}

/**
 * Send an Expo push message to one or more Expo push tokens.
 * https://docs.expo.dev/push-notifications/sending-notifications/
 */
if (! function_exists('send_expo_push')) {
    function send_expo_push(array $tokens, string $title, string $body, array $data = []): void
    {
        $messages = [];
        foreach ($tokens as $t) {
            if (! str_starts_with((string) $t, 'ExponentPushToken') && ! str_starts_with((string) $t, 'ExpoPushToken')) {
                continue; // not a valid Expo token
            }
            $messages[] = [
                'to'       => $t,
                'title'    => $title,
                'body'     => $body,
                'sound'    => 'default',
                'priority' => 'high',
                'channelId'=> 'default',
                'data'     => $data,
            ];
        }
        if (! $messages) return;

        try {
            $ch = curl_init('https://exp.host/--/api/v2/push/send');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS     => json_encode(count($messages) === 1 ? $messages[0] : $messages),
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            log_message('error', 'send_expo_push() failed: ' . $e->getMessage());
        }
    }
}

/**
 * log_action(...) — append an entry to the system audit log.
 *
 * Always non-throwing — a logging failure should never break a request.
 */
if (! function_exists('log_action')) {
    function log_action(string $action, array $opts = []): void
    {
        try {
            $req = service('request');
            (new \App\Modules\System\Models\SystemLogModel())->insert([
                'user_id'      => session('user.id') ?: null,
                'user_name'    => session('user.name') ?: null,
                'action'       => $action,
                'entity_type'  => $opts['entity_type'] ?? null,
                'entity_id'    => isset($opts['entity_id']) ? (int) $opts['entity_id'] : null,
                'description'  => (string) ($opts['description'] ?? ''),
                'ip_address'   => $req ? $req->getIPAddress() : null,
                'user_agent'   => $req ? substr((string) $req->getUserAgent(), 0, 255) : null,
                'payload_json' => isset($opts['payload']) ? json_encode($opts['payload'], JSON_UNESCAPED_UNICODE) : null,
                'severity'     => in_array(($opts['severity'] ?? 'info'), ['info','warning','error'], true) ? ($opts['severity'] ?? 'info') : 'info',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Best-effort — silently log to file.
            log_message('error', 'log_action() failed: ' . $e->getMessage());
        }
    }
}

/**
 * Convenience: notify all admin staff. Used when something needs salon-wide visibility
 * (new booking, new review, etc).
 */
if (! function_exists('notify_broadcast')) {
    function notify_broadcast(array $opts): int
    {
        $opts['user_id'] = null;
        return notify($opts);
    }
}
