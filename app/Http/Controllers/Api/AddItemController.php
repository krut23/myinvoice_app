<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class AddItemController extends Controller
{

    public function addItem(Request $request)
    {
        $itemTable = 'item';

        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255|unique:item,item_name',
            'sales_price' => 'required|numeric',
            'purchase_price' => 'required|numeric',
            'msn' => 'required|numeric',
            'gst' => 'required|string',
            'opening_stock' => 'required|numeric',
            'item_date' => 'required|string',
            'item_image' => 'required|string',
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
                'message' => $validator->errors()->first(),
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
        $item_image = $request->input('item_image');
        $item_category = $request->input('item_category');
        $item_remark = $request->input('item_remark');
        $s_price_add_gst = $request->input('s_price_add_gst');
        $p_price_add_gst = $request->input('p_price_add_gst');
        $low_stock_warning = $request->input('low_stock_warning');
        $temp_stock = $request->input('temp_stock');
        $extra_qty = $request->input('extra_qty');

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
        $user_id = $request->input('user_id');

        if (!$extraQty || !$user_id) {
            return response()->json(['error' => 'Input(s) missing']);
        }

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
        $userId = $request->input('user_id');

        $userExists = DB::table('item')->where('user_id', $userId)->exists();

        if (!$userExists) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        try {
            DB::table('item')->where('user_id', $userId)->delete();
            return response()->json(['message' => 'Data deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function delete_item(Request $request)
    {

        $Id = $request->input('id');

        $IdExists = DB::table('item')->where('id', $Id)->exists();

        if (!$IdExists) {
            return response()->json(['error' => 'Id not found.'], 404);
        }

        try {
            DB::table('item')->where('id', $Id)->delete();
            return response()->json(['message' => ' data deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
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
    
        $results = $query->get();
    
        if (count($results) > 0) {
            return response()->json([
                'success' => true,
                'total' => count($results),
                'data' => $results,
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
   
       $response = [];
       $sql = "SELECT $fieldList FROM item $condition";
   
       $stockData = DB::select($sql);
   
       $count = count($stockData);
   
       if ($count > 0) {
           $response['success'] = true;
           
           foreach ($stockData as $row) {
               array_push($response, (array) $row);
           }
       } else {
           $response['success'] = false;
           $response['error'] = 'No records found';
       }
   
       return response()->json($response);
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
    
        if (!$extraQty || !$id) {
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
    
        array_push($response, ['message' => 'Successfully updated extra quantity']);
    
        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
    

    public function update_stock(Request $request)
    {
        $response = [];
    
        $openingStock = $request->input('opening_stock');
        $id = $request->input('id');
    
        if (!$openingStock || !$id) {
            return response()->json(['success' => false, 'error' => 'Input(s) missing'], 400);
        }
    
        $affectedRows = DB::table('item')
            ->where('id', $id)
            ->update(['opening_stock' => $openingStock]);
    
        if ($affectedRows === 0) {
            return response()->json([
                'success' => false,
                'error' => 'Item not found or no changes made.',
            ], 404);
        }
    
        $message = 'Stock Updated';
    
        array_push($response, ['message' => $message]);
    
        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
    


    public function update_item(Request $request)
    {
        $id = $request->input('id', $request->query('id'));
    
        if ($id === null) {
            return response()->json([
                'error' => 'ID parameter is missing',
                'success' => false
            ], 400);
        }
    
        $updateField = $request->input('update_item');
        $updateValue = $request->input($updateField);
    
        if ($updateField === null) {
            return response()->json([
                'error' => 'Update field is missing',
                'success' => false
            ], 400);
        }
    
        $allowedFields = [
            'item_name',
            'sales_price',
            'purchase_price',
            'msn',
            'gst',
            'opening_stock',
            'item_date',
            'item_image',
            'item_category',
            'item_remark',
            's_price_add_gst',
            'p_price_add_gst',
            'low_stock_warning',
            'temp_stock'
        ];
    
        if ($updateField !== 'all' && !in_array($updateField, $allowedFields)) {
            return response()->json([
                'error' => 'Invalid update field',
                'success' => false
            ], 400);
        }
    
        $dataToUpdate = [];
    
        if ($updateField === 'all') {
            foreach ($allowedFields as $field) {
                $dataToUpdate[$field] = $request->input($field);
            }
        } else {
            $dataToUpdate[$updateField] = $updateValue;
        }
    
        $result = DB::table('item')
            ->where('id', $id)
            ->update($dataToUpdate);
    
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Item updated',
                
            ]);
        } else {
            return response()->json([
                'error' => 'Item not found or no updates were made',
                'success' => false
            ], 404);
        }
    }
    

    public function view_item(Request $request)
    {

        $user_id = $request->input('user_id');
        
        

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

