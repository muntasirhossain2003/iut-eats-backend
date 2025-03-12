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
public function uploadProduct(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|integer',
            'img'         => 'required|image|max:2048', // image validation, max size 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 400);
        }

        // Handle image upload
        if ($request->hasFile('img')) {
            $image = $request->file('img');
            // Create a unique filename using current timestamp
            $imageName = time() . '_' . $image->getClientOriginalName();
            // Define a destination path (public/uploads/images)
            $destinationPath = public_path('/uploads/images');
            // Move the file to the destination path
            $image->move($destinationPath, $imageName);
            // Generate the URL for the uploaded image
            $imgUrl = 'images/' . $imageName;
        }

        // Create a new Food product using the validated data
        $food = new Food();
        $food->name = $request->input('name');
        $food->description = $request->input('description');
        $food->price = $request->input('price');
        $food->img = $imgUrl;
        // Set default values for fields managed by the server
        $food->location = 'Gazipur';
        $food->type_id = 3;
        $food->stars = 4;
        $food->people = 5;
        $food->selected_people = 5;

        $food->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Product uploaded successfully',
            'product' => $food
        ], 200);
    }
    public function editProduct(Request $request)
{
    // Validate the incoming request data including the product ID
    $validator = Validator::make($request->all(), [
        'id'          => 'required|exists:foods,id', // Ensure ID exists in database
        'name'        => 'required|string|max:255',
        'description' => 'required|string',
        'price'       => 'required|integer',
        'img'         => 'sometimes|image|max:2048', // Image is optional for updates
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Validation failed',
            'errors'  => $validator->errors()
        ], 400);
    }

    // Find the existing product
    $food = Food::find($request->id);

    // Handle new image upload if provided
    if ($request->hasFile('img')) {
        $image = $request->file('img');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $destinationPath = public_path('/uploads/images');
        $image->move($destinationPath, $imageName);
        $food->img = 'images/' . $imageName;
    }

    // Update product details
    $food->name = $request->input('name');
    $food->description = $request->input('description');
    $food->price = $request->input('price');

    // Server-managed fields (location, type_id, etc.) remain unchanged
    $food->save();

    return response()->json([
        'status'  => 'success',
        'message' => 'Product updated successfully',
        'product' => $food
    ], 200);
}

public function deleteProducts(Request $request)
{
    // Validate that the ID exists in the foods table
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:foods,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Invalid query',
            'errors'  => $validator->errors()
        ], 400);
    }

    // Retrieve the product ID from the query parameter
    $id = $request->input('id');

    // Find the product using the Food model
    $food = Food::find($id);

    if (!$food) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Product not found',
        ], 404);
    }

    // Delete the product
    $food->delete();

    return response()->json([
        'status'  => 'success',
        'message' => 'Product deleted successfully',
    ], 200);
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
