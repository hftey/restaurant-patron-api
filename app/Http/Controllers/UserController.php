<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the users
     *
     * @param  \App\Models\User  $model
     * @return \Illuminate\View\View
     */
    public function index(User $model)
    {
        return view('users.index');
    }

    public function register(Response $response)
    {
        $this->validate(request(), [
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::create(request(['name', 'username', 'email', 'password']));

        auth()->login($user);
        $token = $user->createToken('tokens')->plainTextToken;

        User::where(['id'=>$user->id])->update(["api_token"=>$token]);
        return [
            'token' => $token,
            'User' => $user
        ];
    }

    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        if (Auth::attempt(['email' => $email, 'password' => $password]))
        {
            $token = Auth::user()->createToken('tokens')->plainTextToken;
            User::where(['id'=>Auth::user()->id])->update(["api_token"=>$token]);
            return [
                'token' => $token,
                'User' => Auth::user()
            ];
        }
        else{
            return response('Authentication failed.',401);
        }
    }


}
