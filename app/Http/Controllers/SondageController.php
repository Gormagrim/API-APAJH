<?php

namespace App\Http\Controllers;

use App\Models\Sondage;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ScoreController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['getMemoryTopFiveScore', 'getLsfTopTen']]);
    }

    public function postBlogArticle(Request $request)
    {
        $this->validate($request, [
            'comment' => 'required|regex:/^[^<>]+$/'
        ], [
            'comment.required' => 'Un article doit obligatoirement avoir un titre.',
            'comment.regex' => 'Merci de saisir correctement le nom de l\'article.'
        ]);
        $input = $request->only('contentTitle', 'contentType');
        try {
            $sondage = new Sondage;
            $sondage->id_content = htmlspecialchars($input['id_content']);
            $sondage->id_users = auth()->user()->id;
            $sondage->comment = htmlspecialchars($input['comment']);
            $sondage->answer_1 = htmlspecialchars($input['answer_1']);
            $sondage->answer_2 = htmlspecialchars($input['answer_2']);
            $sondage->answer_3 = htmlspecialchars($input['answer_3']);
            $sondage->answer_4 = htmlspecialchars($input['answer_4']);
            $sondage->answer_5 = htmlspecialchars($input['answer_5']);
            $sondage->answer_6 = htmlspecialchars($input['answer_6']);
            $sondage->answer_7 = htmlspecialchars($input['answer_7']);
            $sondage->answer_8 = htmlspecialchars($input['answer_8']);
            $sondage->answer_9 = htmlspecialchars($input['answer_9']);
            $sondage->answer_10 = htmlspecialchars($input['answer_10']);
            $sondage->save();
            return response()->json([
                'message' => 'Vos réponses au sondage ont bien été enregistrées.',
                'Contenu' => $sondage
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre article', 500);
        }
    }
}

