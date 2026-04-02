<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ReaEstatUsage;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReaEstatUsageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('panel.pages.rea-estat-usage.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name_ar' => 'required',
            'name_en' => 'required',
            'contract_type' => 'required|in:housing,commercial',
        ];

        $this->validate($request, $rules);

        try {
            $data = [];
            $data = $request->only(['name_ar', 'name_en', 'contract_type']);

            ReaEstatUsage::create($data);

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
    public function update(Request $request, ReaEstatUsage $reaEstatUsage)
    {
        $rules = [
            'name_ar' => 'required',
            'name_en' => 'required',
            'contract_type' => 'required|in:housing,commercial',
        ];

        $this->validate($request, $rules);

        try {
            $data = [];
            $data = $request->only(['name_ar', 'name_en', 'contract_type']);

            $reaEstatUsage->update($data);

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
    public function destroy(ReaEstatUsage $reaEstatUsage)
    {
        try {

            $reaEstatUsage->delete();

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
        $reaEstatUsages = ReaEstatUsage::orderBy('id', 'desc')->get();

        return DataTables::of($reaEstatUsages)
            ->addIndexColumn()
            ->addColumn('action', function ($item) {
                $data_attr = '';
                $data_attr .= 'data-id="' . $item->id . '" ';
                $data_attr .= 'data-name_ar="' . $item->name_ar . '" ';
                $data_attr .= 'data-name_en="' . $item->name_en . '" ';
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
