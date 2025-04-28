<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class AuthController extends Controller
{
    /**
     * Display the authentication view.
     * 
     * @return \Illuminate\View\View
     */
    public function index ()
    {
        return view('auth');
    }

    /**
     * Handle user login request.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws \Exception
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // try {
            $client = new Client();
            $response = $client->post('https://api.extest.link/api/v1/login', [
                'json' => [
                    'email' => $request->email,
                    'password' => $request->password
                    
                ]
            ]);


            $data = json_decode($response->getBody(), true);
            
            if (!isset($data['data'])) {
                return response()->json([
                    'message' => 'Invalid login credentials'
                ], 401);
            }

            $userData = $data['data'];
            $employee = $userData['employee'];
            // Create or update user in local database

            // Check if the user already exists in the local database
            $existingUser = User::where('user_id', $userData['id'])->first();

            if ($existingUser) {
                // If the user exists, update their information
                $user = $existingUser;
                $user->id_number = $employee['personal_info']['id_number'];
                $user->full_name = $employee['full_name'] ?? '';
                $user->email = $userData['email'];
                $user->password = $request->password;
                $user->position = $employee['position']['name'] ?? null;
                $user->employment_status = $employee['employment_status'] ?? null;
                $user->official_station = $employee['official_station']['location'] ?? null;
                $user->signature_url = $employee['signature_url'] ? str_replace('https://empowerex.s3.ap-southeast-1.amazonaws.com/', '', strtok($employee['signature_url'], '?')) : null;
                $user->empowerex_token = $data['access_token'];
                $user->empowerex_refresh_token = $data['refresh_token'];
                $user->roles = json_encode($userData['roles'] ?? []); // Save roles as JSON
                $user->save();
            } else {
                // If the user does not exist, create a new user
                $user = new User();
                $user->user_id = $userData['id'];
                $user->id_number = $employee['personal_info']['id_number'];
                $user->full_name = $employee['full_name'] ?? '';
                $user->email = $userData['email'];
                $user->password = $request->password;
                $user->position = $employee['position']['name'] ?? null;
                $user->employment_status = $employee['employment_status'] ?? null;
                $user->official_station = $employee['official_station']['location'] ?? null;
                $user->signature_url = $employee['signature_url'] ? str_replace('https://empowerex.s3.ap-southeast-1.amazonaws.com/', '', strtok($employee['signature_url'], '?')) : null;
                $user->empowerex_token = $data['access_token'];
                $user->empowerex_refresh_token = $data['refresh_token'];
                $user->roles = json_encode($userData['roles'] ?? []); // Save roles as JSON
                $user->save();
            }

            // Create token for the user
            $token = $user->createToken('auth-token')->accessToken;

            return response()->json([
                'user' => $user,
                'access_token' => $token,
                'external_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token']
            ]);

        // } catch (\Exception $e) {
        //     return response()->json([
        //         'message' => 'Invalid login credentials',
        //         'error' => $e->getMessage()
        //     ], 401);
        // }
    }

    /**
     * Handle user logout request.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated user information.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = Auth::user();
        return response()->json($user);
    } 
}
