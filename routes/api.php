<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/signin', [UserController::class, 'signin']);

Route::post('/register', [UserController::class, 'create']);

Route::get('/verify', [UserController::class, 'verify'])->middleware(EnsureTokenIsValid::class);

Route::get('/bcrypt/{pass}', function (Request $request) {
    $params = $request->all();
    dd($params);
    return response()->json(Hash::make('162867'));
});

Route::post('/forgot-password', function (Request $request) {
    try{
        $request->validate(['email' => 'required|email']);
    }catch(\Exception $error){
        return response()->json($error->getMessage(), 400);
    }

    $status = Password::sendResetLink(
        $request->only('email')
    );
    
    return $status === Password::RESET_LINK_SENT
        ? response()->json('Email de recuperação enviado', 200)
        : response()->json('Erro ao enviar o email de recuperação', 400);
});

Route::post('/reset-password', function (Request $request) {
    try{
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);
    }catch(\Exception $error){
        return response()->json($error->getMessage(), 400);
    }

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->password = Hash::make($password);
 
            $user->save();
 
            //event(new PasswordReset($user));
        }
    );
 
    return $status === Password::PASSWORD_RESET
        ? response()->json('Senha atualizada', 200)
        : response()->json('Não foi possível atualizar a senha', 400);
});