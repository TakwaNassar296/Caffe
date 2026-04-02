<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ServicesPricing;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ServicesPricingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('panel.pages.services-pricings.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name_ar' => 'required',
            'name_en' => 'required',
            'price' => 'required',
            'contract_type' => 'required|in:housing,commercial',
        ];

        $messages = [
            '*.required' => 'هذا الحقل مطلوب'
        ];

        $this->validate($request, $rules, $messages);

        try {
            $data = $request->only(['name_ar', 'name_en', 'price', 'contract_type']);

            ServicesPricing::create($data);

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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServicesPricing $servicesPricing)
    {
        $rules = [
            'name_ar' => 'required',
            'name_en' => 'required',
            'price' => 'required',
            'contract_type' => 'required|in:housing,commercial',
        ];

        $messages = [
            '*.required' => 'هذا الحقل مطلوب'
        ];

        $this->validate($request, $rules, $messages);

        try {
            $data = $request->only(['name_ar', 'name_en', 'price', 'contract_type']);

            $servicesPricing->update($data);

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServicesPricing $servicesPricing)
    {
        try {

            $servicesPricing->delete();

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
        $servicesPricing =ServicesPricing::orderBy('id', 'desc')->get();

        return DataTables::of($servicesPricing)
            ->addIndexColumn()
            ->addColumn('action', function ($item) {
                $data_attr = '';
                $data_attr .= 'data-id="' . $item->id . '" ';
                $data_attr .= 'data-name_ar="' . $item->name_ar . '" ';
                $data_attr .= 'data-name_en="' . $item->name_en . '" ';
                $data_attr .= 'data-price="' . $item->price . '" ';
                $data_attr .= 'data-contract_type="' . $item->contract_type . '" ';
                $string = '';
                $string .= '<button class="edit_btn btn btn-sm btn-outline-primary mb-2 me-1" data-bs-toggle="modal"
            data-bs-target="#edit_modal" ' . $data_attr . '><i class="fa fa-edit"></i></button>';
                $string .= ' <button type="button" class="delete_btn btn btn-sm btn-outline-danger mb-2 me-1"
            data-id="' . $item->id . '"><i class="fa fa-trash"></i></button>';
                return $string;
            })->make(true);
    }
}
