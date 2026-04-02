<?php

namespace App\Http\Controllers\panel;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $valueContracts=Account::get();
        
        return view('panel.pages.accounts.index',compact('valueContracts'));
    }

    public function edit($id)
{
    $item = Account::findOrFail($id);
    return view('panel.pages.accounts.edit', compact('item'));
} 


    public function update(Request $request, $id)
    {
         $request->validate([
            'valueContract' => 'required|numeric',
        ]);

         $valueContract = Account::findOrFail($id);

         $valueContract->valueContract = $request->input('valueContract');
        $valueContract->save();

         return redirect()->back()->with('success', 'تم تحيث القيمه بنجاح!');
    }
}
