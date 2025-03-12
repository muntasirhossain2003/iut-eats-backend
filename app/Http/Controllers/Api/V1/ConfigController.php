<?php

namespace App\Http\Controllers\Api\V1;
use App\Models\Zone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Grimzy\LaravelMysqlSpatial\Types\Point;


class ConfigController extends Controller
{
        public function geocode_api(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
       
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$request->lat.','.$request->lng.'&key='."AIzaSyDw06Pb4KkJo3kUnq5vCeTHXEJZx4_X7jM");
        return $response->json();
    }
        public function get_zone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $point = new Point($request->lat,$request->lng);
        $zones = Zone::contains('coordinates', $point)->latest()->get();
       /* if(count($zones)<1)
        {
            return response()->json(['message'=>trans('messages.service_not_available_in_this_area_now')], 404);
        }
        foreach($zones as $zone)
        {
            if($zone->status)
            {
                return response()->json(['zone_id'=>$zone->id], 200);
            }
        }*/
        //return response()->json(['message'=>trans('messages.we_are_temporarily_unavailable_in_this_area')], 403);
         return response()->json(['zone_id'=>1], 200);
    }
    public function place_api_autocomplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_text' => 'required',
        ]);
    
        if ($validator->errors()->count() > 0) {
            return response()->json(
                ['errors' => Helpers::error_processor($validator)],
                403
            );
        }
    
        $payload = [
            'input' => $request['search_text'],
            'languageCode' => 'en', // Adjust as needed
            'regionCode' => 'us',   // Adjust as needed
        ];
    
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => 'AIzaSyDw06Pb4KkJo3kUnq5vCeTHXEJZx4_X7jM',
            'Content-Type' => 'application/json',
        ])->post('https://places.googleapis.com/v1/places:autocomplete', $payload);
    
        return $response->json();
    }
    public function place_api_details(Request $request)
    {
        // Validate that 'place_id' is provided
        $validator = Validator::make($request->all(), [
            'place_id' => 'required',
        ]);
    
        // Return validation errors if any
        if ($validator->errors()->count() > 0) {
            return response()->json(
                ['errors' => Helpers::error_processor($validator)],
                403
            );
        }
    
        // Get the place ID from the request
        $placeId = $request['place_id'];
    
        // Make the GET request to the new Places API endpoint with the API key in the header
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => 'AIzaSyDw06Pb4KkJo3kUnq5vCeTHXEJZx4_X7jM',
            'X-Goog-FieldMask' => '*',
        ])->get("https://places.googleapis.com/v1/places/{$placeId}");
    
        // Return the API response as JSON
        return $response->json();
    }
}
