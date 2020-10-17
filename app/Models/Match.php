<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    use HasFactory;

    protected $fillable = [
        'away_team_id',
        'home_team_goal',
        'away_team_goal',
        'play_week',
        'is_played'
    ];

    public function homeTeam(){
        return $this->belongsTo('App\Models\Team','home_team_id');
    }
    public function awayTeam(){
        return $this->belongsTo('App\Models\Team','away_team_id');
    }

}
