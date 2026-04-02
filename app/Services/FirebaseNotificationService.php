<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use Google\Client as GoogleClient; 
use Illuminate\Support\Facades\Log;
use Exception;

class FirebaseNotificationService
{
    protected $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/gettin-caffe-firebase-adminsdk-fbsvc-6bfe3eefdb.json');
    }

    public function sendNotification($title, $body)
    {
        if (!file_exists($this->credentialsPath)) {
            Log::error('Firebase credentials file missing', ['path' => $this->credentialsPath]);
            throw new Exception("File not found at: " . $this->credentialsPath);
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($this->credentialsPath);
            
            
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            $guzzleClient = new GuzzleClient(['verify' => false]); 
            $client->setHttpClient($guzzleClient);

            $accessTokenArray = $client->fetchAccessTokenWithAssertion();

            if (!isset($accessTokenArray['access_token'])) {
                Log::error('Firebase Auth Error details:', $accessTokenArray);
                throw new Exception("Failed to obtain access token: " . ($accessTokenArray['error_description'] ?? 'Unknown Error'));
            }

            $accessToken = $accessTokenArray['access_token'];
            $payload = [
                'message' => [
                    'topic' => 'all',
                    'notification' => [
                        'title' => (string)$title,
                        'body' => (string)$body,
                    ],
                    'android' => [
                        'priority' => 'high',
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                            ],
                        ],
                    ],
                ],
            ];

            $response = $guzzleClient->post('https://fcm.googleapis.com/v1/projects/getin-943cd/messages:send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            Log::info('Firebase Notification Sent Successfully');

        } catch (Exception $e) {
            Log::error('Firebase Notification Error: ' . $e->getMessage());
            throw $e;
        }
    }
}