<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Traits\Responser;
use App\Models\Overview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Models\Blog;

class OverviewController extends Controller
{
    use Responser;

    public function index()
    {
        $overviews = Overview::paginate(10);
        return view('panel.pages.overview.index', compact('overviews'));
    } 


    public function create(Request $request)
   {
    $data = $request->only([
        'name_overview','image','value' 
     ]);

    if ($request->hasFile('image')) {
        $data['image'] = fileUploader($request->image, 'overview');
    }

    Overview::create($data);
   


    return redirect()->route('admin.overview.index')->with('success', 'تم أضاافة القسم بنجاح');
}

    

    public function delete($id)
    {
        $category = Overview::findOrFail($id);
     
        if ($category->image) {
            deleteFile($category->image);  
        }
    
        $category->delete();
        return redirect()->route('admin.overview.index')->with('success', 'تم حذف الفئه بنجاح');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name_overview' => 'required|string|max:255',
            'value'=>'required',
        ]);
    
        $overview = Overview::findOrFail($id);
        $overview->name_overview = $request->input('name_overview');
        $overview->value = $request->input('value');
        if ($request->hasFile('image')) {
            if ($overview->image && Storage::exists($overview->image)) {
                deleteFile($overview->image);
           }
   
            $overview->image = fileUploader($request->file('image'), 'overview');
       }
        $overview->save();
    
        return redirect()->route('admin.overview.index')->with('success', 'تم تحديث الفئه بنجاح ');
    }
    
    public function indexblog() 
    {
         $blogs = Blog::orderBy('created_at', 'desc')->get(); 
         return view('panel.pages.blog.index', compact('blogs'));
    }

     
    public function createblog(): \Illuminate\View\View
    {
        return view('panel.pages.blog.create');  
    }

  
    public function storeblog(Request $request)
    {
        $request->validate([
              'title' => 'required',
              'description' => 'required',
              'image' => 'nullable',
              'meta_title' => 'nullable|string|max:255',
              'meta_description' => 'nullable|string|max:500',
        ]);
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blogs', 'public');
        }
    
        Blog::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image' => $imagePath,
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'is_active' => 1,
        ]);
    
        return redirect()->route('seo.blogs')->with('success', 'تمت الإضافة بنجاح');
    }

     public function updateblog(Request $request, $id)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required',
            'meta_description' => 'nullable',
            'meta_title' => 'nullable',
            'image' => 'nullable',
        ];

        $messages = [
            'title.required' => 'عنوان المدونة مطلوب.',
            'description.required' => 'وصف المدونة مطلوب.',
            'image.image' => 'يجب أن يكون الملف صورة.',
            'image.mimes' => 'يمكن أن تكون الصورة بأحد التنسيقات: jpeg, png, jpg, gif, svg.',
            'image.max' => 'يجب ألا يتجاوز حجم الصورة 2 ميجابايت.',
        ];

        $request->validate($rules, $messages);

        try {
            $blog = Blog::findOrFail($id);

            $data = [
                'description' => $request->description,
                'title' => $request->title,
                'meta_title'=>$request->meta_title,
                'meta_description'=>$request->meta_description,
            ];

     if ($request->hasFile('image')) {
            if ($blog->image) {
                Storage::disk('public')->delete($blog->image);
            }

            $data['image'] = $request->file('image')->store('blogs', 'public');
        }


            $blog->update($data);

            return redirect()->back()->with('success', 'تم التحديث بنجاح');               

        } catch (\Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => "حدث خطأ أثناء التحديث: " . $e->getMessage(),
            ]);
        }
    }

    // Delete a Blog Entry
    public function destroyblog($id)
    {
        try {
            $blog = Blog::findOrFail($id);
    
            if ($blog->images) {
                Storage::disk('public')->delete($blog->images);
            }
    
            $blog->delete();
    
            return redirect()->route('seo.blogs')->with('success', 'تم الحذف بنجاح');
        } catch (\Throwable $e) {
            return redirect()->route('seo.blogs')->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    // Show Edit Form for a Blog
    public function editblog($id): \Illuminate\View\View
    {
        $blog = Blog::findOrFail($id);
        return view('panel.pages.blog.edit', compact('blog'));
    }
}
