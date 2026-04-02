<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankAccountResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\ContractPeriodResource;
use App\Http\Resources\PaperworkResource;
use App\Http\Resources\PaymentTypeResource;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\ReaEstatTypeResource;
use App\Http\Resources\ReaEstatUsageResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\ServicePricingResource;
use App\Http\Resources\UnitTypeResource;
use App\Http\Resources\UnitUsageResource;
use App\Http\Traits\Responser;
use App\Models\BankAccount;
use App\Models\City;

use App\Models\ContractPeriod;
use App\Models\Page;
use App\Models\Paperwork;
use App\Models\PaymentType;
use App\Models\Question;
use App\Models\ReaEstatType;
use App\Models\ReaEstatUsage;
use App\Models\Region;
use App\Models\ServicesPricing;
use App\Models\Setting;
use App\Models\UnitType;
use App\Models\UsageUnit;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    use Responser;

    public function cities(Request $request)
    {
        $rules = [
            'region_id' => 'required|exists:regions,id'
        ];
        $this->validate($request, $rules);

        $cities = City::where('region_id', $request->region_id)->get();

        return $this->apiResponse(CityResource::collection($cities), trans('api.success'));
    }


    public function regions(Request $request)
    {
        $regions = Region::orderBy('id', 'desc')->get();

        return $this->apiResponse(RegionResource::collection($regions), trans('api.success'));
    }

    public function termsAndConditions()
    {
        $termsConditions = Page::where('page', 'term_and_condition')->first();

        $data = [
            'description' => $termsConditions ? $termsConditions['description_trans'] : '',
        ];

        return $this->apiResponse($data, trans('api.success'));
    }

   
    public function privacy()
    {
        $privacyPolicy = Page::where('page', 'privacy')->first();
        
        $data = [
            'description' => $privacyPolicy ? $privacyPolicy->description_trans : '',
        ];
        
        return $this->apiResponse($data, trans('api.success'));
    }
    
    
    public function commonQuestions()
    {
        $questions = Question::get();

        return $this->apiResponse(QuestionResource::collection($questions), trans('api.success'));
    }

    public function bankAccounts()
    {
        $bankAccounts = BankAccount::get();

        return $this->apiResponse(BankAccountResource::collection($bankAccounts), trans('api.success'));
    }

    public function servicesPricing(Request $request)
    {
        $rules = [
            'contract_type' => 'required|in:housing,commercial'
        ];
        $this->validate($request, $rules);

        $servicesPricing = ServicesPricing::where('contract_type', $request->contract_type)->get();

        return $this->apiResponse(ServicePricingResource::collection($servicesPricing), trans('api.success'));
    }

    public function paperwork(Request $request)
    {
        $rules = [
            'contract_type' => 'required|in:housing,commercial'
        ];
        $this->validate($request, $rules);

        $paperwork = Paperwork::where('contract_type', $request->contract_type)->get();

        return $this->apiResponse(PaperworkResource::collection($paperwork), trans('api.success'));
    }

    public function realEstatType(Request $request)
    {
        $rules = [
            'contract_type' => 'required|in:housing,commercial'
        ];
        $this->validate($request, $rules);

        $realEstatTypes = ReaEstatType::where('contract_type', $request->contract_type)->get();

        return $this->apiResponse(ReaEstatTypeResource::collection($realEstatTypes), trans('api.success'));
    }

    public function realEstatUsage(Request $request)
    {
        $rules = [
            'contract_type' => 'required|in:housing,commercial'
        ];
        $this->validate($request, $rules);

        $realEstatUsage = ReaEstatUsage::where('contract_type', $request->contract_type)->get();

        return $this->apiResponse(ReaEstatUsageResource::collection($realEstatUsage), trans('api.success'));
    }

    public function unitsTypes(Request $request)
    {
        $rules = [
            'contract_type' => 'required|in:housing,commercial'
        ];
        $this->validate($request, $rules);

        $unitsTypes = UnitType::where('contract_type', $request->contract_type)->get();

        return $this->apiResponse(UnitTypeResource::collection($unitsTypes), trans('api.success'));
    }


    
    public function unitsUsages(Request $request)
    {
        $rules = [
            'contract_type' => 'required|in:housing,commercial'
        ];
        $this->validate($request, $rules);

        $unitsUsages = UsageUnit::where('contract_type', $request->contract_type)->get();

        return $this->apiResponse(UnitUsageResource::collection($unitsUsages), trans('api.success'));
    }



    public function paymentsTypes(Request $request)
    {
        $rules = [
            'contract_type' => 'required|in:housing,commercial'
        ];
        $this->validate($request, $rules);

        $paymentsTypes = PaymentType::where('contract_type', $request->contract_type)->get();

        return $this->apiResponse(PaymentTypeResource::collection($paymentsTypes), trans('api.success'));
    }

    public function contractPeriods(Request $request)
    {
        $rules = [
            'contract_type' => 'required|in:housing,commercial'
        ];
        $this->validate($request, $rules);

        $contractPeriods = ContractPeriod::where('contract_type', $request->contract_type)->get();

        return $this->apiResponse(ContractPeriodResource::collection($contractPeriods), trans('api.success'));
    }

   
   public function settings()
    {
        $settings = Setting::first();

        $settings =  [
            "whatsapp" => $settings->whatsapp ?? '',
            "instagram" => $settings->instagram ?? '',
            "twitter" => $settings->twitter ?? '',
            "snapchat" => $settings->snapchat ?? '',
            "facebook" => 'https://www.facebook.com/?locale=ar_AR',
            "tiktok" =>  'https://www.facebook.com/?locale=ar_AR',
            "linkedIn"=>'https://www.linkedin.com/login/ar',
            "whatsapp_contact" => $settings->whatsapp_contact ?? '',
            'version'=>$settings->version,
            'open_payment'=>$settings->open_payment,
            'is_open'=>$settings->is_open,
             'working_hours' => $settings->working_hours,
        ];

        return $this->apiResponse($settings, trans('api.success'));

   }
}