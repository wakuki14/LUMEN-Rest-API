<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\City;
use App\Locale;
use App\MultiLang;
use Illuminate\Support\Facades\DB;

class CitiesController extends Controller
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
            'country_id' => 'required'
        ]);
        
        if ($validator->fails()) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = $validator->errors()->first();
            $data['data'] = null;
            return response()->json($data);
        }
        $input = $request->all();
        unset($input['name']);
        unset($input['short_name']);
        $city = City::create($input);
        if ($city) {
            $localeIso = $request->header('locale');
            if (empty($localeIso)) {
                $localeIso = 'en';
            }
            $locale = Locale::where('language_iso', $localeIso)->first();
            $mlData = [];
            $name =  $request->get('name');
            if (!empty($name)) {
                $mlData[$locale->id]['name'] = $name;
                $city->name = $name;
            }
            
            $shortName = $request->get('short_name');
            if (!empty($shortName)) {
                $mlData[$locale->id]['short_name'] = $shortName;
                $city->short_name = $shortName;
            }
            if (!empty($mlData)) {
                $multiLangModel = new MultiLang();
                $multiLangModel->saveMultiLang($mlData, $city->id, 'City');
            }
            $data['meta']['code'] = 200;
            $data['meta']['message'] = 'successful';
            $data['data'] = $city;
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
        $cityId = $request->get('city_id');
        $city = City::find($cityId);
        $input = $request->all();
        if (!empty($city)) {
            if (!empty($input['post_code'])) {
                $city->post_code = $input['post_code'];
            }
            if (!empty($input['state'])) {
                $city->state = $input['state'];
            }
            
            if (!empty($input['province'])) {
                $city->province = $input['province'];
            }
            if (!empty($input['country_id'])) {
                $city->country_id = $input['country_id'];
            }
            $city->save();
            
            $localeIso = $request->header('locale');
            if (empty($localeIso)) {
                $localeIso = 'en';
            }
            $locale = Locale::where('language_iso', $localeIso)->first();
            $mlData = [];
            $name =  $request->get('name');
            if (!empty($name)) {
                $mlData[$locale->id]['name'] = $name;
                $city->name = $name;
            }
            
            $shortName = $request->get('short_name');
            if (!empty($shortName)) {
                $mlData[$locale->id]['short_name'] = $shortName;
                $city->short_name = $shortName;
            }
            if (!empty($mlData)) {
                $multiLangModel = new MultiLang();
                $multiLangModel->updateMultiLang($mlData, $city->id, 'City');
            }
            
            $data['meta']['code'] = 200;
            $data['meta']['message'] = 'successful';
            
            $data['data'] = $city;
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
        $countryId = $request->input('country_id');
        if (empty($countryId)) {
            return response()->json([
                'meta' => [
                    'code' => 401,
                    'message' => 'country_id is required'
                ],
                'data' => null
            ]);
        }
        $localeIso = $request->header('locale');
        if (empty($localeIso)) {
            $localeIso = 'en';
        }
        $locale = Locale::where('language_iso', $localeIso)->first();
        $localeId = $locale->id;
        $query = DB::table('cities as C')
        ->select(['C.*', 'ML1.content as name', 'ML2.content as short_name'])
        ->leftJoin('multi_langs as ML1', function ($join) use ($localeId) {
            $join->on('ML1.foreign_id', '=', 'C.id')
            ->on('ML1.model', '=',DB::raw("'City'"))
            ->on('ML1.field', '=',DB::raw("'name'"))
            ->on('ML1.locale', '=',DB::raw("$localeId"));
        })
        ->leftJoin('multi_langs as ML2', function ($join) use ($localeId) {
            $join->on('ML2.foreign_id', '=', 'C.id')
            ->on('ML2.model', '=',DB::raw("'City'"))
            ->on('ML2.field', '=',DB::raw("'short_name'"))
            ->on('ML2.locale', '=',DB::raw("$localeId"));
        })
        ->where('C.country_id', $countryId);
        
        $cities = $query->get();
        $data['meta']['code'] = 200;
        $data['meta']['message'] = 'successful';
        $data['data'] = $cities;
        return response()->json($data);
    }
    
}