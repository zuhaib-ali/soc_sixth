<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ForgetPasswordCode;
use App\Models\User;
use App\Models\DeviceInformationModal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    
    // Admin registration.
    public function adminRegister(Request $request){
                
        // Admin validation
        $validator = Validator::make($request->all(), [
            'name' => "required",
            'email' => "required|email|unique:users",
            'password' => "required"
        ]);
        
        if($validator->errors()->count() != 0){
            return $validator->errors();
        }
        
        
        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'admin'
        ]);

        if($admin){
            return response()->json([
                "status"=>true,
                "message" => 'Registered successfuly',
                "data" => $admin
            ]);
            
        }else{
            return response()->json([
                "status"=>false,
                "message" => 'Failed to register admin'
            ]);
        }
        
    }
    
    
    // User registration.
    public function userRegister(Request $request){
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => "required",
            'email' => "required|email|unique:users",
            'password' => "required",
            'account_type' => "required",
        ]);
        
        if($validator->errors()->count() != 0){
            return $validator->errors();
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'company_name' => $request->company_name,
            'account_type' => $request->account_type,
            'role' => ucfirst($request->account_type)
        ]);

        if($user){
            return response()->json([
                "status"=>true,
                "message" => 'Registered successfuly',
                "data" => $user
            ]);
            
        }else{
            return response()->json([
                "status"=>false,
                "message" => 'Failed to register'
            ]);
        }
    }

    // AUTHENTICATE
    public function authenticate(Request $request){
        return "hello";
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        
        if($validator->errors()->count() != null){
            return $validator->errors();
        }
        
        // If token does not exists.
        if(!$request->has('token')){
            return 'Provide and access token.';

        }else{
            // If token is null or does not matches.
            if($request->token == null){
                return response()->json([
                    "status"=>false,
                    "message" => 'Incorrect access token'
                ]);

            }else{
                if($request->token != env('API_TOKEN')){
                    return response()->json([
                        "status"=>false,
                        "message" => 'Incorrect access token'
                    ]);
                }
            }
        }

        // User if email mataches.
        $user = User::where('email', $request->email)->first();
        
        // if user or password does not meet the credientials.
        if(!$user || $user->password != $request->password){
            return response()->json([
                "status"=>false,
                "message" => "credientials does not match."
            ]);
        }
        
        // All devices user logged in.
        // if($request->device_id != null && $user->role != 'admin'){
        //     $devices = DeviceInformationModal::where(['user_id' => $user->id])->get(); // User logged in with devices.
        //     // devices must be within 3.
        //     if($devices->count() < 4){
                
        //         if(in_array($request->device_id, json_decode($devices->pluck('device_id'))) != true){ 
        //             DeviceInformationModal::create([
        //                 'user_id' => $user->id,
        //                 'device_id' => $request->device_id
        //             ]);
        //         }
            
        //     // Else restricted message.
        //     }else{
        //         return response()->json([
        //             "status"=>false,
        //             "message" => "Login is allowed for only 3 different devices"
        //         ]);
        //     }    
        // }
        
        
        // If account is deactivated.
        if($user->isActivated == false){
            return response()->json([
                "status"=>false,
                "message" => "Your account is deactivated, activate your account to login."
            ]);
        }
        
        return response()->json([
            "status"=>true,
            "user"=>$user,
            "token" => $request->token,
            // "device id"  => $request->device_id
        ]);
    }
    
    
    // GET ALL USERS
    public function getUsers(){
        return response()->json([
            "status" => true,
            "data" => User::where('role', '!=', 'admin')->get()
        ]);
    }
    
    
    // Get user
    public function getUser($name){
        $user = User::where("name", "LIKE", "%".$name."%")
            ->orWhere("email", "LIKE", "%".$name."%")
            ->orWhere("role", "LIKE", "%".$name."%")
            ->get();
        
        if($user->count() != 0){
            return response()->json([
                'status' => true,
                'data' => $user
            ]);    
        }
    }
    
    // Reset password
    public function resetPassword(Request $request){
        
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'old_password' => 'required',
            'new_password' => 'required'
        ]);
        
        if($validator->errors()->count() != 0){
            return $validator->errors();    
        }
        
        
        // Retrive user by email to update password.
        $user = User::where('email', $request->email)->first();
        if(!$user || $user->password != $request->old_password){
            return response()->json([
                'status'=> false,
                'message'=> "Credientials does not match."
            ]);    
            
        }else{
            $user->password = $request->new_password;
            $user->update();    
            return response()->json([
                'status'=> true,
                'message'=> "Password update."
            ]);
        }
        
    }
    
    // Activate and Deactivate user.
    public function activeDeactive(Request $request){
    	$validator = Validator::make($request->all(), [
    		'email' => 'required',
    	]);
            
    	if($validator->errors()->count() != 0){
    		return $validator->errors();
    	}
    	
    	$user = User::where(['email' => $request->email])->first();
            
    	if(!$user){
    		return response([
    			'status' => false,
    			'message' => 'User not found!',
    		]);
    	}
        if($user->isActivated == false){
            $user->isActivated = true;
            $user->update();
        	return response([
        		'status' => true,
        		'message' => 'User activated!'
        	]);
        }else{
            $user->isActivated = false;
            $user->update();
        	return response([
        		'status' => true,
        		'message' => 'User deactivated!'
        	]);
        }
    }
    
    // Forgot password
    public function forgetPassword(Request $request){
        $generator = "1357902468";
        $result = "";
        // Random otp generating.
        for ($i = 1; $i <= 5; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }

        // Data to mail.
        $data = [
            'title' => 'JIRA FORGET PASSWORD CODE',
            'message' => "Code: ". $result,
        ];

        Mail::to($request->email)->send(new ForgetPasswordCode($data));
        
        // Update otp code for user.
        $user = User::where('email', $request->email)->first();
        $user->otp = $result;
        $user->update();
        
        return response([
			'status' => true,
			'message' => "mail sent to ". $request->email,
		]);
    }
    
    // Set password
    public function setPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password'
        ]);
        
        if($validator->errors()->count() != 0){
           return $validator->errors(); 
        }
        
        $user = User::where('email', $request->email)->first();
        $user->password = $request->confirm_password;
        $user->update();
        return response([
			'status' => true,
			'message' => "New password changes successfuly",
		]);
    }
}
