<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Offer;
use App\Models\Page;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Google_Client as GoogleClient;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


 
class PagesController extends Controller
{
    protected $firebaseNotificationService;

    public function __construct(FirebaseNotificationService $firebaseNotificationService)
    {
        $this->firebaseNotificationService = $firebaseNotificationService;
    }

    public function terms_and_conditions()
    {
        $page = Page::where('page', 'term_and_condition')->first();
        return view('panel.pages.terms_and_conditions.index', compact('page'));
    }

    public function terms_and_conditions_update(Request $request)
    {
        $rules = [
            '*' => 'required'
        ];

        $messages =[
            '*.required' => 'هذا الحقل مطلوب'
        ];

        $this->validate($request, $rules, $messages);

        try {
            $date = [
                'description_ar'=>$request->description_ar,
                'description_en'=>$request->description_en,
                'page'=> 'term_and_condition'
            ];

            Page::updateOrCreate(['page'=>'term_and_condition'],$date);

            return response()->json([
                "success" => true,
                "message" => "تمت العملية بنجاح",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function privacy()
    {
        $page = Page::where('page', 'privacy')->first();
        return view('panel.pages.privacy.index', compact('page'));
    }

    public function privacy_update(Request $request)
    {
        $rules = [
            '*' => 'required'
        ];

        $messages =[
            '*.required' => 'هذا الحقل مطلوب'
        ];

        $this->validate($request, $rules, $messages);

        try {
            $date = [
                'description_ar'=>$request->description_ar,
                'description_en'=>$request->description_en,
                'page' => 'privacy'
            ];

            Page::updateOrCreate(['page'=>'privacy'],$date);

            return response()->json([
                "success" => true,
                "message" => "تمت العملية بنجاح",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }
    public function index()
    {
        $blogs=Blog::all();
        return view('panel.pages.blog.index',compact('blogs'));
    }
    public function create()
    {
        return view('panel.pages.blog.create');

    }

    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blogs', 'public');
        }
    
         Blog::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image' => $imagePath,
            'is_active' => 1, 
        ]);
    
        return redirect()->route('admin.blogs')->with('success','تمت الاضافة بنحاح');
    }
    
      
      public function offers()
      {
          $offers = Offer::paginate(10);
          $users=User::all();
          return view('panel.pages.offers.index', compact('offers','users'));
      }
  
      public function createOffer()
      {
          return view('panel.pages.offers.create');
      }
  
      public function storeOffer(Request $request)
      {
          $validated = $request->validate([
              'title' => 'required',
              'body' => 'required',
              'is_active' => 'required|boolean',
              'start_date' => 'required|date',
              'end_date' => 'required|date',
          ]);
  
           $offer = Offer::create($validated);
  
          // Send notification via Firebase
          try {
              $this->firebaseNotificationService->sendNotification($offer->title, $offer->body);
          } catch (\Exception $e) {
              Log::error('Firebase Notification Error: ' . $e->getMessage());
          }
  
           return redirect()
              ->route('admin.offers')
              ->with('success', 'تم إضافة العرض بنجاح');
      }
  
      public function sendAll(Request $request)
      {
          $validated = $request->validate([
              'group_message' => 'required|string|max:255',
          ]);
  
          $title = 'عقدي';  
          $body = $validated['group_message'];
  
          try {
              $this->firebaseNotificationService->sendNotification($title, $body);
              return redirect()->back()->with('suceess','تم الأرسال بنجاح');

          } catch (\Exception $e) {
              Log::error('Firebase Notification Error: ' . $e->getMessage(), [
                  'title' => $title,
                  'body' => $body,
                  'trace' => $e->getTraceAsString(),
              ]);
              return redirect()->back()->with('error','فشل الارسال');
          }
      }

      public function send(Request $request)
      {
          // Validate the incoming request
          $validated = $request->validate([
              'user_id' => 'required|exists:users,id',
              'message' => 'required|string|max:255',
          ]);
      
           $notifiable = User::findOrFail($validated['user_id']);
          $message = $validated['message'];
      
          try {
               if (!$notifiable->fcm_token) {
                  throw new \Exception('لا يوجد fcm خاص بالمستخدم');
              }
      
               \App\Http\Traits\Notifiable::sendNotificationToFCM($notifiable->fcm_token, $message, $notifiable);
      
               session()->flash('success', 'تمت العملية بنجاح');
          } catch (\Exception $e) {
              // Log the error for debugging
              Log::error('Notification Error: ' . $e->getMessage(), [
                  'user_id' => $notifiable->id,
                  'message' => $message,
                  'trace' => $e->getTraceAsString(),
              ]);
      
               session()->flash('error', 'فشل الارسال' . ': ' . $e->getMessage());
          }
      
           return redirect()->back();
      }
      
  

    
   
}