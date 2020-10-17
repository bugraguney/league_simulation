<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    public function home_team_matches(){
        return $this->hasMany('App\Models\Match','home_team_id');
    }
}
