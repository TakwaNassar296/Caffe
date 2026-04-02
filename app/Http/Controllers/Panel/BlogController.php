<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    // Display Blogs
    public function index(): \Illuminate\View\View
    {
        $blogs = Blog::all();
        return view('panel.pages.blog.index', compact('blogs'));
    }

    // Create New Blog Entry
    public function create(): \Illuminate\View\View
    {
        return view('panel.pages.blog.create'); // View for creating a new blog
    }

    // Store New Blog Entry
    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'images' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image input
    ]);

    // Store the image
    if ($request->hasFile('images')) {
        $image = $request->file('images');
        $imagePath = $image->store('blogs', 'public'); // Store in 'public/uploads' folder
    }

    // Create a new blog entry (adjust based on your model)
    Blog::create([
        'title' => $request->input('title'),
        'description' => $request->input('description'),
        'image' => $imagePath ?? null, // Store image path if uploaded
    ]);

     return redirect()->route('seo.blogs')->with('success', 'تم الأضافة بنجاح'); // Updated message                  

}

    // Update Existing Blog Entry
    public function update(Request $request, $id)
    {
          
        $rules = [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'images' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $messages = [
            'title.required' => 'عنوان المدونة مطلوب.',
            'description.required' => 'وصف المدونة مطلوب.',
            'images.image' => 'يجب أن يكون الملف صورة.',
            'images.mimes' => 'يمكن أن تكون الصورة بأحد التنسيقات: jpeg, png, jpg, gif, svg.',
            'images.max' => 'يجب ألا يتجاوز حجم الصورة 2 ميجابايت.',
        ];

        $request->validate($rules, $messages);

        try {
            $blog = Blog::findOrFail($id);

            $data = [
                'description' => $request->description,
                'title' => $request->title,
            ];

            if ($request->hasFile('images')) {
                // Delete the old image if exists
                if ($blog->images) {
                    Storage::disk('public')->delete($blog->images);
                }

                // Store the new image
                $data['images'] = $request->file('images')->store('blogs', 'public');
            }

            $blog->update($data);

            return redirect()->route('seo.blogs')->with('success', 'تم التحديث بنجاح'); // Updated message                  

        } catch (\Throwable $e) {
            return response()->json([
                "success" => false,
                "message" => "حدث خطأ أثناء التحديث: " . $e->getMessage(),
            ]);
        }
    }

    // Delete a Blog Entry
    public function destroy($id)
    {
        try {
            $blog = Blog::findOrFail($id);

            // Delete the blog image if exists
            if ($blog->images) {
                Storage::disk('public')->delete($blog->images);
            }

            // Delete the blog entry
            $blog->delete();

       return redirect()->back()->with('success', 'تم الحذف بنجاح'); // Updated message           
        } catch (\Throwable $e) {
          return redirect()->back()->with('success', 'فشل الحذف بنجاح'); // Updated message  
          }
    }

    // Show Edit Form for a Blog
    public function edit($id) 
    {
        $blog = Blog::findOrFail($id);
        return view('panel.pages.blog.edit', compact('blog'));
    }
}
