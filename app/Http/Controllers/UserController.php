<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGroup;
use App\Models\Location;
use App\Models\UserDescription;
use App\Models\UserPhoto;
use App\Models\ContentLike;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['likeCount', 'likeMatch', 'showUserPhoto', 'activateUser', 'mailForPasswordLost', 'passwordLost']]);
    }

    public function countUser()
    {
        try {
            $user = new User;
            return response()->json([
                'message' => 'Nombre d\'utilisateurs',
                'nombre' => $user->count()
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant le comptage des utilisateurs', 500);
        }
    }

    public function countProUser()
    {
        try {
            $user = new User;
            return response()->json([
                'message' => 'Nombre d\'utilisateurs',
                'nombre' => $user->where(function ($q) {
                    $q->where('id_userGroup', '1')
                        ->orWhere('id_userGroup', '2')
                        ->orWhere('id_userGroup', '3')
                        ->orWhere('id_userGroup', '4')
                        ->orWhere('id_userGroup', '5');
                })->count()
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant le comptage des utilisateurs', 500);
        }
    }

    public function countActiveUser()
    {
        try {
            $user = new User;
            return response()->json([
                'message' => 'Nombre d\'utilisateurs',
                'nombre' => $user->where('isActive', 1)->count()
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant le comptage des utilisateurs', 500);
        }
    }

    public function activateUser(Request $request)
    {
        $this->validate($request, [
            'mail' => 'required',
            'validationKey' => 'required'
        ], [
            'mail.required' => 'Votre adresse mail est nécessaire à l\'activation de compte.',
            'validationKey.required' => 'Votre clef de validation est nécessaire à l\'activation de votre compte.'
        ]);

        $input = $request->only('mail', 'validationKey');
        try {
            $user = new User;
            $check = $user->where('mail', $input['mail'])->where('validationKey', $input['validationKey'])->count();
            if ($check == 1) {
                $user->where('mail', $input['mail'])->where('validationKey', $input['validationKey'])->update([
                    'isActive' => 1,
                    'validationKey' => md5(uniqid())
                ]);
                return response()->json([
                    'message' => 'Votre compte à bien été activé',
                    'code' => 200
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Une erreur est survenue durant la\'activation de votre compte.',
                    'code' => 400
                ], 500);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant l\'activation de votre compte', 500);
        }
    }

    public function desactivateUser(Request $request)
    {
        $this->validate($request, [
            'mail' => 'required',
            'validationKey' => 'required'
        ], [
            'mail.required' => 'Votre adresse mail est nécessaire à l\'activation de compte.',
            'validationKey.required' => 'Votre clef de validation est nécessaire à l\'activation de votre compte.'
        ]);

        $input = $request->only('mail', 'validationKey');
        try {
            $user = new User;
            $user->where('mail', $input['mail'])->where('validationKey', $input['validationKey'])->update([
                'isActive' => 0,
                'validationKey' => md5(uniqid())
            ]);
            return response()->json([
                'message' => 'Votre compte à bien été activé'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant l\'activation de votre compte', 500);
        }
    }

    public function sendMail(Request $request)
    {
        $this->validate($request, [
            'mail' => 'required',
            'validationKey' => 'required'
        ], [
            'mail.required' => 'Votre adresse mail est nécessaire à l\'activation de compte.',
            'validationKey.required' => 'Votre clef de validation est nécessaire à l\'activation de votre compte.'
        ]);

        $input = $request->only('mail', 'validationKey');
        try {
            $user = new User;

            $cle = $input['validationKey'];
            $mail = $input['mail'];
            $destinataire = $mail;
            $sujet = "Activation de votre votre compte";
            $entete = "From: contact@apajh.web.fr";

            // Le lien d'activation est composé du login(log) et de la clé(cle)
            $message = 'Bienvenue sur votre site,
                    Pour activer votre compte, veuillez cliquer sur le lien ci dessous
                    ou copier/coller dans votre navigateur internet.
     
                    https://www.apajh.web.jeseb.fr/activation-log=' . urlencode($mail) . '-cle=' . urlencode($cle) . ';
     
     
                    ---------------
                    Ceci est un mail automatique, Merci de ne pas y répondre.';

            mail($destinataire, $sujet, $message, $entete); // Envoi du mail

            return response()->json([
                'message' => 'Votre mail a bien été envoyé'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant l\'activation de votre compte', 500);
        }
    }

    public function passwordModify(request $request)
    {
        $this->validate($request, [
            'current_password' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password'
        ], [
            'password.required' => 'Merci de saisir votre nouveau mot de passe',
            'current_password.required' => 'Merci de saisir votre mot de passe actuel.',
            'password_confirmation.required' => 'Merci de confirmer votre nouveau mot de passe.',
            'password_confirmation.same' => 'Il y a une erreur dans la confirmation de votre mot de passe.',
            'password.min' => 'Votre mot de passe doit contenir minimum 8 caractères.'
        ]);
        $user = new user;
        $checkUser = $user->findOrFail(auth()->user()->id);
        $current = $request->current_password;
        try {

            if (Hash::check($current, $checkUser->password)) {
                if ($request->password == $request->password_confirmation) {
                    if ($request->password != $request->current_password) {
                        $user->where('id', auth()->user()->id)->update([
                            'password' => Hash::make($request->password),
                            'password_modification_date' => Carbon::now()
                        ]);
                        return response()->json([
                            'message' => 'Votre mot de passe à bien été modifié.'
                        ], 202);
                    } else {
                        return response()->json([
                            'message' => 'Votre nouveau mot de passe doit être différent de l\'ancien.'
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'message' => 'Il y a une erreur dans la confirmation de votre mot de passe.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'message' => 'Votre mot de passe actuel n\'est pas correct.'
                ], 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la modification de votre mot de passe.', 500);
        }
    }

    public function passwordLost(request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
            'mail' => 'required',
            'validationKey' => 'required'
        ], [
            'password.required' => 'Merci de saisir votre nouveau mot de passe',
            'password_confirmation.required' => 'Merci de confirmer votre nouveau mot de passe.',
            'password_confirmation.same' => 'Il y a une erreur dans la confirmation de votre mot de passe.',
            'password.min' => 'Votre mot de passe doit contenir minimum 8 caractères.'
        ]);
        $user = new user;
        try {
            $toto = $user->where('mail', $request->mail)->where('validationKey', $request->validationKey)->get();
            if (!empty($toto)) {
                if ($request->password == $request->password_confirmation) {
                    $user->where('mail', $request->mail)->where('validationKey', $request->validationKey)->update([
                        'password' => Hash::make($request->password),
                        'password_modification_date' => Carbon::now()
                    ]);
                    return response()->json([
                        'test' => $toto,
                        'message' => 'Votre mot de passe à bien été modifié.'
                    ], 202);
                } else {
                    return response()->json([
                        'message' => 'Il y a une erreur dans la confirmation de votre mot de passe.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'message' => 'Il y a une erreur dans la saisis de vos informations.'
                ], 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la modification de votre mot de passe.', 500);
        }
    }

    public function mailForPasswordLost(request $request)
    {
        $this->validate($request, [
            'mail' => 'required|email'
        ], [
            'mail.required' => 'Une adresse mail est nécessaire à l\'envoi du formulaire de mot de passe perdu.',
            'mail.email' => 'Merci de saisir une adresse mail valide.',
        ]);
        $input = $request->only('mail');
        $userMail = new User();
        $check = $userMail->where('mail', $input['mail'])->count();
        $cle = $userMail->where('mail', $input['mail'])->get();
        try {
            if ($check == 1) {
                return response()->json([
                    'cle' => $cle,
                    'message' => 'Un mail de confirmation a été envoyé sur l\'adresse mail que vous venez de saisir. Merci de cliquer sur le lien présent dans ce mail.'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Cette adresse mail n\'existe pas sur notre site.'
                ], 404);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue.', 500);
        }
    }

    public function passwordChangeAlert()
    {
        try {
            $creation = strtotime(auth()->user()->created_at);
            $modifiaction = strtotime(auth()->user()->password_modification_date);
            $now = strtotime(Carbon::now());
            if (auth()->user()->password_modification_date == null) {
                $alert = ($now - $creation) / (3600 * 24);
            } else {
                $alert = ($now - $modifiaction) / (3600 * 24);
            }
            if ($alert > 90) {
                return response()->json([
                    'message' => 'Votre mot de passe a plus de 90 jours, vous devriez le modifier.'
                ], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Une erreur est survenue.'
            ], 500);
        }
    }

    public function getUsersList()
    {
        try {
            $allUsers = User::with(['UserGroup', 'UserDescription', 'Location', 'UserPhoto'])->get();
            return response()->json($allUsers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }
    
    public function getUsersListForMessage()
    {
        try {
            $allUsers = User::with(['UserGroup', 'UserDescription', 'Location', 'UserPhoto'])->where('id_userGroup', '<', '7')->get();
            return response()->json($allUsers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getProUsersList()
    {
        try {
            $allUsers = User::with(['UserGroup', 'UserDescription', 'Location', 'UserPhoto'])->where('id_userGroup', 3)->orWhere('id_userGroup', 4)->get();
            return response()->json($allUsers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getOneUser($id)
    {
        try {
            $user = User::with(['UserGroup', 'UserDescription', 'Location', 'UserPhoto'])->where('id', $id)->get();
            return response()->json($user, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('utilisateur non trouvé', 404);
        }
    }

    public function getUserDescription()
    {
        try {
            $user = User::where(['id' => auth()->user()->id])->first();
            $userGroup = UserGroup::find($user->id_userGroup);
            $userDescription = UserDescription::where(['id_users' => auth()->user()->id])->first();
            $location = Location::where('id', auth()->user()->id_location)->first();
            $photo = UserPhoto::where(['id_users' => auth()->user()->id])->first();
            return response()->json([
                'message' => 'détail de l\'utilisateur',
                'user' => $user,
                'user_group' => $userGroup,
                'user_description' => $userDescription,
                'ville' => $location,
                'photo' => $photo
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Utilisateur non trouvé', 404);
        }
    }

    public function deleteUser(Request $request)
    {
        try {
            $user = new User;
            $checkUser = User::findOrFail($request->id);
            if ($checkUser->id == auth()->user()->id || auth()->user()->usergroup->id == 1) {
                $user->where('id', $request->id)->delete();
                return response()->json([
                    'message' => 'L\'utilisateur à bien été supprimé.'
                ], 200);
            } else {
                return response()->json('Vous n\'êtes pas autorisé à réaliser cette action', 401);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Utilisateur non trouvé', 404);
        }
    }

    public function addDescription(Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required|regex:/^([A-Z]{1}[a-zA-ZÀ-ÖØ-öø-ÿ]+)([- ]{1}[A-Z]{1}[a-zA-ZÀ-ÖØ-öø-ÿ]+){0,3}$/',
            'lastname' => 'required|regex:/^([A-Z]{1}[a-zA-ZÀ-ÖØ-öø-ÿ]+)([- ]{1}[A-Z]{1}[a-zA-ZÀ-ÖØ-öø-ÿ]+){0,3}$/',
            'job' => 'required'
        ], [
            'firstname.required' => 'Votre prénom est nécessaire à la création de votre profil.',
            'firstname.regex' => 'Merci de saisir correctement votre prénom.',
            'lastname.required' => 'Votre nom de famille est nécessaire à la création de votre profil.',
            'lastname.regex' => 'Merci de saisir correctement votre nom de famille.',
            'job.required' => 'Votre activité est nécessaire à la création de votre profil.'
        ]);

        $input = $request->only('firstname', 'lastname', 'job', 'id_location');

        try {
            $userDescription = new UserDescription;
            $user = new User;
            $userDescription->firstname = $input['firstname'];
            $userDescription->lastname = $input['lastname'];
            $userDescription->job = $input['job'];
            $userDescription->id_users = auth()->user()->id;
            $userDescription->save();
            $user->where('id', auth()->user()->id)->update([
                'id_location' => $input['id_location']
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été ajoutés',
                'userDescription' => $userDescription
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de vos informations', 500);
        }
    }

    public function updateDescription(Request $request)
    {
        $this->validate($request, [
            'firstname' => 'required|regex:/^([A-Z]{1}[a-zÀ-ÖØ-öø-ÿ]+)([- ]{1}[A-Z]{1}[a-zÀ-ÖØ-öø-ÿ]+){0,3}$/',
            'lastname' => 'required|regex:/^([A-Z]{1}[a-zÀ-ÖØ-öø-ÿ]+)([- ]{1}[A-Z]{1}[a-zÀ-ÖØ-öø-ÿ]+){0,3}$/',
            'job' => 'required'
        ], [
            'firstname.required' => 'Votre prénom est nécessaire à la mise à jour de votre profil.',
            'firstname.regex' => 'Merci de saisir correctement votre prénom.',
            'lastname.required' => 'Votre nom de famille est nécessaire à la mise à jour de votre profil.',
            'lastname.regex' => 'Merci de saisir correctement votre nom de famille.',
            'job.required' => 'Votre activité est nécessaire à la mise à jour de votre profil.'
        ]);

        $input = $request->only('firstname', 'lastname', 'job', 'id_location');

        try {
            $userDescription = new UserDescription;
            $user = new User;
            $userDescription->where('id_users', auth()->user()->id)->update([
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'],
                'job' => $input['job'],
                'id_users' => auth()->user()->id
            ]);
            $user->where('id', auth()->user()->id)->update([
                'id_location' => $input['id_location']
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $userDescription
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la modification de vos informations', 500);
        }
    }

    public function changerUserGroup(Request $request)
    {
        $input = $request->only('id_userGroup', 'id');
        try {
            $user = new User;
            if (auth()->user()->id_userGroup == 1) {
                $user->where('id', $input['id'])->update([
                    'id_userGroup' => $input['id_userGroup']
                ]);
                $message = 'Le groupe de l\'utilisateur à bien été modifié.';
                $code = 300;
            } else if (auth()->user()->id_userGroup == 2) {
                if ($input['id_userGroup'] > 2) {
                    $user->where('id', $input['id'])->update([
                        'id_userGroup' => $input['id_userGroup']
                    ]);
                    $message = 'Le groupe de l\'utilisateur à bien été modifié.';
                    $code = 300;
                } else {
                    $message = 'Vous n\'êtes pas autorisé';
                    $code = 401;
                }
            }
            return response()->json([
                'message' => $message,
                'code' => $code
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la modification de vos informations', 500);
        }
    }

    // METHODES POUR LES PHOTOS DE PROFIL
    public function addUserPhoto(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|image:jpeg,png,jpg,gif,svg|max:1024'
        ], [
            'file.required' => 'Merci de selectionner un fichier pour votre photo de profil.',
            'file.image' => 'Le fichier doit obligatoirement être en .jpeg, .png, .jpg, .gif ou .svg.',
            'file.max' => 'La taille du fichier ne doit pas dépasser 1024 ko.'
        ]);

        try {
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $destination_path = './upload/user/';
                $image = 'U-' . auth()->user()->id . time() . '.' . $image->extension();
                $request->file('file')->move($destination_path, $image);
                $userphoto = new UserPhoto;
                $userphoto->photoLink = '/upload/user/' . $image;
                $userphoto->fileName = $image;
                $userphoto->id_users = auth()->user()->id;
                $userphoto->save();
                return response()->json([
                    'message' => 'Votre photo a bien été ajouté.',
                    'détail' => $userphoto
                ], 201);
            } else {
                return response()->json('Merci de sélectionner un fichier.', 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant l\'ajout de votre photo.', 500);
        }
    }

    public function deleteUserPhoto()
    {
        try {
            $userphoto = new UserPhoto;
            if (File::exists('./upload/user/' . auth()->user()->userphoto->fileName)) {
                File::delete('./upload/user/' . auth()->user()->userphoto->fileName);
            }
            $userphoto->where('id_users', auth()->user()->id)->delete();
            return response()->json([
                'message' => 'Votre photo a bien été supprimée',
                'Contenu' => $userphoto
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Photo non trouvée', 404);
        }
    }

    public function showUserPhoto($fileName)
    {
        try {
            $path = './upload/user/' . $fileName;
            if (!File::exists($path)) {
                abort(404);
            }
            $file = base64_encode(File::get($path));
            $type = File::mimeType($path);
            return response()->json(['file' => $file, 'type' => $type], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la récupération de la video', 500);
        }
    }
    // METHODES POUR LES LIKES
    public function addlike(request $request)
    {
        $this->validate($request, [
            'id_content' => 'required'
        ]);
        try {
            $like = new ContentLike;
            $content = new Content;
            $match = $like->where('id_content', $request->id_content)->where('id_users', auth()->user()->id)->count();
            $contentMatch = $content->where('id', $request->id_content)->count();
            if ($contentMatch > 0) {
                if ($match == 0) {
                    $like->id_content = $request->id_content;
                    $like->id_users = auth()->user()->id;
                    $like->save();
                    return response()->json([
                        'isLike' => $match,
                        'message' => 'Votre like à bien été ajouté à l\'article.'
                    ], 200);
                } else {
                    return response()->json([
                        'isLike' => $match,
                        'message' => 'Vous aimez déjà cet article.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'message' => 'Vous ne pouvez pas aimer du contenu qui n\'existe pas.'
                ], 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre like', 500);
        }
    }

    public function likeMatch(request $request)
    {
        $like = new ContentLike;
        $match = $like->where('id_content', $request->id_content)->where('id_users', auth()->user()->id)->count();
        return response()->json([
            'isLike' => $match
        ], 200);
    }

    public function dislike(request $request)
    {
        try {
            $like = new ContentLike;
            $match = $like->where('id_content', $request->id_content)->where('id_users', auth()->user()->id)->count();
            if ($match == 1) {
                $like->where('id_content', $request->id_content)->where('id_users', auth()->user()->id)->delete();
                return response()->json([
                    'message' => 'Votre like à bien été retiré de l\'article.',
                    'dislike' => $like
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Vous ne pouvez pas ne plus aimer un article que vous n\'aimez pas.'
                ], 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la suppression de votre like.', 500);
        }
    }

      
}
