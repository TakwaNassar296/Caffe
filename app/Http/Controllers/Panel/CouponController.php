<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::where('is_delete', 0)->paginate(10);
        return view('panel.pages.coupon.index', compact('coupons'));
    }

    

    public function create()
    {
         return view('panel.pages.coupon.create');   
    
    }

   
    public function store(Request $request )
    {
        $rules = [
            '*' => 'required'
        ];
        
        $messages = [
            '*.required' => 'هذا الحقل مطلوب'
        ];
        
        $this->validate($request, $rules,$messages);
        
        try {
            
             
            $data = [];
            $data = $request->only([ 'name', 'code_coupon', 'type_coupon', 'value_coupon', 'date_start', 'date_end','usage','usage_of_user' ]);
        
            Coupon::create($data);
        
            return redirect()->back()->with('success','تمت الأضافة بنجاح');

        } catch (\Exception $e) {
            return redirect()->back()->with(["error" => false,"message" => $e->getMessage()]);

    }
}

    public function status($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            $coupon->is_delete=$coupon->is_delete== 0 ? 1 : 0;          
            $coupon->save();

            return redirect()->back()->with('success', 'تم تحديث الحالة بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تحديث الحالة: ' . $e->getMessage());
        }
    }


    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('panel.pages.coupon.edit',compact('coupon'));
        
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
            'code_coupon' => 'required',
            'type_coupon' => 'required|in:value,ratio',  
            'value_coupon' => 'required|numeric',
            'date_start' => 'required|date',
            'date_end' => [
                'required',
                'date',
                'after_or_equal:date_start',
                'after_or_equal:' . now()->format('Y-m-d'),],
            'usage' => 'required|integer|min:1',
            'usage_of_user' => 'required|integer|min:1',
        ];
    
        $messages = [
            '*.required' => 'هذا الحقل مطلوب',
            'type_coupon.in' => 'نوع الخصم يجب أن يكون "value" أو "ratio"',
            'value_coupon.numeric' => 'قيمة الخصم يجب أن تكون رقمية',
            'date_start.date' => 'تاريخ بداية الخصم يجب أن يكون تاريخ صحيح',
            'date_end.date' => 'تاريخ نهاية الخصم يجب أن يكون تاريخ صحيح',
            'date_end.after_or_equal' => 'يجب أن يكون تاريخ الانتهاء بعد أو يساوي تاريخ البداية ولا يكون في الماضي.',
            'usage.integer' => 'عدد مرات استخدام الخصم يجب أن يكون عدداً صحيحاً',
            'usage.min' => 'عدد مرات استخدام الخصم يجب أن لا يقل عن صفر',
            'usage_of_user.integer' => 'عدد مرات استخدام الخصم لكل مستخدم يجب أن يكون عدداً صحيحاً',
            'usage_of_user.min' => 'عدد مرات استخدام الخصم لكل مستخدم يجب أن لا يقل عن صفر',
        ];
    
        $this->validate($request, $rules, $messages);
    
        try {
            $coupon = Coupon::findOrFail($id);
    
            $coupon->update([
                'name' => $request->input('name'),
                'code_coupon' => $request->input('code_coupon'),
                'type_coupon' => $request->input('type_coupon'),
                'value_coupon' => $request->input('value_coupon'),
                'date_start' => $request->input('date_start'),
                'date_end' => $request->input('date_end'),
                'usage' => $request->input('usage'),
                'usage_of_user' => $request->input('usage_of_user'),
            ]);
    
            return redirect()->route('admin.coupon.index')->with('success', 'تمت الإضافة بنجاح');

        } catch (\Exception $e) {
            return redirect()->back()->with(["error" => false,"message" => $e->getMessage()]);

        }
    }

    //show coupon Usage
    public function couponUsage()
    {
        $usage = CouponUsage::paginate(15);
        return view('panel.pages.coupon.usage',compact('usage'));   

    }

}
 