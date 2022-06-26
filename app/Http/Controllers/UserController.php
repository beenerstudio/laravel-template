<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $sort = $request->has("sort") ? $request->query("sort") : "name";
            $order = $request->has("order") ? $request->query("order") : "asc";
            $query = User::select("users.*")
                ->orderBy($sort, $order);
            $users = $query->paginate(
                $request->query("limit", $query->count())
            );

            return response()->json([
                "message" => __("Success"),
                "data" => $users,
            ]);
        } catch (Exception $e) {
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                "email" => ["required", "email"],
                "name" => ["required"],
                "password" => ["required"],
                "roles" => ["required"],
            ]);

            DB::beginTransaction();

            $user = new User();
            $user->email = $request->input("email");
            $user->name = $request->input("name");
            $user->password = Hash::make($request->input("password"));
            $user->created_at = now();
            $user->updated_at = now();
            $user->save();

            $role_ids = $request->input("roles");
            $user->roles()->sync($role_ids);

            DB::commit();

            return response()->json([
                "message" => __("Success"),
                "data" => $user,
            ]);
        } catch (ValidationException $e) {
            Log::error($e->getMessage());
            return response()->json(
                [
                    "message" => $e->getMessage(),
                    "data" => null,
                ],
                400
            );
        } catch (InvalidArgumentException $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(
                [
                    "message" => $e->getMessage(),
                    "data" => null,
                ],
                400
            );
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                return response()->json(
                    [
                        "message" => __("The user has been registered"),
                        "data" => null,
                    ],
                    400
                );
                return response()->json(
                    [
                        "message" => __("Internal server error"),
                        "data" => null,
                    ],
                    500
                );
            }
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                "email" => ["required", "email"],
                "name" => ["required"],
                "password" => ["required"],
                "roles" => ["required"],
            ]);

            DB::beginTransaction();

            $user = User::find($id);
            if (!isset($user)) {
                throw new InvalidArgumentException(__("User is not found"));
            }

            $roles = $user->roles;
            for ($i = 0; $i < count($roles); $i++) {
                if ($roles[$i]->id === 1) {
                    throw new InvalidArgumentException(
                        __("Admin can not be updated")
                    );
                }
            }

            $user->email = $request->input("email");
            $user->name = $request->input("name");
            $user->password = Hash::make($request->input("password"));
            $user->updated_at = now();
            $user->save();

            $role_ids = $request->input("roles");
            for ($i = 0; $i < count($role_ids); $i++) {
                if ($role_ids[$i] === 1) {
                    throw new InvalidArgumentException(
                        __("Sys admin can not be used by normal user")
                    );
                }
            }
            $user->roles()->sync($role_ids);

            DB::commit();

            return response()->json([
                "message" => __("Success"),
                "data" => null,
            ]);
        } catch (ValidationException $e) {
            Log::error($e->getMessage());
            return response()->json(
                [
                    "message" => $e->getMessage(),
                    "data" => null,
                ],
                400
            );
        } catch (InvalidArgumentException $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(
                [
                    "message" => $e->getMessage(),
                    "data" => null,
                ],
                400
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $user = User::find($id);
            if (!isset($user)) {
                throw new InvalidArgumentException(__("User is not found"));
            }

            $roles = $user->roles;
            for ($i = 0; $i < count($roles); $i++) {
                if ($roles[$i]->id === 1) {
                    throw new InvalidArgumentException(
                        __("Sys admin can not be deleted")
                    );
                }
            }

            $user->delete();

            DB::commit();

            return response()->json([
                "message" => __("Success"),
                "data" => null,
            ]);
        } catch (ValidationException $e) {
            Log::error($e->getMessage());
            return response()->json(
                [
                    "message" => $e->getMessage(),
                    "data" => null,
                ],
                400
            );
        } catch (InvalidArgumentException $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(
                [
                    "message" => $e->getMessage(),
                    "data" => null,
                ],
                400
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
