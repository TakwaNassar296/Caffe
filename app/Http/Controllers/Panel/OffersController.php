<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OfferController extends Controller
{
     public function index()
    {
        $offers = Offer::paginate(10);
        return view('panel.pages.offers.index', compact('offers'));
    }

    public function create()
    {
        return view('panel.pages.offers.create');
    }

    public function store(Request $request)
    {
         $validated = $request->validate([
            'title' => 'required',
            'body' => 'required',
            'is_active' => 'required|boolean',
            'start_date' => 'required|date',
            'end_date' => 'required',
         ]);

        // Handle image upload
        $data = $validated;
        

        // Create the offer
        $offer = Offer::create($data);
        // Send notification via Firebase
         try {
            $this->sendFirebaseNotification($offer->title, $offer->body);
        } catch (\Exception $e) {
            Log::error('Firebase Notification Error: ' . $e->getMessage());
        }

        // Redirect with success message
        return redirect()
            ->route('admin.offers')
            ->with('success', 'تم إضافة العرض بنجاح');
    }

    private function sendFirebaseNotification($title, $body)
    {
         
        // Ensure the path to your service account key is correct
        $credentialsPath = storage_path('app/contrat-77651-348b72e9ab54.json');
        
        if (!file_exists($credentialsPath)) {
            throw new \Exception('Firebase credentials file not found');
        }

        try {
            // Create a new Google Client
            $client = new GoogleClient();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            // Authorize and get access token
            $client->refreshTokenWithAssertion();
            $accessToken = $client->getAccessToken();

            if (!isset($accessToken['access_token'])) {
                throw new \Exception('Failed to obtain access token');
            }

            // Prepare notification payload
            $message = [
                'message' => [
                    'topic' => 'all',
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'android' => [
                        'priority' => 'high'
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'category' => 'new_offer'
                            ]
                        ]
                    ]
                ]
            ];

            // Use Guzzle for more reliable HTTP requests
            $httpClient = new GuzzleClient();
            $response = $httpClient->post('https://fcm.googleapis.com/v1/projects/contrat-77651/messages:send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken['access_token'],
                    'Content-Type' => 'application/json'
                ],
                'json' => $message
            ]);

            // Log successful response
            Log::info('Firebase Notification Sent Successfully', [
                'title' => $title,
                'response' => $response->getBody()->getContents()
            ]);

            return true;
        } catch (\Exception $e) {
            // Log any errors that occur during notification sending
            Log::error('Firebase Notification Error: ' . $e->getMessage(), [
                'title' => $title,
                'body' => $body,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}