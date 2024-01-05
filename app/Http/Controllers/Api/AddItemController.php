<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class AddItemController extends Controller
{

 public function add_item(Request $request)
{
    $itemTable = 'item';

    $validator = Validator::make($request->all(), [
        'item_name' => 'required|string|max:255',
        'sales_price' => 'required|numeric',
        'purchase_price' => 'required|numeric',
        'msn' => 'required|string',
        'gst' => 'required|string',
        'opening_stock' => 'required|numeric',
        'item_date' => 'required|string',
        'item_image' => 'image',
        'item_category' => 'required|string',
        'item_remark' => 'required|string',
        's_price_add_gst' => 'required|string',
        'p_price_add_gst' => 'required|string',
        'low_stock_warning' => 'required|string',
        'temp_stock' => 'required|numeric',
        'extra_qty' => 'required|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => $validator->errors()->first(),
        ], 400);
    }

    $userId = $request->user()->id;
    $item_name = $request->input('item_name');

    // Check if an item with the same name already exists
    $existingItem = DB::table($itemTable)
        ->where('item_name', $item_name)
        ->where('user_id', $userId)
        ->first();

    if ($existingItem) {
        return response()->json([
            'success' => false,
            'error' => 'An item with the same name already exists.',
        ], 400);
    }
    
     $userId = $request->user()->id;
    $item_name = $request->input('item_name');
    $sales_price = $request->input('sales_price');
    $purchase_price = $request->input('purchase_price');
    $msn = $request->input('msn');
    $gst = $request->input('gst');
    $opening_stock = $request->input('opening_stock');
    $item_date = $request->input('item_date');
    $item_category = $request->input('item_category');
    $item_remark = $request->input('item_remark');
    $s_price_add_gst = $request->input('s_price_add_gst');
    $p_price_add_gst = $request->input('p_price_add_gst');
    $low_stock_warning = $request->input('low_stock_warning');
    $temp_stock = $request->input('temp_stock');
    $extra_qty = $request->input('extra_qty');
    $item_image = $request->file('item_image');

    // Create the new item
    DB::table($itemTable)->insert([
        'user_id' => $userId,
        'item_name' => $item_name,
          'sales_price' => $sales_price,
      'purchase_price' => $purchase_price,
      'msn' => $msn,
      'gst' => $gst,
      'opening_stock' => $opening_stock,
      'item_date' => $item_date,
      'item_image' => $item_image,
      'item_category' => $item_category,
      'item_remark' => $item_remark,
      's_price_add_gst' => $s_price_add_gst,
      'p_price_add_gst' => $p_price_add_gst,
      'low_stock_warning' => $low_stock_warning,
      'temp_stock' => $temp_stock,
      'extra_qty' => $extra_qty
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Item Added',
    ]);
}




    public function updateExtraQtyByUserId(Request $request)
    {
        $response = [];

        $extraQty = $request->input('extra_qty');
        $user_id = $request->user()->id;

        // if (!$extraQty) {
        //     return response()->json(['success' => false, 'error' => 'Input(s) missing']);
        // }

        DB::table('item')
            ->where('user_id', $user_id)
            ->update(['extra_qty' => $extraQty]);

        return response()->json([
                    'success' => true,
                    'user_id'=>$user_id,
                    'extra_qty' =>$extraQty,
                    'message' => 'Successfully updated extra quantity'


        ]);

            return response()->json($response);
    }



    public function delete_item_all_data(Request $request)
    {
        $userId = $request->user()->id;

        $userExists = DB::table('item')->where('user_id', $userId)->exists();

        if (!$userExists) {
            return response()->json(['success' => false,'error' => 'User not found.'], 404);
        }

        try {
            DB::table('item')->where('user_id', $userId)->delete();
            return response()->json(['success' => true,'message' => 'Data deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'error' => 'An error occurred'], 500);
        }
    }

    public function delete_item(Request $request)
    {

        $Id = $request->input('id');

        $IdExists = DB::table('item')->where('id', $Id)->exists();

        if (!$IdExists) {
            return response()->json(['success' => false,'error' => 'Id not found.'], 404);
        }

        try {
            DB::table('item')->where('id', $Id)->delete();
            return response()->json(['success' => true,'message' => ' data deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'error' => 'An error occurred'], 500);
        }
    }

    public function Show_item_details(Request $request)
    {
        $id = $request->input('id');
        $itemName = $request->input('item_name');

        $query = DB::table('item');

        if ($id) {
            $query->where('id', $id);
        }

        if ($itemName) {
            $query->where('item_name', 'like', '%' . $itemName . '%');
        }

        $result = $query->first();

        if ($result) {
            return response()->json([
                'success' => true,
                'total' => 1,
                'user_data' => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'No data found',
            ]);
        }
    }


    public function select_opening_stock(Request $request)
    {
        $id = $request->input('id');

        $condition = '';
        $fieldList = 'id';

        if ($id) {
            $condition = "WHERE id = $id";
            $fieldList = '*';
        }

        $stockData = DB::select("SELECT $fieldList FROM item $condition");

        $count = count($stockData);

        if ($count > 0) {
            return response()->json(['success' => true, 'stock_data' => $stockData]);
        } else {
            return response()->json(['success' => false, 'error' => 'No records found']);
        }
    }


   public function item_date_stock(Request $request)
   {
       $response = [];

       $id = $request->input('id');
       $item_date = $request->input('item_date');

       $fieldList = ['id', 'item_date', 'temp_stock'];

       $query = DB::table('item')->select($fieldList);

       if ($id !== null) {
           $query->orWhere('id', $id);
       }

       if ($item_date !== null) {
           $query->orWhere('item_date', 'like', "%$item_date%");
       }

       $results = $query->orderBy('id', 'desc')->get();

       $count = count($results);

       if ($count > 0) {
           foreach ($results as $result) {
               $response[] = (array) $result;
           }

           return response()->json([
               'success' => true,
               'total' => $count,
               'data' => $response,
           ]);
       } else {
           return response()->json([
               'success' => false,
               'error' => 'No records found',
           ], 404);
       }
   }


    public function update_extra_qty(Request $request)
    {
        $response = [];

        $extraQty = $request->input('extra_qty');
        $id = $request->input('id');

        if ( !$id) {
            return response()->json(['success' => false, 'error' => 'Input(s) missing'], 400);
        }

        $count = DB::table('item')->where('id', $id)->count();

        if ($count === 0) {
            return response()->json([
                'success' => false,
                'error' => 'Record not found for the given id.',
            ], 404);
        }

        DB::table('item')->where('id', $id)->update(['extra_qty' => $extraQty]);

        $item = DB::table('item')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated extra quantity',
            'Data' => $item

        ]);
    }


    public function update_stock(Request $request)
    {
        $response = [];

        $openingStock = $request->input('opening_stock');
        $id = $request->input('id');

       

        $affectedRows = DB::table('item')
            ->where('id', $id)
            ->update(['opening_stock' => $openingStock]);

        if ($affectedRows = 0) {
            return response()->json([
                'success' => false,
                'error' => 'Item not found or no changes made.',
            ], 404);
        }

        $message = 'Stock Updated';


        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }



    public function update_item(Request $request)
    {
        $response = [];

        $input = $request->all();

        // Validate the request data
        $request->validate([
            'id' => 'required|exists:item,id',
            'item_name' => 'required|string|max:255',
            'sales_price' => 'required|numeric',
            'purchase_price' => 'required|numeric',
            'msn' => 'required|string|max:255',
            'gst' => 'required|numeric',
            'opening_stock' => 'required|numeric',
            'item_date' => 'required|date',
            'item_image' => 'nullable|image',
            'item_category' => 'required|string|max:255',
            'item_remark' => 'nullable|string',
            's_price_add_gst' => 'required|numeric',
            'p_price_add_gst' => 'required|numeric',
            'low_stock_warning' => 'required|numeric',
            'temp_stock' => 'required|numeric',
        ]);

        // Handle the item_image field
        $item_image = $request->file('item_image');

        if ($item_image) {
            $item_image_name = $item_image->getClientOriginalName();
            $item_image_path = $item_image->storeAs('public/item_images', $item_image_name);
            $item_image = $item_image_name;
        } else {
            // Get the current item data from the database
            $item = DB::table('item')->where('id', $input['id'])->first();

            // Keep the old item image
            $item_image = $item->item_image;
        }

        // Update the item in the database
        $data=  DB::table('item')
            ->where('id', $input['id'])
            ->update([
                'item_name' => $input['item_name'],
                'sales_price' => $input['sales_price'],
                'purchase_price' => $input['purchase_price'],
                'msn' => $input['msn'],
                'gst' => $input['gst'],
                'opening_stock' => $input['opening_stock'],
                'item_date' => $input['item_date'],
                'item_image' => $item_image,
                'item_category' => $input['item_category'],
                'item_remark' => $input['item_remark'],
                's_price_add_gst' => $input['s_price_add_gst'],
                'p_price_add_gst' => $input['p_price_add_gst'],
                'low_stock_warning' => $input['low_stock_warning'],
                'temp_stock' => $input['temp_stock'],
            ]);

        // Get the updated item data
        $data = DB::table('item')
            ->where('id', $input['id'])
            ->first();

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Item Updated',
            'user_data' => $data
        ]);
    }







    public function view_item(Request $request)
    {

        $user_id = $request->user()->id;

        $fieldList = "id, item_name, sales_price, gst, s_price_add_gst, p_price_add_gst, opening_stock, item_image, item_remark, low_stock_warning, extra_qty";
        $condition = null;


        if ($user_id !== null) {
            $condition = " WHERE user_id = ?";
            $fieldList = "*";
        }

        $result = DB::select("SELECT $fieldList FROM item $condition", [$user_id]);
        $response = [];


        if (count($result) > 0) {

            $response['success'] = true;
            $response['total'] = count($result);
            $response['data'] = $result;
        } else {
            $response['error'] = 'Data not found';
            $response['success'] = false;
        }

        return response()->json($response, 200);
    }


}

