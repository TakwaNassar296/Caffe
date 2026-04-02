<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('panel.pages.bank-accounts.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            '*' => 'required'
        ];

        $messages = [
            '*.required' => 'هذا الحقل مطلوب'
        ];

        $this->validate($request, $rules, $messages);

        try {
            $data = [];
            $data = $request->only([
                'bank_name_ar',
                'bank_name_en',
                'bank_account_name_ar',
                'bank_account_name_en',
                'bank_account_number',
                'iban_number',
            ]);

            BankAccount::create($data);

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
    public function update(Request $request, BankAccount $bankAccount)
    {
        $rules = [
            '*' => 'required'
        ];

        $messages = [
            '*.required' => 'هذا الحقل مطلوب'
        ];

        $this->validate($request, $rules, $messages);

        try {
            $data = [];
            $data = $request->only([
                'bank_name_ar',
                'bank_name_en',
                'bank_account_name_ar',
                'bank_account_name_en',
                'bank_account_number',
                'iban_number',
            ]);

            $bankAccount->update($data);

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
    public function destroy(BankAccount $bankAccount)
    {
        try {

            $bankAccount->delete();

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
        $accounts = BankAccount::orderBy('id', 'desc')->get();

        return DataTables::of($accounts)
            ->addIndexColumn()
            ->addColumn('action', function ($item) {
                $data_attr = '';
                $data_attr .= 'data-id="' . $item->id . '" ';
                $data_attr .= 'data-bank_name_ar="' . $item->bank_name_ar . '" ';
                $data_attr .= 'data-bank_name_en="' . $item->bank_name_en . '" ';
                $data_attr .= 'data-bank_account_name_ar="' . $item->bank_account_name_ar . '" ';
                $data_attr .= 'data-bank_account_name_en="' . $item->bank_account_name_en . '" ';
                $data_attr .= 'data-bank_account_number="' . $item->bank_account_number . '" ';
                $data_attr .= 'data-iban_number="' . $item->iban_number . '" ';
                $string = '';
                $string .= '<button class="edit_btn btn btn-sm btn-outline-primary mb-2 me-1" data-bs-toggle="modal"
            data-bs-target="#edit_modal" ' . $data_attr . '><i class="fa fa-edit"></i></button>';
                $string .= ' <button type="button" class="delete_btn btn btn-sm btn-outline-danger mb-2 me-1"
            data-id="' . $item->id . '"><i class="fa fa-trash"></i></button>';
                return $string;
            })->make(true);
    }
}
