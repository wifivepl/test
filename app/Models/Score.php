<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class Score extends Model
{
    use HasFactory;
    protected $fillable = ['game_id', 'number', 'status', 'score'];

    public static function calculate(int $from, int $to, int $number){
        $range = $to - $from;
        $match = 0;
        $probability = 1/$range;
        return (100 / $probability);
    }
    public static function status_name($status){
        switch ($status) {
            case 0:
               return 'pending';
                break;
            case 1:
                return 'won';
                 break;
            default:
               return 'lost';
                break;
        }
    }
    public static function save_scores(){
        $text = '';
        $scores = DB::table('scores')
       ->select('score', 'name')
       ->leftJoin('games', 'scores.game_id', '=', 'games.id')
        ->orderBy('score', 'desc')
        ->limit(30)
        ->get();

        foreach ($scores as $key => $value) {
            $text .= $key+1;
            $text .= "\t";
            $text .= $value->name;
            $text .= "\t";
            $text .= $value->score;
            $text .= "\n";
        }

        Storage::put('scores.txt', $text);
    }
}
