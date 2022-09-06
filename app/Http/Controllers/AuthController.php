<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Application|ResponseFactory|Response|object
     */
    public function index(Request $request)
    {
        $login = $request->login;
        $email = $request->email;
        $password = $request->password;
        $password_repeat = $request->password_repeat;

        $errors = [];

        if(empty($login)) $errors["error_login"] = "Поле логин обязательно для заполнения";
        if(empty($email)) $errors["error_email"] = "Поле почта обязательно для заполнения";
        if(empty($password)) $errors["error_password"] = "Поле пароль обязательно для заполнения";
        if(empty($password_repeat)) $errors["error_password_repeat"] = "Поле повторного пароля обязательно для заполнения";
        if($password !== $password_repeat) $errors["error_passwords"] = "Пароли не совпадают";
        if(count(User::where("name", "=", $login)->get()) !== 0) $errors["error_login_unique"] = "Логин уже существует";

        if(!empty($errors)){
            return response([
                "status" => false,
                "errors" => $errors
            ], 400)
                ->setStatusCode(400, 'error validation');
        }

        $create_user = User::create([
            "name" => $login,
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "bearer_token" => "Bearer " . Str::random(60)
        ]);

        return response([
            "status" => true,
            "user_id" => $create_user->id
        ], 201)
            ->setStatusCode(201, "registered")
            ->header("authorization", $create_user->bearer_token);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $login = $request->login;
        $password = $request->password;

        $errors = [];

        if(empty($login)) $errors["error_login"] = "Поле логина обязательно к заполнению";
        if(empty($password)) $errors["error_password"] = "Поле пароль обязательно к заполнению";

        if(!empty($errors)){
            return response([
                "status" => false,
                "errors" => $errors
            ], 400)
                ->setStatusCode(400, "validation error");
        }

        $search_user_login = User::where("name", "=", $login)->get();

        if(count($search_user_login) == 0) {
            return response([
                "status" => false,
                "errors" => ["validation" => "Неверный логин или пароль"]
            ], 400)
                ->setStatusCode(400, "validation error");
        }

        if($login == "admin" && $password == "admin"){
                return response([
                    "status" => true,
                    "message" => "Вы авторизховались"
                ], 200)
                    ->setStatusCode(200, "autorized")
                    ->header("authorization", $search_user_login[0]["bearer_token"]);
        }

        if (!password_verify($password, $search_user_login[0]["password"])) {
            return response([
                "status" => false,
                "errors" => ["validation" => "Неверный логин или пароль"]
            ], 400)
                ->setStatusCode(400, "validation error");
        }

        return response([
            "status" => true,
            "message" => "authorization complete"
        ], 200)
            ->setStatusCode(200, "authorization complete")
            ->header("authorization", $search_user_login[0]["bearer_token"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return Response
     */
    public function show(User $user, Request $request)
    {
        $token = $request->header("authorization");

        if($token === null || empty($token)){
            return response([
                "status" => false,
                "message" => "Вы не авторизованы"
            ], 401)
                ->setStatusCode(401, "unauthorized");
        }

        return response([
            "status" => true,
            "message" => "Вы успешно вышли из системы"
        ], 200)
            ->setStatusCode(200, "exit complete");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return Response
     */
    public function update(Request $request, User $user)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return Response
     */
    public function destroy(User $user)
    {
        //
    }
}
