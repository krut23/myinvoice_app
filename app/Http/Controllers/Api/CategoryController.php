<?php

namespace App\Http\Controllers\Api;

use stdClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Validation\ValidationException;


class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(Authenticate::class);
    }
    public function createCategory(Request $request){
        try {
            $request->validate([
                'category_name' => 'required|string|max:255|unique:category,category_name',
            ]);

            $userId = $request->user()->id;
            $categoryName = $request->input('category_name');

            DB::table('category')->insert([
                'user_id' => $userId,
                'category_name' => $categoryName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category Added'
            ], 201);
        } catch (ValidationException $exception) {

            $errorResponse = new stdClass();
            $errorResponse->success = false;
            $errorResponse->errors = [];

            foreach ($exception->errors() as $field => $errors) {
                $errorResponse->errors[] = $errors[0];
            }

            $errorMessage = implode("\n", $errorResponse->errors);

            return response()->json([
                'success' => false,
                'error' => $errorMessage
            ], 400);

        }
    }

    public function delete_category_all_data(Request $request)
    {
        $userId = $request->input('user_id');

        // Check if the user exists in the "category" table
        $userExists = DB::table('category')->where('user_id', $userId)->exists();

        if (!$userExists) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        try {
            DB::table('category')->where('user_id', $userId)->delete();
            return response()->json(['message' => 'Data deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function delete_category(Request $request)
    {
        $id = $request->input('id');

        // Check if the user exists in the "category" table
        $userExists = DB::table('category')->where('id', $id)->exists();

        if (!$userExists) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        try {
            DB::table('category')->where('id', $id)->delete();
            return response()->json(['message' => 'Data deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function select_category_name(Request $request)
    {
        $user_id = $request->input('user_id');
        $response = [];
    
        if (!$user_id) {
            return response()->json([
                'success' => false,
                'error' => 'missing_user_id',
                'message' => 'user_id parameter is required.',
            ]);
        }
    
        $categories = DB::table('category')
            ->select('id', 'category_name')
            ->where('user_id', $user_id)
            ->get();
    
        $count = $categories->count();
    
        if ($count > 0) {
            return response()->json([
                'success' => true,
                
                'message' => 'Successfully get user fieldlist data.',
                'total' => $count,
                'data' => $categories,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'no_data',
                'message' => 'No categories found for the user with id ' . $user_id,
            ]);
        }
    }
    


    public function update_category(Request $request)
    {
        $response = [];
    
        $categoryName = $request->input('category_name');
        $id = $request->input('id');
    
        if (!$categoryName || !$id) {
            return response()->json(['success' => false, 'error' => 'Input(s) missing'], 400);
        }
    
        $count = DB::table('category')->where('id', $id)->count();
    
        if ($count === 0) {
            return response()->json([
                'success' => false,
                'error' => 'Record not found for the given id.',
            ], 404);
        }
    
        DB::table('category')->where('id', $id)->update(['category_name' => $categoryName]);
    
        array_push($response, ['message' => 'Successfully updated category']);
    
        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }
    

    public function view_category(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $condition = null;
            $fieldList = ['id', 'category_name'];
    
            if (!empty($user_id)) {
                $condition = ['user_id' => $user_id];
                $fieldList = ['*'];
            }
    
            $categories = DB::table('category')
                ->select($fieldList)
                ->where($condition)
                ->get();
    
            $count = $categories->count();
    
            if ($count > 0) {
                return response()->json([
                    'success' => true,
                    'total' => $count,
                    'data' => $categories,
                    'error' => 'no_error',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'no_data',
                    'message' => 'No categories found for the user with id ' . $user_id,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

}
