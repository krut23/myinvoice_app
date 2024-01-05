<?php

namespace App\Http\Controllers\Api;

use stdClass;
use App\Models\User;
use Tymon\JWTAuth\JWTAuth;
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
        try {
            $request->validate([
                'user_name' => 'required|string|unique:users',
                'password' => 'required|string|min:8',
            ]);

            // Register the new user.
            $user = User::create([
                'user_name' => $request->user_name,
                'password' => Hash::make($request->password),
            ]);

            // Generate a JWT token.
            $token = Auth::fromUser($user);

            // Save the JWT token to the database.
            $user->remember_token = $token;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'authorization' => $token,
                'user' => $user
            ]);

        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            $errorMessage = '';
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessage .= $message . PHP_EOL;
                }
            }

            return response()->json([
                'success' => false,
                'error' => $errorMessage,
            ], 400);
        }
    }



    public function user_update(Request $request)
    {
        try {
            $validated = $request->validate([
                'business_name' => 'required|string',
                'phone_number' => 'required|string|unique:users',
                'gst_number' => 'sometimes|string',
                'your_name' => 'required|string',
                'name' => 'required|string',
                'business_logo' => 'required|image|max:2048',
                'signature' => 'required|image|max:2048',
                'state' => 'required|string',
                'address' => 'required|string',
            ]);


            $business_logo = $request->file('business_logo');

            if ($business_logo) {
                $business_logo_name = $business_logo->getClientOriginalName();
                $business_logo_path = $business_logo->storeAs('public/business_logos', $business_logo_name);
                $business_logo = $business_logo_name;
            }
            $signature = $request->file('signature');

            if ($signature) {
                $signature_name = $signature->getClientOriginalName();
                $signature_path = $signature->storeAs('public/signatures', $signature_name);
                $signature = $signature_name;
            }

            $user = DB::table('users')
            ->where('id', $request->user()->id)
            ->update([
                'business_name' => $request->input('business_name'),
                'phone_number' => $request->input('phone_number'),
                'gst_number' => $request->input('gst_number'),
                'your_name' => $request->input('your_name'),
                'name' => $request->input('name'),
                'business_logo' => $business_logo,
                'signature' => $signature,
                'state' => $request->input('state'),
                'address' => $request->input('address'),
            ]);

            $user = \DB::table('users')
            ->where('id', $request->user()->id)
            ->first();
            unset($user->password);

            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => 'User updated successfully.',
            ]);
        } catch (ValidationException $exception) {

            $errorResponse = new stdClass();
            $errorResponse->success = false;
            $errorResponse->errors = [];

            foreach ($exception->errors() as $field => $errors) {
                $errorResponse->errors[$field] = $errors[0];
            }

            return response()->json([
                'success' => false,
                $errorResponse],400);
        }
    }

    public function add_signature(Request $request)
    {
        $signature = $request->file('signature');
        $user = Auth::user();
        $id = $user->id;

        if (!$signature || !$id) {
            return response()->json([
                'success' => 'false',
                'error' => 'input(s) is missing',
            ], 400);
        }

        $filename = uniqid() . '.' . $signature->getClientOriginalExtension();

        // Save the signature image to the public/signatures folder
        $signature->storeAs('public/signatures', $filename);

        // Update the user's signature in the database
        DB::table('users')
            ->where('id', $id)
            ->update([
                'signature' => $filename,
            ]);

        // Return the signature image name as a JSON response
        return response()->json([
            'success' => 'true',
            'message' => 'signature Updated',
            'signature' => $filename,
        ]);
    }




    public function businessname_phonenumber(Request $request)
    {
        $user_id = $request->user()->id;

        $sql = "select business_name, phone_number from users where id='{$user_id}'";
        $users = DB::select($sql);

        $response = [
            'success' => 'true',
            'total' => count($users),
            'user_data' => $users[0],
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

        try {
            $sql = "select $FieldList from users $condition";
            $users = DB::select($sql);

            return response()->json([
                'success' => true,
                'total' => count($users),
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function fetch_user_name(Request $request)
    {
        try {
            $user_name = $request->input('user_name');

            if (!empty($user_name)) {
                $users = DB::select("SELECT id, user_name, business_name, phone_number, gst_number, your_name, name, business_logo, signature, state, address, created_at, updated_at FROM users WHERE user_name = ?", [$user_name]);
            } else {
                $users = DB::select("SELECT id, user_name, business_name, phone_number, gst_number, your_name, name, business_logo, signature, state, address, created_at, updated_at FROM users");
            }

            $response = [
                'success' => true,
                'total' => count($users),
                'data' => $users,

            ];

            return response()->json($response);
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage(),

            ];

            return response()->json($response, 500);
        }
    }

    public function fetch_user_password(Request $request)
    {
        $condition = null;
        $FieldList = "user_name,password,id";

        if (isset($request['user_name']) || isset($request['password'])) {
            $condition = " where user_name like '%$request->user_name%' && password like '%$request->password%'";
        }

        $sql = "select $FieldList from users $condition";

        try {
            $result = DB::select($sql);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }

        $response = [
            'success' => true,
            'total' => count($result),
            'data' => $result
        ];

        return response()->json($response);
    }

    public function update_gst_registration(Request $request)
    {
        $input = $request->all();

        // Update the user's GST registration information.
        $user = \DB::table('users')
            ->where('id', $request->user()->id);

        if (isset($input['business_name'])) {
            $user->update(['business_name' => $input['business_name']]);
        }

        if (isset($input['gst_number'])) {
            $user->update(['gst_number' => $input['gst_number']]);
        }

        if (isset($input['your_name'])) {
            $user->update(['your_name' => $input['your_name']]);
        }

        if (isset($input['phone_number'])) {
            $user->update(['phone_number' => $input['phone_number']]);
        }

        // Retrieve the updated user information.
        $user = $user->first();

        // Remove the password from the user information before returning it to the user.
        unset($user->password);

        // Return the updated user information to the user.
        return response()->json([
            'success' => true,
            'message' => 'updated successfully',
            'user' => $user,
        ], 200);
    }


    public function show_ph_gst_address(Request $request)
    {
        $id = $request->user()->id;
        $condition = null;
        $FieldList = "id,phone_number,gst_number,state,address,your_name,business_name,business_logo";

        if (!$id) {
            return response()->json(['success' => false, 'error' => 'Missing id']);
        }

        $condition = "WHERE id = '$id'";

        $sql = "SELECT $FieldList FROM users $condition";

        $users = DB::select($sql);

        if (count($users) > 0) {
            $responseData = [
                'success' => true,
            ];

            foreach ($users as $user) {
                $responseData['user_data'] = [
                    'id' => $user->id,
                    'phone_number' => $user->phone_number,
                    'gst_number' => $user->gst_number,
                    'state' => $user->state,
                    'address' => $user->address,
                    'your_name' => $user->your_name,
                    'business_name' => $user->business_name,
                    'business_logo' => $user->business_logo,
                ];
            }

            return response()->json($responseData);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No user data found.'
            ]);
        }
    }



    public function update_business_user(Request $request)
    {
        $response = [];

        $id = $request->user()->id;
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

        // Save the uploaded business logo file
        if ($request->hasFile('business_logo')) {
            $business_logo = $request->file('business_logo');
            $filename = uniqid() . '.' . $business_logo->getClientOriginalExtension();
            $business_logo = Storage::putFileAs('public/business_logos', $business_logo, $filename);

            // Get only the image name
            $validFields['business_logo'] = $filename;
        }

        // Update the user information in the database
        DB::table('users')->where('id', $id)->update($validFields);
        $updatedUser = DB::table('users')->where('id', $id)->first();

        // Get the business user information
        $businessUserInfo = [
            'id' => $id,
            'business_name' => $updatedUser->business_name,
            'business_logo' => $updatedUser->business_logo,
            'gst_number' => $updatedUser->gst_number,
            'state' => $updatedUser->state,
            'address' => $updatedUser->address,
        ];

        return response()->json([
            'success' => true,
            'message' => 'User information updated',
            'user_data' => $businessUserInfo,
        ]);
    }


    public function update_phone_number(Request $request)
    {
        $response = [];

        // Get the current user ID.
        $id = Auth::user()->id;

        $phone_number = $request->input('phone_number');

    // Validate the new phone number.
    $validator = \Validator::make([
        'phone_number' => $phone_number,
    ], [
        'phone_number' => 'required|string|min:10|max:10',
    ]);

    if ($validator->fails()) {
        // Get the first validation error message.
        $errorMessage = $validator->errors()->first();

        // Return a validation error response.
        return response()->json([
            'success' => false,
            'error' => $errorMessage,
        ], 400);
    }

    // Update the user's phone number in the database.
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

    // Return a success response.
    return response()->json([
        'success' => true,
        'message' => 'User updated successfully',
    ]);
    }



    public function update_user_name(Request $request)
    {
        $response = [];

        $request->validate([
            'user_name' => 'required',
        ]);

        $id = $request->user()->id;
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

        return response()->json([
            'success' => true,
            "message" => "Name successfully updated",
            'data' => $userName,
        ]);
    }


    public function user_add_sign_logo_ph(Request $request)
    {
        $response = [];

        $id = $request->user()->id;
        $fieldList = [
            'id',
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
        'user_data' => $response[0],
        ]);
    }




    public function user_name_logo(Request $request)
    {
        $response = [];

        $id = $request->user()->id;
        $fieldList = [
            'id',
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

         // Get the first user record.
       $userData = $userData->first();

        return response()->json(["success" => true,'user_data' => $userData,]);
    }


    public function view_signature(Request $request)
    {
        try {
            $id = $request->user()->id;


            $signature = DB::table('users')
                ->select('signature')
                ->where('id', $id)
                ->first();

            if (!$signature) {
                return response()->json([
                    'success' => false,
                    'error' => 'Signature not found for the user with id ' . $id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully retrieved user signature.',
                'user_data' => $signature,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteSignature(Request $request)
{
    $user = Auth::user();

    // Check if the user has a signature
    if (!$user->signature) {
        return response()->json([
            'success' => false,
            'error' => 'User does not have a signature',
        ], 404);
    }

    // Delete the signature image from the public/signatures folder
    Storage::delete('public/signatures/' . $user->signature);

    // Update the user's signature in the database
    $user->signature = null;
    $user->save();

    // Return a success response
    return response()->json([
        'success' => true,
        'message' => 'Signature deleted successfully',
    ]);
}


}

