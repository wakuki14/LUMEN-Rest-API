<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Country;
use App\Locale;
use App\MultiLang;
use Illuminate\Support\Facades\DB;

class CountriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    /**
     * Create city
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $data = [];
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'iso_code' => 'required|max:3'
        ]);
        
        if ($validator->fails()) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = $validator->errors()->first();
            $data['data'] = null;
            return response()->json($data);
        }
        $input = $request->all();
        unset($input['name']);
        $country = Country::create($input);
        if ($country) {
            $localeIso = $request->header('locale');
            if (empty($localeIso)) {
                $localeIso = 'en';
            }
            $locale = Locale::where('language_iso', $localeIso)->first();
            $mlData = [];
            $name =  $request->get('name');
            if (!empty($name)) {
                $mlData[$locale->id]['name'] = $name;
                $country->name = $name;
            }

            if (!empty($mlData)) {
                $multiLangModel = new MultiLang();
                $multiLangModel->saveMultiLang($mlData, $country->id, 'Country');
            }
            $data['meta']['code'] = 200;
            $data['meta']['message'] = 'successful';
            $data['data'] = $country;
            return response()->json($data);
        }
        
        return response()->json([
            'meta' => [
                'code' => 401,
                'message' => 'Unable to create city'
            ],
            'data' => null
        ]);
        
    }
    
    /**
     *
     * Update city
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $data = [];
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'iso_code' => 'max:3'
        ]);
        
        if ($validator->fails()) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = $validator->errors()->first();
            $data['data'] = null;
            return response()->json($data);
        }
        $countryId = $request->get('country_id');
        $country = Country::find($countryId);
        $input = $request->all();

        if (!empty($country)) {
            if (!empty($input['iso_code'])) {
                $country->iso_code = $input['iso_code'];
                $country->iso_code = $input['iso_code'];
            }
            if (!empty($input['phone_code'])) {
                $country->phone_code = $input['phone_code'];
                $country->phone_code = $input['phone_code'];
            }
           
            $country->save();
            
            $localeIso = $request->header('locale');
            if (empty($localeIso)) {
                $localeIso = 'en';
            }
            $locale = Locale::where('language_iso', $localeIso)->first();
            $mlData = [];
            $name =  $request->get('name');
            if (!empty($name)) {
                $mlData[$locale->id]['name'] = $name;
                $country->name = $name;
            }
            if (!empty($mlData)) {
                $multiLangModel = new MultiLang();
                $multiLangModel->updateMultiLang($mlData, $country->id, 'Country');
            }
            
            $data['meta']['code'] = 200;
            $data['meta']['message'] = 'successful';
            
            $data['data'] = $country;
            return response()->json($data);
        }
        
        return response()->json([
            'meta' => [
                'code' => 401,
                'message' => 'Unable to update city'
            ],
            'data' => null
        ]);
    }
    
    /**
     * Get city name
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $data = [];
        $localeIso = $request->header('locale');
        if (empty($localeIso)) {
            $localeIso = 'en';
        }
        $locale = Locale::where('language_iso', $localeIso)->first();
        $localeId = $locale->id;
        $query = DB::table('countries as C')->select(['C.*', 'ML1.content as name'])
        ->leftJoin('multi_langs as ML1', function ($join) use ($localeId) {
            $join->on('ML1.foreign_id', '=', 'C.id')
            ->on('ML1.model', '=',DB::raw("'Country'"))
            ->on('ML1.field', '=',DB::raw("'name'"))
            ->on('ML1.locale', '=',DB::raw("$localeId"));
        })->orderBy('ML1.content');
        
        $countries = $query->get();
        $data['meta']['code'] = 200;
        $data['meta']['message'] = 'successful';
        $data['data'] = $countries;
        return response()->json($data);
    }
    
}