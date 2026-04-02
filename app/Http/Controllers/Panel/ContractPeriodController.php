<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ContractPeriod;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContractPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('panel.pages.contract-period.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'period' => 'required',
            'note_ar' => 'required',
            'note_en' => 'required',
            'price' => 'required|integer',
            'contract_type' => 'required|in:housing,commercial',
        ];

        $this->validate($request, $rules);

        try {
            $data = [];
            $data = $request->only(['period', 'note_ar', 'note_en', 'price', 'contract_type']);

            ContractPeriod::create($data);

            return response()->json([
                "success" => true,
                "message" => 'تمت العملية بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContractPeriod $contractPeriod)
    {
        $rules = [
            'period' => 'required',
            'note_ar' => 'required',
            'note_en' => 'required',
            'price' => 'required|integer',
            'contract_type' => 'required|in:housing,commercial',
        ];

        $this->validate($request, $rules);

        try {
            $data = [];
            $data = $request->only(['period', 'note_ar', 'note_en', 'price', 'contract_type']);

            $contractPeriod->update($data);

            return response()->json([
                "success" => true,
                "message" => 'تمت العملية بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContractPeriod $contractPeriod)
    {
        try {

            $contractPeriod->delete();

            return response()->json([
                "success" => true,
                "message" => 'تمت العملية بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function datatable()
    {
        $contractPeriod = ContractPeriod::orderBy('id', 'desc')->get();

        return DataTables::of($contractPeriod)
            ->addIndexColumn()
            ->addColumn('action', function ($item) {
                $data_attr = '';
                $data_attr .= 'data-id="' . $item->id . '" ';
                $data_attr .= 'data-period="' . $item->period . '" ';
                $data_attr .= 'data-note_ar="' . $item->note_ar . '" ';
                $data_attr .= 'data-note_en="' . $item->note_en . '" ';
                $data_attr .= 'data-price="' . $item->price . '" ';
                $data_attr .= 'data-contract_type="' . $item->contract_type . '" ';
                $string = '';
               // Edit button (always visible)
            $string .= '<button class="edit_btn btn btn-sm btn-outline-primary mb-2 me-1" data-bs-toggle="modal"
            data-bs-target="#edit_modal" ' . $data_attr . '><i class="fa fa-edit"></i></button>';
            
            
            //     $string .= '<button class="edit_btn btn btn-sm btn-outline-primary mb-2 me-1" data-bs-toggle="modal"
            // data-bs-target="#edit_modal" ' . $data_attr . '><i class="fa fa-edit"></i></button>';
            
            //     $string .= ' <button type="button" class="delete_btn btn btn-sm btn-outline-danger mb-2 me-1"
            //  data-id="' . $item->id . '"><i class="fa fa-trash"></i></button>';
                 return $string;
            })->make(true);
    }


    //add value


    public function indexValue()
    {
        $valueContracts=Account::get();
        
        return view('panel.pages.accounts.index',compact('valueContracts'));
    }

   

    public function updateValue(Request $request, $id)
    {
        $request->validate([
            'valueContract' => 'required',
         
        ]);
    
        $valueContracts = Account::findOrFail($id);
        $valueContracts->valueContract = $request->input('valueContract');
       
        $valueContracts->save();
    
        return redirect()->route('admin.account.index')->with('success', 'تم تحديث القيمه بنجاح ');
    }
    
}
