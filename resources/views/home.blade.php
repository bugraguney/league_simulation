@extends('layouts.app')

@section('content')
    <div class="row mt-3">
        <div class="col-xs-12">
            <a href="{{ route('change_language',['lang'=>'tr']) }}" class="btn btn-dark @if(app()->getLocale()=='tr') disabled @endif ">TR</a>
            <a href="{{ route('change_language',['lang'=>'en']) }}" class="btn btn-dark @if(app()->getLocale()=='en') disabled @endif ">EN</a>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col">
            <form action="{{ route('new_league') }}" method="post">
                @csrf
                <input type="submit" value="@lang('New League')" class="btn btn-danger">
            </form>
        </div>
        <div class="col text-right">
            <a href="#" id="next_week" class="btn btn-success @if($last_match_played->count() && $last_match_played->first()->play_week == 6) disabled @endif">@lang('Next Week')</a>
            <a href="#" id="play_all" class="btn btn-primary @if($last_match_played->count() && $last_match_played->first()->play_week == 6) disabled @endif">@lang('Play All')</a>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-xs-12 col-lg-6 mb-4">
            <div class="card">
                <h5 class="card-header">@lang('League Table')</h5>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered table-hover m-0">
                        <thead>
                        <tr>
                            <th>@lang('Team')</th>
                            <th class="text-center">@lang('Played')</th>
                            <th class="text-center">@lang('Won')</th>
                            <th class="text-center">@lang('Drawn')</th>
                            <th class="text-center">@lang('Lost')</th>
                            <th class="text-center">@lang('GD')</th>
                            <th class="text-center">@lang('Points')</th>
                        </tr>
                        </thead>
                        <tbody id="league_table">
                        @foreach($league_table as $values)
                            <tr>
                                <td>{{ $values['team_name'] }}</td>
                                <td class="text-center">{{ $values['played'] }}</td>
                                <td class="text-center">{{ $values['won'] }}</td>
                                <td class="text-center">{{ $values['drawn'] }}</td>
                                <td class="text-center">{{ $values['lost'] }}</td>
                                <td class="text-center">{{ $values['gd'] }}</td>
                                <td class="text-center">{{ $values['points'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-lg-6 mb-4">
            <div class="card">
                <h5 class="card-header">@lang('Match Results')</h5>
                <div class="card-body p-0" id="match_results">
                    @if($last_match_played->count() == 0)
                        <div class="p-3">
                            @lang('No match has been played yet')
                        </div>
                    @else
                        <table class="table table-striped table-bordered table-hover m-0">
                            <thead>
                            <tr>
                                <th colspan="3">{{ $last_match_played->first()->play_week }}. @lang('Week Match Results')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($last_match_played as $match)
                                <tr>
                                    <td>{{ $match->homeTeam->name }}</td>
                                    <td>{{ $match->home_team_goal }} - {{ $match->away_team_goal }}</td>
                                    <td>{{ $match->awayTeam->name }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    @endempty
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <h5 class="card-header">@lang('Champion Prediction')</h5>
                <div class="card-body p-0" id="champion_prediction">
                    @if($week >= 4 and $week < 6)
                        <table class="table table-bordered table-hover table-striped m-0">
                            @foreach($champion_prediction as $prediction)
                                <tr>
                                    <td>{{ $prediction['name'] }}</td>
                                    <td class="text-center">%{{ round($prediction['points']) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <div class="p-3">
                            @lang('Simulation must be played for at least 4 weeks in order for the system to predict champions.')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
