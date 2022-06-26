<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TokenController extends Controller
{
    /**
     * login
     */
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                "email" => ["required", "email"],
                "password" => ["required"],
            ]);

            $user = User::where("email", $request->input("email"))->first();
            if (!isset($user)) {
                throw new InvalidArgumentException(
                    __("User is not found"),
                    400
                );
            }

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new InvalidArgumentException(
                    __("The provided credentials are incorrect."),
                    400
                );
            }

            $user->tokens()->delete();

            $token = $user->createToken("auth_token");
            $data = [
                "token" => $token->plainTextToken,
                "expireIn" => (int) config("sanctum.expiration", 10),
            ];

            DB::commit();

            return response()->json([
                "message" => __("Success"),
                "data" => $data,
            ]);
        } catch (InvalidArgumentException $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(
                [
                    "message" => $e->getMessage(),
                    "data" => null,
                ],
                $e->getCode()
            );
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(
                [
                    "message" => __("Internal server error"),
                    "data" => null,
                ],
                500
            );
        }
    }
}
