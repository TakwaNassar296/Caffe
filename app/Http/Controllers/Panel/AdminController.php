<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('panel.pages.admins.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:admins,email',
            'mobile' => 'required',
            'photo' => 'required|mimes:png,jpeg,jpg',
            'password' => 'required|confirmed',
        ];

        $this->validate($request, $rules);

        try {
            $data = [];
            $data = $request->only(['name', 'email', 'mobile']);

            if ($request->hasFile('photo')) {
                $file = $request->photo;
                $path = fileUploader($file, 'admins');
                $data['photo'] = $path;
            }
            $data['password'] = bcrypt($request->password);

            Admin::create($data);

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
    public function update(Request $request, Admin $admin)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'mobile' => 'required',
            'photo' => 'mimes:png,jpeg,jpg',
            'password' => 'confirmed',
        ];

        $this->validate($request, $rules);

        try {
            $data = [];
            $data = $request->only(['name', 'email', 'mobile']);

            if ($request->hasFile('photo')) {
                $file = $request->photo;
                $path = fileUploader($file, 'admins');
                $data['photo'] = $path;
            }

            if ($request->get('password')) {
                $data['password'] = bcrypt($request->password);
            }

            if (isset($admin->photo)) {
                deleteFile($admin->photo);
            }

            $admin->update($data);

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
    public function destroy(Admin $admin)
    {
        try {

            if (isset($admin->photo)) {
                deleteFile($admin->photo);
            }

            $admin->delete();

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
        $admins = Admin::orderBy('id', 'desc')->get();

        return DataTables::of($admins)
            ->addIndexColumn()
            ->addColumn('action', function ($item) {
                $data_attr = '';
                $data_attr .= 'data-id="' . $item->id . '" ';
                $data_attr .= 'data-name="' . $item->name . '" ';
                $data_attr .= 'data-email="' . $item->email . '" ';
                $data_attr .= 'data-mobile="' . $item->mobile . '" ';
                $data_attr .= 'data-photo="' . $item->photo_path . '" ';
                $string = '';
                $string .= '<button class="edit_btn btn btn-sm btn-outline-primary mb-2 me-1" data-bs-toggle="modal"
            data-bs-target="#edit_modal" ' . $data_attr . '><i class="fa fa-edit"></i></button>';
                $string .= ' <button type="button" class="delete_btn btn btn-sm btn-outline-danger mb-2 me-1"
            data-id="' . $item->id . '"><i class="fa fa-trash"></i></button>';
                return $string;
            })->make(true);
    }
}
