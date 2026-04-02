<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\UnitUsage;
use Illuminate\Http\Request;

class UsageUnitController extends Controller
{
    public function index()
    {
        $units = UnitUsage::orderBy('id', 'desc')->get();
        return view('panel.pages.unit-usages.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'contract_type' => 'required|in:housing,commercial',
        ]);

        try {
            UnitUsage::create($request->only(['name_ar', 'name_en', 'contract_type']));

            return redirect()->route('admin.unit.index')->with('success', 'تمت الإضافة بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الإضافة: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, UnitUsage $unit)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'contract_type' => 'required|in:housing,commercial',
        ]);

        try {
            $unit->update($request->only(['name_ar', 'name_en', 'contract_type']));

            return redirect()->route('admin.unit.index')->with('success', 'تم التعديل بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء التعديل: ' . $e->getMessage()]);
        }
    }

    public function destroy(UnitUsage $unit)
    {
        try {
            $unit->delete();

            return redirect()->route('admin.unit.index')->with('success', 'تم الحذف بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }
}
