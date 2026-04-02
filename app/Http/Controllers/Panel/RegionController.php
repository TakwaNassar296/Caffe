<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Region;
use Illuminate\Http\Request;

use Yajra\DataTables\Facades\DataTables;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cities = City::all();
        return view('panel.pages.regions.index', compact('cities'));
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
            $data = $request->only(['name_ar', 'name_en']);

            Region::create($data);

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
    public function update(Request $request, Region $region)
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
            $data = $request->only(['name_ar', 'name_en', 'city_id']);

            $region->update($data);

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
    public function destroy(Region $region)
    {
        try {

            $region->delete();

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
        $regions =Region::orderBy('id', 'desc')->get();

        return DataTables::of($regions)
            ->addIndexColumn()
            ->addColumn('action', function ($item) {
                $data_attr = '';
                $data_attr .= 'data-id="' . $item->id . '" ';
                $data_attr .= 'data-name_ar="' . $item->name_ar . '" ';
                $data_attr .= 'data-name_en="' . $item->name_en . '" ';
                $data_attr .= 'data-city="' . $item->city_id . '" ';
                $string = '';
                $string .= '<button class="edit_btn btn btn-sm btn-outline-primary mb-2 me-1" data-bs-toggle="modal"
            data-bs-target="#edit_modal" ' . $data_attr . '><i class="fa fa-edit"></i></button>';
                $string .= ' <button type="button" class="delete_btn btn btn-sm btn-outline-danger mb-2 me-1"
            data-id="' . $item->id . '"><i class="fa fa-trash"></i></button>';
                return $string;
            })->make(true);
    }
}
