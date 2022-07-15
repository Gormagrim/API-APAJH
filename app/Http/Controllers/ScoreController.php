<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ScoreController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['getMemoryTopFiveScore', 'getLsfTopTen']]);
    }

    public function getMemoryTopFiveScore(Request $request)
    {
        try {
            $allScore = Score::with(['Game', 'User', 'UserDescription'])->where('id_game', 2)->where('difficulty', $request['difficulty'])->orderBy('time')->limit(5)->get();
            return response()->json($allScore, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function postMemoryScore(Request $request)
    {
        $this->validate($request, [
            'score' => 'required',
            'time' => 'required',
            'difficulty' => 'required'
        ], [
            'score.required' => 'Un score est nécessaire.',
            'time.required' => 'Un temps de jeu est nécessaire.',
            'difficulty.required' => 'Un choix de difficulté est nécessaire.'
        ]);
        $input = $request->only('score', 'time', 'difficulty');

        try {
            $gameScore = new Score;
            $gameScore->score = htmlspecialchars($input['score']);
            $gameScore->id_users = auth()->user()->id;
            $gameScore->time = htmlspecialchars($input['time']);
            $gameScore->difficulty = htmlspecialchars($input['difficulty']);
            $gameScore->id_game = 2;
            $gameScore->save();
            return response()->json([
                'message' => 'Votre score a bien été enregistré.',
                'Contenu' => $gameScore
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant l\'envoi de votre score.', 500);
        }
    }

    public function lsfGamePlay(Request $request)
    {
        $this->validate($request, [
            'difficulty' => 'required'
        ]);
        $input = $request->only('difficulty');
        try {
            $play = new Score;
            $play->updateOrInsert([
                'id_users' => auth()->user()->id,
                'id_game' => 1,
                'difficulty' => $input['difficulty']
            ])->where('id_users', auth()->user()->id)->where('id_game', 1)->where('difficulty', $input['difficulty'])->increment('trials', 1);
            return response()->json($play, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant l\'ajout de la vue', 500);
        }
    }

    public function lsfGameVictory(Request $request)
    {
        $this->validate($request, [
            'difficulty' => 'required'
        ]);
        $input = $request->only('difficulty');
        try {
            $play = new Score;
            $play->updateOrInsert([
                'id_users' => auth()->user()->id
            ])->where('id_users', auth()->user()->id)->where('id_game', 1)->where('difficulty', $input['difficulty'])->increment('victory', 1);
            return response()->json($play, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant l\'ajout de la vue', 500);
        }
    }  

    public function getMyLsfScore(Request $request)
    {
        try {
            $myLsfScore = Score::with(['Game'])->where('id_game', 1)->where('difficulty', $request['difficulty'])->where('id_users', auth()->user()->id)->get();
            return response()->json($myLsfScore, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getLsfTopTen(Request $request)
    {
        try {
            $LsfTopTen = Score::with(['Game', 'User', 'UserDescription'])->where('id_game', 1)->where('difficulty', $request['difficulty'])->limit(10)->orderBy('victory', 'DESC')->get();
            return response()->json($LsfTopTen, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

}
