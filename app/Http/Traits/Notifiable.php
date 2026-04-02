<?php

namespace App\Http\Traits;

use App\Notifications\UserNotification;

trait Notifiable
{

    static function sendNotificationToFCM($fcm_token, $message, $notifiable)
    {
        $title = trans('api.app');
        $result = null;
        try {

            $endpoint = 'https://fcm.googleapis.com/fcm/send';

            $headers = [
                'Authorization: key=' . config('services.firebase.secret'),
                'Content-Type: application/json'
            ];

            $notification = [
                'title' => $title,
                'body' => $message,
                'badge' => 1,
            ];

            $payload = [
                'notification' => $notification,
                "registration_id" => $fcm_token,
            ];

            if (!empty($fcm_token)) {

                // $data = [
                //     "registration_id" => $fcm_token,
                //     'to' => $fcm_token,
                //     "data" => [
                //         'title' => $title,
                //         'body' => $message,
                //         'badge' => 1,
                //     ],
                //     "notification" =>
                //     [
                //         'title' => $title,
                //         'body' => $message,
                //         'badge' => 1,
                //     ]
                // ];

                $notifiable->notify(new UserNotification($title, $message));

                // $ch = curl_init();
                $ch = curl_init($endpoint);
                // curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                $result = curl_exec($ch);
                curl_close($ch);
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }

        return $result;
    }
}
