<?php

namespace App\Http\Controllers;

use App\Models\Match;
use App\Models\Team;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index()
    {
        try {

            if (!$this->checkLeagueStart()) {
                $this->leagueStart();
            }
            $league_table = $this->getLeagueTable();
            $last_match_played = $this->getLastMatchPlayed();
            $week = 0;
            if ($last_match_played->count() > 0) {
                $week = $last_match_played->first()->play_week;
            }
            $champion_prediction = $this->getChampionPrediction($league_table, $week);

            return view('home', compact('league_table', 'last_match_played', 'champion_prediction', 'week'));
        } catch (QueryException $queryException) {
            report($queryException);
            abort(500);
        } catch (\Throwable $throwable) {
            report($throwable);
            abort(500);
        }

    }

    /**
     * @param $lang
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeLanguage($lang)
    {
        try {
            Session::put(['locale' => $lang]);
            return redirect()->route('home');
        } catch (\Throwable $throwable) {
            report($throwable);
            abort(500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function playAll()
    {
        try {
            $bulunulanHafta = Match::where('is_played', 1)->max('play_week') + 1;
            if ($bulunulanHafta > 6) {
                return new JsonResponse(['status' => false]);
            }
            for ($x = $bulunulanHafta; $x <= 6; $x++) {
                $response = $this->nextWeek();
            }
            return $response;
        } catch (QueryException $queryException) {
            report($queryException);
            abort(500);
        } catch (\Throwable $throwable) {
            report($throwable);
            abort(500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function nextWeek()
    {
        try {
            $matchesOfTheWeek = Match::with(['homeTeam', 'awayTeam'])->where('is_played', 0)->orderBy('play_week', 'asc')->limit(2)->get();
            foreach ($matchesOfTheWeek as $match) {
                $match->update([
                    'home_team_goal' => rand(0, 5),
                    'away_team_goal' => rand(0, 5),
                    'is_played' => true
                ]);
            }

            $league_table = $this->getLeagueTable();
            $week = $matchesOfTheWeek->first()->play_week;

            $champion_prediction = $this->getChampionPrediction($league_table, $week);

            return new JsonResponse(['status' => true, 'datas' => [
                'league_table' => $league_table,
                'matches_of_the_week' => $matchesOfTheWeek,
                'champion_prediction' => $champion_prediction
            ]]);
        } catch (QueryException $queryException) {
            report($queryException);
            abort(500);
        } catch (\Throwable $throwable) {
            report($throwable);
            abort(500);
        }
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function newLeague()
    {
        try {
            Match::truncate();
            return redirect()->route('home');
        } catch (QueryException $queryException) {
            report($queryException);
            abort(500);
        } catch (\Throwable $throwable) {
            report($throwable);
            abort(500);
        }
    }

    /**
     * @return array
     */
    private function getLeagueTable()
    {
        $league_table = [];
        $teams = Team::all();
        foreach ($teams as $team) {
            $league_table[$team->id] = [
                'team_name' => $team->name,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'gd' => 0,
                'gf' => 0,
                'points' => 0
            ];
        }

        $matches = Match::where('is_played', true)->get();
        foreach ($matches as $match) {
            $league_table[$match->home_team_id]['played'] += 1;
            $league_table[$match->away_team_id]['played'] += 1;
            $league_table[$match->home_team_id]['gf'] += $match->home_team_goal;
            $league_table[$match->away_team_id]['gf'] += $match->away_team_goal;
            if ($match->home_team_goal > $match->away_team_goal) {
                $league_table[$match->home_team_id]['points'] += 3;
                $league_table[$match->home_team_id]['won'] += 1;
                $league_table[$match->away_team_id]['lost'] += 1;
            } elseif ($match->home_team_goal == $match->away_team_goal) {
                $league_table[$match->home_team_id]['points'] += 1;
                $league_table[$match->away_team_id]['points'] += 1;

                $league_table[$match->home_team_id]['drawn'] += 1;
                $league_table[$match->away_team_id]['drawn'] += 1;
            } else {
                $league_table[$match->home_team_id]['lost'] += 1;
                $league_table[$match->away_team_id]['won'] += 1;
                $league_table[$match->away_team_id]['points'] += 3;
            }

            $league_table[$match->home_team_id]['gd'] += $match->home_team_goal - $match->away_team_goal;
            $league_table[$match->away_team_id]['gd'] += $match->away_team_goal - $match->home_team_goal;

        }

        array_multisort(array_column($league_table, 'points'), SORT_DESC,
            array_column($league_table, 'gd'), SORT_DESC, array_column($league_table, 'gf'), SORT_DESC,
            $league_table);


        return $league_table;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getLastMatchPlayed()
    {
        return Match::with(['homeTeam', 'awayTeam'])->where('is_played', true)->orderBy('play_week', 'desc')->limit(2)->get();
    }

    /**
     * @param $league_table
     * @param $week
     * @return array|false
     */
    private function getChampionPrediction($league_table, $week)
    {
        if ($week < 4) {
            return false;
        }
        $leader_points = $league_table[0]['points'];
        $acceptable_difference = (6 - $week) * 3;
        $result = [];
        $totalPoints = 0;
        foreach ($league_table as $key => $value) {
            if ($leader_points - $value['points'] <= $acceptable_difference) {
                $totalPoints += $value['points'];
                $result[$key] = ['name' => $value['team_name'], 'points' => $value['points']];
            } else {
                $result[$key] = ['name' => $value['team_name'], 'points' => 0];
            }
        }

        if ($totalPoints > 0) {
            $rate = 100 / $totalPoints;
        } else {
            $rate = 0;
        }

        foreach ($result as $key => $value) {
            $result[$key]['points'] *= $rate;
        }

        // @Todo: oynatılacak maçlar sorgulanıp ev sahibi takıma artı oran verilecek. Deplasmandaki karşılaştırma sonucu olumlu ise artı oran verilecek...

        return $result;
    }


    /**
     * @return int
     */
    private function checkLeagueStart()
    {
        return Match::count();
    }


    private function leagueStart()
    {
        $teams = Team::all()->shuffle();
        $encounters = [
            [1 => 2, 3 => 4],
            [2 => 3, 4 => 1],
            [1 => 3, 4 => 2],
            [2 => 4, 3 => 1],
            [1 => 4, 3 => 2],
            [4 => 3, 2 => 1]
        ];
        foreach ($encounters as $week => $encounter) {
            foreach ($encounter as $home_team => $away_team) {
                $teams[$home_team - 1]->home_team_matches()->create([
                    'away_team_id' => $teams[$away_team - 1]->id,
                    'play_week' => $week + 1,
                    'is_played' => false
                ]);
            }
        }
    }
}
