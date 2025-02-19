<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Food;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
        
    public function get_popular_products(Request $request){
  
        $list = Food::where('type_id', 2)->take(10)->orderBy('created_at','DESC')->get();
        
                foreach ($list as $item){
                    $item['description']=strip_tags($item['description']);
                    $item['description']=$Content = preg_replace("/&#?[a-z0-9]+;/i"," ",$item['description']); 
                    unset($item['selected_people']);
                    unset($item['people']);
                }
                
                 $data =  [
                    'total_size' => $list->count(),
                    'type_id' => 2,
                    'offset' => 0,
                    'products' => $list
                ];
                
         return response()->json($data, 200);
 
    }
        public function get_recommended_products(Request $request){
        $list = Food::where('type_id', 3)->take(10)->orderBy('created_at','DESC')->get();
        
                foreach ($list as $item){
                    $item['description']=strip_tags($item['description']);
                    $item['description']=$Content = preg_replace("/&#?[a-z0-9]+;/i"," ",$item['description']); 
                    unset($item['selected_people']);
                    unset($item['people']);
                }
                
                 $data =  [
                    'total_size' => $list->count(),
                    'type_id' => 3,
                    'offset' => 0,
                    'products' => $list
                ];
                
         return response()->json($data, 200);
    }

    public function searchProducts(Request $request)
{
    
    $validator = Validator::make($request->all(), [
        'query' => 'required|string|min:1|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid search query',
            'errors' => $validator->errors()
        ], 400);
    }

    $query = $request->input('query');
    
    // Search for foods with names containing the search query
    $list = Food::where('name', 'LIKE', '%' . $query . '%')
                ->orderBy('created_at', 'DESC')
                ->take(50)  // Limit results to 50 items
                ->get();

    // Process items similar to your other endpoints
    foreach ($list as $item) {
        $item['description'] = strip_tags($item['description']);
        $item['description'] = preg_replace("/&#?[a-z0-9]+;/i", " ", $item['description']);
        unset($item['selected_people']);
        unset($item['people']);
    }

    $data = [
        'total_size' => $list->count(),
        'query' => $query,
        'offset' => 0,
        'products' => $list
    ];

    return response()->json($data, 200);
}
    

       public function test_get_recommended_products(Request $request){
  
        $list = Food::skip(5)->take(2)->get();
      
        foreach ($list as $item){
            $item['description']=strip_tags($item['description']);
            $item['description']=$Content = preg_replace("/&#?[a-z0-9]+;/i"," ",$item['description']); 
        }
        
         $data =  [
            'total_size' => $list->count(),
            'limit' => 5,
            'offset' => 0,
            'products' => $list
        ];
         return response()->json($data, 200);
        // return json_decode($list);
    }
    public function get_drinks(Request $request){
        $list = Food::where('type_id', 4)->take(10)-> get();
        
                foreach ($list as $item){
                    $item['description']=strip_tags($item['description']);
                    $item['description']=$Content = preg_replace("/&#?[a-z0-9]+;/i"," ",$item['description']); 
                    unset($item['selected_people']);
                    unset($item['people']);
                }
                
                 $data =  [
                    'total_size' => $list->count(),
                    'type_id' => 4,
                    'offset' => 0,
                    'products' => $list
                ];
                
         return response()->json($data, 200);
    }

}
