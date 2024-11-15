<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 
use Carbon\Carbon;

class AuthController extends Controller
{
    // Registration method
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    // Login method
    public function login(Request $request)
    { 
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]); 

        if (!Auth::attempt($validated)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.']
            ]);
        }

        $user = Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    // Logout method
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function generateResetToken(Request $request)
    { 
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]); 
 
        $user = User::where('email', $request->email)->first();
        
        //
        $token = Str::random(60);
 
        $expiresAt = Carbon::now()->addSeconds(120);
 
        $user->password_reset_token = $token;
        $user->password_reset_token_expires_at = $expiresAt;
        $user->save();
 
        return response()->json([
            'message' => 'Password reset token generated successfully.',
            'token' => $token,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    }
    public function resetPassword(Request $request)
    { 
        $validator = $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]); 
 
        $user = User::where('email', $request->email)->first();

        if (!$user) { 
            return response()->json(['error' => 'User not found'], 400);
        }
 
        if ($user->password_reset_token !== $request->token) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        if (Carbon::now()->greaterThan($user->password_reset_token_expires_at)) {
            return response()->json(['error' => 'Token has expired'], 400);
        }
 
        $user->password = bcrypt($request->password);
        $user->password_reset_token = null;
        $user->password_reset_token_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }
}
