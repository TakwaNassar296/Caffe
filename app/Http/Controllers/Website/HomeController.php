<?php

namespace App\Http\Controllers\Website;

use \Log;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\City;
 
use App\Models\Contract;
use App\Models\Overview;
use App\Models\Page;
use App\Models\PagePaperwork;
use App\Models\Paperwork;
use App\Models\Question;
use App\Models\RealEstate;
use App\Models\UnitsReal;
use App\Models\User;
use App\Models\Video;
use App\Notifications\ContractNotification;
use Illuminate\Http\Request;
use App\Models\Visitor;

class HomeController extends Controller
{

      
    public function home(){
         
        $ip_address = request()->ip();
        $visitor = Visitor::where('ip_address', $ip_address)->first();
    
        if ($visitor) {
        } else {
            Visitor::create([
                'ip_address' => $ip_address,
                'time_visit' => 1,  
            ]);
        }
        
        $overview=Overview::paginate(8);
        
        $real = RealEstate::with('contracts')->get();
        $unit = UnitsReal::with('contracts')->get();

        return view('website.pages.home',compact('real','unit','overview'));
    }


     public function blog()
     {
        $blogs = Blog::orderBy('created_at', 'desc')->get();
        return view('website.pages.blogs',compact('blogs'));
     }

      public function singelblog($slug)
     {
         $blog = Blog::where('slug', $slug)->firstOrFail();
     
         $relatedBlogs = Blog::where('slug', '!=', $slug)->limit(3)->get();
     
         return view('website.pages.singleblogs', compact('blog', 'relatedBlogs'));
     }
     
     

    public function aboutUs(){
        return view('website.pages.aboutUs');
    }
 
     public function qa()
    {
        $Questions=Question::all();
        return view('website.pages.qa',compact('Questions'));
 
      }


    public function terms()
    {
         $termsConditions = Page::where('page', 'term_and_condition')->first();
        $descriptions = $termsConditions ? $termsConditions->description_trans : '';
    
        return view('website.pages.tearms', compact('descriptions'));
    }
    
    
    
   

  
}