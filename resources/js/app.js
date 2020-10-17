require('jquery/src/jquery');

var token = $('meta[name=csrf-token]').attr('content');
var language = $('html').attr('lang');
console.log(language);

if (language == 'tr') {
    var week_match_results = 'Haftanın Maç Sonuçları';
    var champion_prediction_message = "Sistemin şampiyon tahmini yapabilmesi için en az 4 hafta smilasyon oynatılmalıdır.";
} else {
    var week_match_results = 'Week Match Results';
    var champion_prediction_message = 'Simulation must be played for at least 4 weeks in order for the system to predict champions.';
}

function update_league_table(league_table) {
    var league_table_content = '';
    $(league_table).each(function (key, value) {
        league_table_content += '<tr>' +
            '<td>' + value.team_name + '</td>' +
            '<td class="text-center">' + value.played + '</td>' +
            '<td class="text-center">' + value.won + '</td>' +
            '<td class="text-center">' + value.drawn + '</td>' +
            '<td class="text-center">' + value.lost + '</td>' +
            '<td class="text-center">' + value.gd + '</td>' +
            '<td class="text-center">' + value.points + '</td>' +
            '</tr>';

    });
    $('#league_table').html(league_table_content);
}

function update_match_results(matches_of_the_week) {
    var matches_of_the_week_content = '<table class="table table-striped table-bordered table-hover m-0">' +
        '<thead>' +
        '<tr>' +
        '<th colspan="3">' +
        matches_of_the_week[0].play_week + '. ' + week_match_results
    '</th></tr>' +
    '</thead>';

    $(matches_of_the_week).each(function (key, value) {
        matches_of_the_week_content += '<tr>' +
            '<td>' + value.home_team.name + '</td>' +
            '<td>' + value.home_team_goal + ' - ' + value.away_team_goal + '</td>' +
            '<td>' + value.away_team.name + '</td>' +
            '</tr>';
    });

    matches_of_the_week_content += '</table>';

    $('#match_results').html(matches_of_the_week_content);
}

function update_champion_prediction(champion_prediction, week) {
    if (week >= 4 && week < 6) {
        var champion_prediction_content = '<table class="table table-striped table-bordered table-hover m-0">';
        $(champion_prediction).each(function (key, value) {
            champion_prediction_content += '<tr>' +
                '<td>' + value.name + '</td>' +
                '<td class="text-center"> %' + Math.round(value.points) + '</td>' +
                '</tr>';
        });
        champion_prediction_content += '</table>';
        $('#champion_prediction').html(champion_prediction_content);
    } else {
        $('#champion_prediction').html('<div class="p-3">' + champion_prediction_message + '</div>');
    }

}

$("#next_week").click(function () {
    $('#next_week').addClass('disabled');
    $('#play_all').addClass('disabled');
    $.ajax({
        method: "POST",
        url: "/next_week",
        data: {_token: token}
    })
        .done(function (response) {
            if (response.datas.matches_of_the_week[0].play_week != 6) {
                $('#next_week').removeClass('disabled');
                $('#play_all').removeClass('disabled');
            }
            update_league_table(response.datas.league_table);
            update_match_results(response.datas.matches_of_the_week);
            update_champion_prediction(response.datas.champion_prediction, response.datas.matches_of_the_week[0]['play_week']);
        })
        .fail(function () {
            alert('error');
            $('#play_all').removeClass('disabled');
            $('#next_week').removeClass('disabled');
        });
});

$("#play_all").click(function () {
    $('#next_week').addClass('disabled');
    $('#play_all').addClass('disabled');
    $.ajax({
        method: "POST",
        url: "/play_all",
        data: {_token: token}
    })
        .done(function (response) {
            if (response.datas.matches_of_the_week[0].play_week != 6) {
                $('#next_week').removeClass('disabled');
                $('#play_all').removeClass('disabled');
            }
            update_league_table(response.datas.league_table);
            update_match_results(response.datas.matches_of_the_week);
            update_champion_prediction(response.datas.champion_prediction);
        })
        .fail(function () {
            alert('error');
            $('#next_week').removeClass('disabled');
            $('#play_all').removeClass('disabled');
        });
});
