<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 400);
        }
    
        try {
            $phoneNumber = $request->input('mobile');
            $password = $request->password;
            $user = Employee::where('mobile', $phoneNumber)->first();
    
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }
            if (Hash::check($password, $user->password)) {
				
                if ($user->status == 1) {
                    
                    $assignedPermissions = DB::table('user_permission_categories')
                                                ->select('user_permission_categories.*')
                                                ->join('employees','employees.id','=','user_permission_categories.employee_id')
                                                ->where('user_permission_categories.employee_id', $user->id)
                                                ->get();

                                            $brandMap = [
                                                1 => 'ONN',
                                                2 => 'PYNK',
                                                3 => 'Both',
                                            ];

                                            $brands = $assignedPermissions->pluck('brand')->unique()->toArray();

                                    // Check conditions
                                        if (in_array(3, $brands)) {
                                            $brandPermissions = 'Both';
                                        } elseif (in_array(1, $brands) && in_array(2, $brands)) {
                                            $brandPermissions = 'Both';
                                        } else {
                                            $brandPermissions = collect($brands)
                                                ->map(fn($brand) => $brandMap[$brand] ?? $brand)
                                                ->implode(', ');
                                        }
                    
        
                    return response()->json([
                        'status' => true,
                        'message' => 'Login successful',
                        'user' => $user,
                        'brand' => $brandPermissions
                       
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Your account is temporarily blocked. Contact Admin.'
                    ], 403); 
                }
            } else {
                return response()->json(['status' => true, 'message' => 'You have entered wrong login credential. Please try with the correct one.', 'data' => $userCheck->password]);
            }
    
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'An error occurred while login.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    


    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'error' => $validator->errors()], 400);
        }
        try{
            $phoneNumber = $request->mobile;
            $otp = $request->otp;

            $user = User::where('mobile', $phoneNumber)
                        ->where('otp', $otp)
                        ->first();
            if ($user) {
                return response()->json(['status' => true,'message' => 'OTP verified successfully','data' =>$user], 200);
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP or mobile number'
                    
                ], 401);
            }
        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Book transfer error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred during the book transfer.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

}