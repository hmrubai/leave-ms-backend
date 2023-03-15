<?php
namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function registerUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'employee_code' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'data' => $validateUser->errors()
                ], 409);
            }

            $profile_image = null;
            $profile_url = null;
            if($request->hasFile('image')){
                $image = $request->file('image');
                $time = time();
                $profile_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                $destinationProfile = 'uploads/profile';
                $image->move($destinationProfile, $profile_image);
                $profile_url = $destinationProfile . '/' . $profile_image;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_no' => $request->contact_no,
                'country_id' => $request->country_id,
                'address' => $request->address,
                'institution' => $request->institution,
                'education' => $request->education,
                'user_type' => $request->user_type ? $request->user_type : "Student",
                'password' => Hash::make($request->password)
            ]);

            if($request->hasFile('image')){
                User::where('id', $user->id)->update([
                    'image' => $profile_url
                ]);
            }

            $response_user = [
                'name' => $user->name, 
                'email'=> $user->email, 
                'user_type' => $request->user_type ? $request->user_type : "Student", 
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];

            return response()->json([
                'status' => true,
                'message' => 'Registration Successful',
                'data' => $response_user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'data' => $validateUser->errors()
                ], 409);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                    'data' => []
                ], 409);
            }

            $user = User::where('email', $request->email)->first();

            if(!$user->is_active){
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been suspended. Please, contact to Administrator!',
                    'data' => []
                ], 409);
            }

            $response_user = [
                'id' => $user->id,
                'name' => $user->name,
                'employee_code' => $user->employee_code,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'image' => $user->image,
                'address' => $user->address,
                'institution' => $user->institution,
                'education' => $user->education,
                'contact_no' => $user->contact_no,
                'updated_at' => $user->updated_at,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'data' => $response_user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function loginViaCode(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'employee_code' => 'required',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'data' => $validateUser->errors()
                ], 409);
            }

            if(!Auth::attempt($request->only(['employee_code', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Employee Code & Password does not match with our record.',
                    'data' => []
                ], 409);
            }

            $user = User::where('employee_code', $request->employee_code)->first();

            if(!$user->is_active){
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has been suspended. Please, contact to Administrator!',
                    'data' => []
                ], 409);
            }

            $response_user = [
                'id' => $user->id,
                'name' => $user->name,
                'employee_code' => $user->employee_code,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'image' => $user->image,
                'address' => $user->address,
                'institution' => $user->institution,
                'education' => $user->education,
                'contact_no' => $user->contact_no,
                'updated_at' => $user->updated_at,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'data' => $response_user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function profileDetailsByID($user_id)
    {
        $user = User::select('users.*', 'countries.country_name')->where('users.id', $user_id)
        ->leftJoin('countries', 'countries.id', 'users.country_id')
        ->first();

        return $user;
    }

    public function saveOrUpdateUser(Request $request)
    {
        try {
            $formData = json_decode($request->data, true);
            if($formData['id']){
                $profile_url = null;
                if($request->hasFile('file')){
                    $image = $request->file('file');
                    $time = time();
                    $feature_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                    $destinationProfile = 'uploads/profile';
                    $image->move($destinationProfile, $feature_image);
                    $profile_url = $destinationProfile . '/' . $feature_image;
                }

                User::where('id', $formData['id'])->update([
                    'name' => $formData['name'],
                    'email' => $formData['email'],
                    'contact_no' => $formData['contact_no'],
                    'country_id' => $formData['country_id'],
                    'address' => $formData['address'],
                    'institution' => $formData['institution'],
                    'education' => $formData['education'],
                    'user_type' => $formData['user_type'] ? $formData['user_type'] : "Student"
                ]);

                if($request->hasFile('file')){
                    User::where('id', $formData['id'])->update([
                        'image' => $profile_url
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'User has been updated successfully',
                    'data' => []
                ], 200);

            } else {
                $isExist = User::where('email', $formData['email'])->first();
                if (empty($isExist)) 
                {
                    $profile_url = null;
                    if($request->hasFile('file')){
                        $image = $request->file('file');
                        $time = time();
                        $feature_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                        $destinationProfile = 'uploads/profile';
                        $image->move($destinationProfile, $feature_image);
                        $profile_url = $destinationProfile . '/' . $feature_image;
                    }

                    $user = User::create([
                        'name' => $formData['name'],
                        'email' => $formData['email'],
                        'contact_no' => $formData['contact_no'],
                        'country_id' => $formData['country_id'],
                        'address' => $formData['address'],
                        'institution' => $formData['institution'],
                        'education' => $formData['education'],
                        'user_type' => $formData['user_type'] ? $formData['user_type'] : "Student"
                    ]);

                    if($request->hasFile('file')){
                        User::where('id', $user->id)->update([
                            'image' => $profile_url
                        ]);
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'User has been added successfully',
                        'data' => []
                    ], 200);
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'User already Exist!',
                        'data' => []
                    ], 409);
                }
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 400);
        }
    }

    public function changePassword(Request $request)
    {
        $user_id = $request->user()->id;
        $user = User::where('users.id', $user_id)->first();

        $validateUser = Validator::make($request->all(), 
        [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        $user_object = [
            'email' => $user->email, 
            'password' => $request->old_password
        ];

        if(!Auth::guard('web')->attempt($user_object)){
            return response()->json([
                'status' => false,
                'message' => 'Email & Password does not match with our record.',
                'data' => []
            ], 409);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Your password has been updated successful!',
            'data' => $user
        ], 200);
    }

    public function updatePasswordByAdmin(Request $request)
    {
        $user_id = $request->user_id;
        $user = User::where('users.id', $user_id)->first();

        $validateUser = Validator::make($request->all(), 
        [
            'user_id' => 'required',
            'new_password' => 'required'
        ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'data' => $validateUser->errors()
            ], 409);
        }

        if(empty($user)){
            return response()->json([
                'status' => false,
                'message' => 'User not found!',
                'data' => []
            ], 409);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Your password has been updated successful!',
            'data' => $user
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $user_id = $request->user()->id;
        try {
            // $validateUser = Validator::make($request->all(), 
            // [
            //     'name' => 'required'
            // ]);

            // if($validateUser->fails()){
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'validation error',
            //         'data' => $validateUser->errors()
            //     ], 409);
            // }

            if(!$request->name && !$request->contact_no && !$request->country_id && !$request->address && !$request->institution && !$request->education && !$request->hasFile('image')){
                return response()->json([
                    'status' => false,
                    'message' => 'Please, attach information!',
                    'data' => []
                ], 200);
            }

            $profile_image = null;
            $profile_url = null;
            if($request->hasFile('image')){
                $image = $request->file('image');
                $time = time();
                $profile_image = "profile_image_" . $time . '.' . $image->getClientOriginalExtension();
                $destinationProfile = 'uploads/profile';
                $image->move($destinationProfile, $profile_image);
                $profile_url = $destinationProfile . '/' . $profile_image;
            }

            if($request->name){
                User::where('id', $user_id)->update([
                    'name' => $request->name
                ]);
            }

            if($request->contact_no){
                User::where('id', $user_id)->update([
                    'contact_no' => $request->contact_no
                ]);
            }

            if($request->country_id){
                User::where('id', $user_id)->update([
                    'country_id' => $request->country_id
                ]);
            }

            if($request->address){
                User::where('id', $user_id)->update([
                    'address' => $request->address
                ]);
            }

            if($request->institution){
                User::where('id', $user_id)->update([
                    'institution' => $request->institution
                ]);
            }

            if($request->education){
                User::where('id', $user_id)->update([
                    'education' => $request->education
                ]);
            }

            // User::where('id', $user_id)->update([
            //     'name' => $request->name,
            //     'contact_no' => $request->contact_no,
            //     'country_id' => $request->country_id,
            //     'address' => $request->address,
            //     'institution' => $request->institution,
            //     'education' => $request->education
            // ]);

            if($request->hasFile('image')){
                User::where('id', $user_id)->update([
                    'image' => $profile_url
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Updated Successful',
                'data' => $this->profileDetailsByID($user_id)
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        $user_id = $request->user()->id;
        $user = User::select('users.*', 'countries.country_name')->where('users.id', $user_id)
        ->leftJoin('countries', 'countries.id', 'users.country_id')
        ->first();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $user
        ], 200);
    }

    public function getExpertList(Request $request)
    {
        $users = User::select('users.id', 'users.name', 'users.email', 'users.contact_no', 'users.address', 'users.education', 'users.institution', 'users.image', 'countries.country_name')
        ->where('users.user_type', 'Expert')
        ->where('users.is_active', true)
        ->leftJoin('countries', 'countries.id', 'users.country_id')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $users
        ], 200);
    }

    public function getAdminExpertList(Request $request)
    {
        $users = User::select('users.*', 'countries.country_name')
        ->where('users.user_type', 'Expert')
        ->leftJoin('countries', 'countries.id', 'users.country_id')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Successful',
            'data' => $users
        ], 200);
    }

    public function deleteUserAccount(Request $request)
    {
        $user_id = $request->user()->id;

        $user = User::where('id', $user_id)->first();

        User::where('id', $user_id)->update([
            "contact_no" => $user_id . "_deleted_" . $user->contact_no,
            "email" => $user_id . "_deleted_" . $user->email,
            "is_active" => false
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Account deleted successful',
            'data' => []
        ], 200);
    }
}
