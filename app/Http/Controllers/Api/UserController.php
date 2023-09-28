<?php

namespace App\Http\Controllers\Api;

use stdClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{


    public function user_register(Request $request)
    {
        $userTable = 'users';

        try {
            $validated = $request->validate([
                'user_name' => 'required|string|unique:users',
                'password' => 'required|string|min:8',
                'business_name' => 'required|string',
                'phone_number' => 'required|string|unique:users',
                'gst_number' => 'sometimes|string',
                'your_name' => 'required|string',
                'name' => 'required|string',
                'business_logo' => 'required|image',
                'signature' => 'required|image',
                'state' => 'required|string',
                'address' => 'required|string',
            ]);

            $businessLogoFilePath = $request->file('business_logo')->store('public/business_logos');
            $signatureFilePath = $request->file('signature')->store('public/signatures');

            $businessLogoFileName = basename($businessLogoFilePath);
            $signatureFileName = basename($signatureFilePath);

            $user = new User([
                'user_name' => $validated['user_name'],
                'password' => Hash::make($validated['password']),
                'business_name' => $request->input('business_name'),
                'phone_number' => $request->input('phone_number'),
                'gst_number' => $request->input('gst_number'),
                'your_name' => $request->input('your_name'),
                'name' => $request->input('name'),
                'business_logo' => $businessLogoFileName,
                'signature' => $signatureFileName,
                'state' => $request->input('state'),
                'address' => $request->input('address'),
            ]);

            $user->save();

            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => 'User created successfully.',
            ]);
        } catch (ValidationException $exception) {

            $errorResponse = new stdClass();
            $errorResponse->success = false;
            $errorResponse->errors = [];

            foreach ($exception->errors() as $field => $errors) {
                $errorResponse->errors[$field] = $errors[0];
            }

            return response()->json($errorResponse, 400);
        }
    }



    public function updateSignature(Request $request)
    {
        $signature = $request->file('signature');
        $user = Auth::user();
        $id = $user->id;


        if (!$signature || !$id) {
            return response()->json([
                'error' => 'input(s) is missing',
            ], 400);
        }

        $filename = uniqid() . '.' . $signature->getClientOriginalExtension();
        $signature = Storage::putFileAs('public/signatures', $signature, $filename);

        $user = DB::table('users')
            ->where('id', $id)
            ->update([
                'signature' => $filename,
            ]);

        return response()->json([
            'message' => 'signature Updated',
            'signature' => $signature,
        ]);
        }



    
    public function show_businessName_phoneNumber(Request $request)
     {
            $condition = null;
            $FieldList = 'id,business_name,phone_number';

            if ($request->has('id')) {
                $condition = " where id='{$request->id}'";
            }

            $sql = "select $FieldList from users $condition";
            $users = DB::select($sql);

            $response = [
                'total' => count($users),
                'data' => $users,
            ];

            return response()->json($response);
     }

   
    public function check_username_register_or_not(Request $request)
    {
        $condition = null;
        $FieldList = 'user_name';

        if (isset($request['user_name'])) {
            $condition = " where user_name like '%{$request['user_name']}%'";
            $fieldList = '*';
        }

        $sql = "select $FieldList from users $condition";


        return response()->json([
            'total' => count($users),
            'data' => $users,
        ]);
    }

    public function fetch_user_name(Request $request)
    {
        $user_name = $request->input('user_name');

        if (!empty($user_name)) {
            $users = DB::select("SELECT id, user_name, business_name, phone_number, gst_number, your_name, name, business_logo, signature, state, address, created_at, updated_at FROM users WHERE user_name = ?", [$user_name]);
        } else {
            $users = DB::select("SELECT id, user_name, business_name, phone_number, gst_number, your_name, name, business_logo, signature, state, address, created_at, updated_at FROM users");
        }

        $response = [
            'total' => count($users),
            'data' => $users
        ];

        return response()->json($response);
    }

    public function fetch_user_password(Request $request)
    {
        $condition = null;
        $FieldList = "user_name,password,id";

        if (isset($request['user_name']) || isset($request['password'])) {
            $condition = " where user_name like '%$request->user_name%' && password like '%$request->password%'";
        }

        $sql = "select $FieldList from users $condition";
        $result = DB::select($sql);

        $response = [
            'total' => count($result),
            'data' => $result
        ];

        return response()->json($response);
    }

    public function update_gst_registration(Request $request)
    {
        $input = $request->all();

        $rules = [
            'business_name' => 'required',
            'gst_number' => 'required',
            'your_name' => 'required',
            'phone_number' => 'required',
        ];

        $validator = \Validator::make($input, $rules);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $user = \DB::table('users')
            ->where('id', $request->user()->id)
            ->update([
                'business_name' => $input['business_name'],
                'gst_number' => $input['gst_number'],
                'your_name' => $input['your_name'],
                'phone_number' => $input['phone_number'],
            ]);

        $user = \DB::table('users')
            ->where('id', $request->user()->id)
            ->first();
        unset($user->password);
        return response()->json([
            'message' => 'updated successfully',
            'user' => $user,
        ]);
    }

    public function show_ph_gst_address(Request $request)
    {
        $id = $request->get('id');
        $condition = null;
        $FieldList = "id,phone_number,gst_number,state,address,your_name,business_name,business_logo";
    
        if (!$id) {
            return response()->json(['success' => false, 'error' => 'Missing id']);
        }
    
        $condition = "WHERE id = '$id'";
    
        $sql = "SELECT $FieldList FROM users $condition";
    
        $users = DB::select($sql);
    
        if (count($users) > 0) {
            return response()->json([
                'success' => true,
                'total' => count($users),
                'data' => $users,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'No data found',
            ]);
        }
    }
    


    public function update_business_user(Request $request)
    {
        $response = [];

        $id = $request->input('id');
        $updateFields = $request->only('business_name', 'business_logo', 'gst_number', 'state', 'address');

        if (!$id) {
            return response()->json([
                'success' => false,
                'error' => 'ID parameter is missing'
            ], 400);
        }

        $validFields = [];

        foreach ($updateFields as $field => $value) {
            if ($value !== null) {
                $validFields[$field] = $value;
            }
        }

        if (empty($validFields)) {
            return response()->json([
                'success' => false,
                'error' => 'No valid update fields provided'
            ], 400);
        }

        DB::table('users')->where('id', $id)->update($validFields);

        return response()->json([
            'success' => true,
            'message' => 'User information updated'
        ]);
    }


    public function update_phone_number(Request $request)
    {
        $response = [];
    
        $input = $request->all();
    
        if (!isset($input['phone_number']) || !isset($input['id'])) {
            return response()->json(['success' => false, 'error' => 'Input(s) missing'], 400);
        }
    
        $id = $input['id'];
        $phone_number = $input['phone_number'];
    
        $affectedRows = DB::table('users')
            ->where('id', $id)
            ->update([
                'phone_number' => $phone_number,
            ]);
    
        if ($affectedRows === 0) {
            return response()->json([
                'success' => false,
                'error' => 'User not found or no changes made.',
            ], 404);
        }
    
        array_push($response, ['message' => 'User updated successfully']);
    
        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
    

    public function update_your_name(Request $request)
    {
        $response = [];
    
        $request->validate([
            'id' => 'required',
            'user_name' => 'required',
        ]);
    
        $id = $request->input('id');
        $userName = $request->input('user_name');
    
        $affectedRows = DB::table('users')
            ->where('id', $id)
            ->update(['user_name' => $userName]);
    
        if ($affectedRows === 0) {
            return response()->json([
                'success' => false,
                'error' => 'User not found or no changes made.',
            ], 404);
        }
    
        array_push($response, ["message" => "Name successfully updated"]);
    
        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
    

    public function user_add_sign_logo_ph(Request $request)
    {
        $response = [];
        
        $request->validate([
            'id' => 'required',
        ]);
        
        $id = $request->input('id');
        $fieldList = [
            'business_name',
            'phone_number',
            'gst_number',
            'address',
            'state',
            'business_logo',
            'signature',
        ];
        
        $userData = DB::table('users')
            ->select($fieldList)
            ->where('id', $id)
            ->get();
        
        if ($userData->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'User not found.',
            ], 404);
        }
        
        $userData->each(function ($item) use (&$response) {
            array_push($response, $item);
        });
        
        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
    
    


    public function user_name_logo(Request $request)
    {
        $response = [];
    
        $request->validate([
            'id' => 'required',
        ]);
    
        $id = $request->input('id');
        $fieldList = [
            'business_name',
            'business_logo',
        ];
    
        $userData = DB::table('users')
            ->select($fieldList)
            ->where('id', $id)
            ->get();
    
        if ($userData->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'User not found.',
            ], 404);
        }
    
        array_push($response, ["success" => "true"]);
    
        $userData->each(function ($item) use (&$response) {
            array_push($response, $item);
        });
    
        return response()->json($response);
    }
    

    public function view_signature(Request $request)
    {
        try {
            $id = $request->input('id');
    
            if (!$id) {
                return response()->json([
                    'success' => false,
                    'error' => 'missing_id',
                    'message' => 'id parameter is required.',
                ]);
            }
    
            $signature = DB::table('users')
                ->select('signature')
                ->where('id', $id)
                ->first();
    
            if (!$signature) {
                return response()->json([
                    'success' => false,
                    'error' => 'no_data',
                    'message' => 'Signature not found for the user with id ' . $id,
                ]);
            }
    
            return response()->json([
                'success' => true,
                'error' => 'no_error',
                'message' => 'Successfully retrieved user signature.',
                'data' => $signature,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

}

