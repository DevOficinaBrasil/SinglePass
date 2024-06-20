<?php


namespace App\Http\Controllers\Repository;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Database\QueryException;

class UserRepository implements UserRepositoryInterface
{
    public function signin(Request $data): array|Exception
    {
        $credentials = $data->only('email', 'password');

        if(! $token = JWTAuth::attempt($credentials)){
            throw new Exception('Credenciais de login inválidas.');
        }

        JWTAuth::setToken($token);
        
        $payload = JWTAuth::getPayload();

        return [$token, $payload->get('user_id')];
    }

    public function store(Request $data): array|Exception
    {
        try{
            $userModel = User::create([
                'name'     => $data->name,
                'email'    => $data->email,
                'role'     => $data->role,
                'password' => Hash::make($data->password),
            ]);
        }catch(QueryException $error){
            throw new Exception($error->getMessage());
        }

        $token = JWTAuth::fromUser($userModel);
        
        return [$token, $userModel->id];
    }
}