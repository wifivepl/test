<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\Score;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function new(Request $request)
    {
      /*  playerName: string // optional, default 'Unnamed player'
        from: number // optional, default 1, greater than 0, greater than or equal `to` - 2
        to: number // optional, default 9, greater than 2, greater than or equal 'from' + 2
        attempts: number // optional, default 3, greater than 0
*/
//return response()->json($request->to - 2, 200);
        $request->validate([
            'from' => 'numeric|min:'.((int)$request->to>0?1:((int)$request->to - 2)),
            'to' => 'numeric|min:'.((int)$request->from>2?3:((int)$request->from + 2)),
            'attempts' => 'gt:0'
        ]);
        //return response()->json('ok', 200);
        if($request->has(['from', 'to'])){
            $random = rand($request->from, $request->to);
        }else{
            $random = rand(1, 9);
        }
        
        $request->request->add(['number' => $random]);
      //  return response()->json($request->all(), 200);
       $game = Game::create($request->all());

       return response()->json(['id' => $game->id ], 200);
    }

    public function guess(Request $request){
        $request->validate([
            'id' => 'exists:games',
            
        ]);
        $game = Game::findOrfail($request->id);
        $scores = Score::where('game_id',$game->id)->count();
        if($scores == $game->attempts){
            return response()->json('No attempts left', 403);
        }

        $start  = new Carbon($game->created_at);
        $end    = new Carbon();

        if($start->diff($end)->format('%i') > 5){
            return response()->json('Time left', 403);
        }


       $won = $game->number == $request->number;
       $status = $won;
       if(!$won){
           $status = $scores+1<$game->attempts?0:2;
       }
       
       $score = $won?Score::calculate($game->from, $game->to, $request->number, $scores + 1):0;

      $newscore = Score::create(array(
           'game_id' => $request->id,
           'number' => $request->number,
           'status' => $status,
           'score' => $score
       ));
      


$id = $newscore->id;
$competitiors = Score::where('score','>', 0)->orderBy('score','desc')->get();
$position = $competitiors->search(function ($person, $key) use ($id) {
    return $person->id == $id;
});

Score::save_scores();

        return response()->json(array(
        'status' => Score::status_name($status),
        'number' => $request->number,
        'score' => $score,
        'place' => $won?$position + 1:false
       ), 200);

       
    }

    public function scores(Request $request){

       $scores = DB::table('scores')
       ->select('score', 'name')
       ->leftJoin('games', 'scores.game_id', '=', 'games.id')
        ->orderBy('score', 'desc')
        ->limit(30)
        ->get();
        return response()->json(['scores' => $scores],200);
    }
    
}
