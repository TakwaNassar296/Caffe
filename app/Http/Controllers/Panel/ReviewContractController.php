<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\User;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewContractController extends Controller
{

  
    public function review_done()
    {
         $review = Contract::GetReview()->paginate(10);
         foreach ($review as $contract) {
            $contract->totalContractPrice = $contract->getPriceContractAttribute();
        }
        return view('panel.pages.reviewContract.confiremd_review', compact('review'));
    }
    

  public function index()
{
     
    $review = Contract::where('is_completed', 1)
                      ->where('is_review', 0)
                       ->where('step', '>', 6)
                       ->orderBy('created_at', 'desc')
                      ->paginate(10);

     return view('panel.pages.reviewContract.index', compact('review'));
}


        public function sendFile($id)
        {
            $contract = Contract::findOrFail($id); 
            return view('panel.pages.reviewContract.File', compact('contract'));
        }

  
        public function fileContract(Request $request, $uuid)
        {
            $request->validate([
                'contract_uuid' => 'required|string|exists:contracts,uuid',
                'file' => 'required|mimes:pdf,doc,docx|max:10240',
            ]);
        
            $contract_uuid = $request->input('contract_uuid');
            $file = $request->file('file');
            

            $fileName = $contract_uuid . '_' . time() . '.' . $file->getClientOriginalExtension();
            

            $directory = 'public/uploads/fileContract';
            $filePath = $directory . '/' . $fileName;
        
            // Store the file
            $file->storeAs($directory, $fileName);
        
             $contract = Contract::where('uuid', $contract_uuid)->first();
        
            if (!$contract) {
                return redirect()->back()->with('error', 'عقد غير موجود');
            }
        
            //  $user = User::find($contract->user_id);
        
            // if (!$user) {
            //     return redirect()->back()->with('error', 'المستخدم غير موجود');
            // }
        
            // Delete the old file if it exists
            if ($contract->file) {
                $oldFilePath = $contract->file;
                if (Storage::exists($oldFilePath)) {
                    Storage::delete($oldFilePath);
                }
            }
        
            // Update the contract with the new file path
            $contract->file = $filePath;
            $contract->save();
        
            return redirect()->route('admin.daily.index')->with('success', 'تم إسناد العقد بنجاح');
        }
        

    public function confirm($id)
    {
        $contract = Contract::findOrFail($id);
    
         if (empty($contract->file)) {
            return redirect()->back()->with('error', 'العميل قام بدفع ثمن العقد لابد من أرسال العقد قبل انتهاء المعاينه');
        }
    
         $contract->update(['is_review' => true]);
    
        return redirect()->back()->with('success', 'تم التاكيد علي العقد وارساله للمستخدم');
    }
    
    
    public function daily()
    {
        $daily = Contract::where('created_at', '>=', now()->subDay())
        ->where('is_delete',0)
        ->where('step', '>', 6)
         ->orderBy('created_at', 'desc')
        ->paginate(10);
        return view('panel.pages.reviewContract.daily', compact('daily'));
    }

 

  
}
