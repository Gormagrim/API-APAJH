<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Le token est necessaire partout sauf pour register et login
        $this->middleware('auth:api', ['except' => ['login', 'register', 'checkMail', 'checkPassword']]);
    }
    // Méthode pour enregistrer un compte utilisateur
    public function register(Request $request)
    {
        $errors = [
            'mail.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'mail.email' => 'Merci de saisir une adresse mail valide.',
            'mail.unique' => 'Cette adresse mail a déjà été utilisée pour la création d\'un compte sur notre site.',
            'password.required' => 'Un mot de passe est obligatoire pour créer votre compte.',
            'password.min' => 'Votre mot de passe doit contenir minimum 8 caractères.'
        ];
        $this->validate($request, [
            'mail' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'isActive' => 'boolean',
            'inscriptionDate' => 'date',
            'id_userGroup' => 'regex:/^[1-7]$/'
        ], $errors);
        $input = $request->only('mail', 'password');

        try {
            $user = new User;
            $user->mail = $input['mail'];
            $password = $input['password'];
            $user->password = Hash::make($password);
            $user->validationKey = md5(uniqid());
            $user->save();

            return response()->json([
                'message' => 'Vous vous êtes correctement inscit',
                'user' => $user
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre compte', 500);
        }
    }
    // Méthode de connection
    public function login(Request $request)
    {
        $this->validate($request, [
            'mail' => 'required',
            'password' => 'required'
        ], [
            'mail.required' => 'Votre adresse mail est nécessaire à la connection.',
            'mail.email' => 'Merci de saisir une adresse mail valide.',
            'password.required' => 'Un mot de passe est nécessaire à la connection.'
        ]);

        $input = $request->only('mail', 'password');

        if (!$authorized = Auth::attempt($input)) {
            $code = 401;
            $output = [
                'code' => $code,
                'message' => 'Votre adresse mail ou votre mot de passe comporte une erreur.'
            ];
        } else {
            if(!empty(auth()->user()->userphoto->fileName)) {
                $userPhoto = auth()->user()->userphoto->fileName;
            } else {
                $userPhoto = null;
            }
            $token = $this->respondWithToken($authorized);
            $code = 200;
            $output = [
                'code' => $code,
                'message' => 'Vous vous êtes correctement connecté',
                'token' => $token,
                'id' => auth()->user()->id,
                'userPhoto' => $userPhoto,
                'userGroup' => auth()->user()->id_userGroup,
                'isActive' => auth()->user()->isActive
            ];
            $user = new User();
            $user->where('id', auth()->user()->id)->update([
                'last_Login' => Carbon::now()
            ]);
        }
        return response()->json($output, $code);
    }
    // Méthode pour obtenir des information sur soi
    public function me()
    {
        return response()->json(auth()->user());
    }
    // Méthode déconnection
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Vous vous êtes déconnecté']);
    }
    // Méthode pour réinitialiser la durée du token
    public function extendToken()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    public function checkMail(Request $request)
    {
        try {
            $userMail = new User();
            $mail = $userMail->where('mail', $request->mail)->count();
            return response()->json(['message' => $mail]);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la requête.', 500);
        }
    }

    public function checkPassword(Request $request)
    {
        try {
            $userPassword = new user;
            $password = $userPassword->where('mail', $request->mail)->first();
            if (Hash::check($request->password, $password->password)) {
                return response()->json([
                    'message' => 1
                ], 200);
            }else{
                return response()->json([
                   'message' => 'Votre adresse mail ou votre mot de passe comporte une erreur.'
                ], 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la requête.', 500);
        }
    }
}
