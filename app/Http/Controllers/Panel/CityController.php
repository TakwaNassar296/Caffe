<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use App\Models\Region;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cities = City::paginate(33);
        $regions=Region::all();
        return view('panel.pages.cities.index', compact('cities','regions'));
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

        $this->validate($request, $rules,$messages);

        try {
            $citiesJson = Storage::disk('public')->get('citiesP.json');
            $data = json_decode($citiesJson, true);
            
            $data = [];
            $data = $request->only(['name_ar', 'name_en','region_id']);

            City::create($data);

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

    public function delete($id)
    {
        $city = City::findOrFail($id);
        $city->delete();
    
        return redirect()
            ->back()
            ->with('success', 'تمت العملية بنجاح');
    }
    
}