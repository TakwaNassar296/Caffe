<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
         
        return view('panel.pages.users.index', compact('users'));
    }
    
      public function contractUser($id){
          
        $user = User::find($id);
        $contracts = Contract::where('user_id', $user->id)->paginate(10);
        return view('panel.pages.users.contract', compact('contracts'));
    }
    

    public function changeStatus(Request $request)
    {
        $user = User::findOrFail($request->id);
        try {
            $user->update([
                'is_active' => !$user->is_active
            ]);
            return response()->json([
                "success" => true,
                "message" => 'تم تغير الحاله بنجاح',
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
        $users = User::orderBy('id', 'desc')->get();

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('action', function ($user) {
                // if ($user->trashed()) {
                //     return '<button class="restore_btn btn btn-sm btn-outline-warning" data-id="' . $user->id . '">' . trans('dashboard.restore') . '</button>';
                // } else {
                return '--';
                // }
            })
            ->editColumn('mobile', function ($user) {
                return '<bdi>' . $user->mobile . '</bdi>';
            })->editColumn('is_active', function ($user) {
                if ($user->is_active) {
                    return '<div class="form-check form-switch"><input type="checkbox" data-id="' . $user->id . '" data-is-active="' . $user->is_active .
                        '" class="form-check-input isActiveUser" checked></div>';
                } else {
                    return '<div class="form-check form-switch"> <input type="checkbox" data-id="' . $user->id . '" data-is-active="' . $user->is_active .
                        '" class="form-check-input isActiveUser"></div>';
                }
            })->editColumn('created_at', function ($user) {
                return $user->created_at->format('Y-m-d');
            })->rawColumns(['mobile', 'is_active', 'action'])
            ->make(true);
    }

     
}
