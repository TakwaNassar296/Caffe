<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ReaEstatType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReaEstatTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('panel.pages.rea-estat-type.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name_ar' => 'required',
            'contract_type' => 'required|in:housing,commercial',
        ];

        $this->validate($request, $rules);

        try {
            $data = [];
            $data['is_rooms'] = $request->has('is_rooms') ? 1 : 0;

            $data = $request->only(['name_ar', 'contract_type']);
            $data['is_room'] = $request->has('is_room') ? 1 : 0;

            ReaEstatType::create($data);

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
    public function update(Request $request, ReaEstatType $reaEstatType)
    {
        $rules = [
            'name_ar' => 'required',
            'contract_type' => 'required|in:housing,commercial',
        ];

        $this->validate($request, $rules);

        try {
            $data = [];
            $data = $request->only(['name_ar', 'contract_type']);

            $reaEstatType->update($data);

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
    public function destroy(ReaEstatType $reaEstatType)
    {
        try {

            $reaEstatType->delete();

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
        $reaEstatTypes = ReaEstatType::orderBy('id', 'desc')->get();

        return DataTables::of($reaEstatTypes)
            ->addIndexColumn()
            ->addColumn('action', function ($item) {
                $data_attr = '';
                $data_attr .= 'data-id="' . $item->id . '" ';
                $data_attr .= 'data-name_ar="' . $item->name_ar . '" ';
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
