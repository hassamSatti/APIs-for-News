<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 
use Carbon\Carbon;
use App\Traits\ApiResponseTrait;
/**
 * @OA\Info(
 *     title="API Documentation",
 *     description="API for authentication system",
 *     version="1.0.0"
 * )
 */
class AuthController extends Controller
{
    use ApiResponseTrait;
    // Registration method

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Test User"),
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="password", type="string", example="passcode"),
     *             @OA\Property(property="password_confirmation", type="string", example="passcode")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

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

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
    }

    // Login method

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login a user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="password", type="string", example="passcode")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=422, description="Invalid credentials")
     * )
     */
    public function login(Request $request)
    { 
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]); 

        if (!Auth::attempt($validated)) {
            return $this->errorResponse('Invalid credentials', 422);
        }

        $user = Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    // Logout method

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout a user",
     *     tags={"Authentication"},
     *     @OA\Response(response=200, description="Logged out successfully")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/reset-token",
     *     summary="Generate password reset token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset token generated"),
     *     @OA\Response(response=422, description="Email not found")
     * )
     */
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
 
        return $this->successResponse([
            'token' => $token,
            'expires_at' => $expiresAt->toDateTimeString(),
        ], 'Password reset token generated successfully.');
    }
    /**
     * @OA\Post(
     *     path="/api/auth/reset-password",
     *     summary="Reset user password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="reset-token"),
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset successfully"),
     *     @OA\Response(response=422, description="Invalid token or expired")
     * )
     */
    public function resetPassword(Request $request)
    { 
        $validator = $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]); 
 
        $user = User::where('email', $request->email)->first();

        if (!$user) { 
            return $this->errorResponse('User not found', 400);
        }
 
        if ($user->password_reset_token !== $request->token) {
            return $this->errorResponse('Invalid token', 400);
        }

        if (Carbon::now()->greaterThan($user->password_reset_token_expires_at)) {
            return $this->errorResponse('Token has expired', 400);
        }
 
        $user->password = bcrypt($request->password);
        $user->password_reset_token = null;
        $user->password_reset_token_expires_at = null;
        $user->save();

        return $this->successResponse(null, 'Password reset successfully.');
    }
}
