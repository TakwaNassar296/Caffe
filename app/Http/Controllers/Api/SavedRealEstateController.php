<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\RealEstate;
use App\Models\UnitsReal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SavedRealEstateController extends Controller
{
    public function savedRealEstate(Request $request)
    {
        $user_id = Auth::id();
        
        // Validate required inputs
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'name_real_estate' => 'required|string',
         ]);

        // Retrieve the contract using contract_id
        $contract = Contract::findOrFail($validated['contract_id']);

        // Wrap in transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Prepare data for RealEstate
            $realData = [
                'user_id' => $user_id,
                'property_owner_iban' => $contract->property_owner_iban,
                'contract_type' => $contract->contract_type,
                'date_first_registration' => $contract->date_first_registration,
                'real_estate_registry_number' => $contract->real_estate_registry_number,
                'name_real_estate' => $validated['name_real_estate'],
                'name_owner' => $contract->name_owner,
                'unit_number_of_real' => $contract->unit_number_of_real,
                'unit_number' => $contract->unit_number,
                'instrument_number' => $contract->instrument_number,
                'instrument_history' => $contract->instrument_history,
                'instrument_type' => $contract->instrument_type,
                'property_city_id' => $contract->property_city_id,
                'street' => $contract->street,
                'number_of_floors' => $contract->number_of_floors,
                'postal_code' => $contract->postal_code,
                'extra_figure' => $contract->extra_figure,
                'type_real_estate_other' => $contract->type_real_estate_other,
                'property_owner_id_num' => $contract->property_owner_id_num,
                'property_owner_dob_hijri' => $contract->property_owner_dob_hijri,
                'property_owner_mobile' => $contract->property_owner_mobile,
                'neighborhood' => $contract->neighborhood,
                'property_place_id' => $contract->property_place_id,
                'building_number' => $contract->building_number,
                'property_type_id' => $contract->property_type_id,
                'property_usages_id' => $contract->property_usages_id,
            ];

            // Create a new RealEstate record
            $real = RealEstate::create($realData);

            // Prepare data for UnitsReal
            $unitData = [
                'real_estates_units_id' => $real->id,
                'user_id' => $user_id,
                'unit_number' => $contract->unit_number,
                'unit_area' => $contract->unit_area,
                'electricity_meter_number' => $contract->electricity_meter_number,
                'water_meter_number' => $contract->water_meter_number,
                'Gasmeter' => $contract->Gasmeter,                
                'window_ac' => $contract->window_ac,
                'split_ac' => $contract->split_ac,
                'tootal_rooms' => $contract->tootal_rooms,
                'The_number_of_toilets' => $contract->The_number_of_toilets,
                'number_of_kitchens' => $contract->number_of_kitchens,
                'unit_usage_id' => $contract->unit_usage_id,
                'unit_type_id' => $contract->unit_type_id,
                'floor_number' => $contract->floor_number,
                'number_of_parking_spaces' => $contract->number_of_parking_spaces,
                'number_of_halls' => $contract->number_of_halls,
            ];

            // Create a new UnitsReal record associated with the RealEstate
            $unit = UnitsReal::create($unitData);

            // Commit transaction if everything is fine
            DB::commit();

            return response()->json([
                'message' => 'تمت إضافة العقار بنجاح',
                'code' => Response::HTTP_CREATED,
                'success' => true,
                'data' => [
                    'real_estate' => $real,
                    'units_real' => $unit,
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء إضافة العقار',
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'success' => false,
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
