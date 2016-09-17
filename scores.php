<?php
$teams = file_get_contents("teams.json");
$teams = json_decode($teams);
?>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="css/bootstrap.css" type="text/css">
<link rel="stylesheet" href="css/scores.css" type="text/css">
<link rel="stylesheet" href="css/jquery-ui.css" type="text/css">
<script src="js/jquery-1.9.1.js"></script>
<script src="js/jquery-ui.js"></script>

<script>
    $(function () {
        $('#date')
            .datepicker({
                buttonImage: 'calendar.png',
                buttonImageOnly: true,
                changeMonth: true,
                changeYear: true,
                showOn: 'both',
                yearRange: '2012:2016',
                dateFormat: 'ddmmyy',
            });
    });
</script>
</head>
<body>
<nav class="navbar navbar-default">
  <a class="navbar-brand" href="https://github.com/danny3b/nba_scores">NBA Scores</a>
  </nav>
<div class="container"><div class="col-md-6 col-md-offset-3">
<form action="<?=$_SERVER['REQUEST_URI'] ?>" method="post">
<?php
$chosen_team = $_POST["team"];
echo '<div class="form-group"><label class="control-label">Choose team</label><select class="form-control" name="team" id="team">';

foreach ($teams as $team) {
    echo '<option value="' . $team->abbreviation . '" ' . ($team->abbreviation == $chosen_team ? "selected" : "") . '>' . $team->teamName . '</option>';
    
    if ($team->abbreviation == $chosen_team) {
        $team_id = $team->teamId;
        $team_name = $team->teamName;
        $team_abbreviation = $team->abbreviation;
    }
}
echo "</select></div>";
?>
<?php
$chosen_date = $_POST["date"];

if (isset($chosen_date)) {
    $date = $chosen_date;
    $date_d = substr($date, 0, 2);
    $date_m = substr($date, 2, 2);
    $date_y = substr($date, 4, 4);
}
else {

    //if date is not defines, show current day
    date_default_timezone_set('America/New_York');
    $date_d = date('d');
    $date_m = date('m');
    $date_y = date('Y');
    $date = date('dmY');
}
?>
<div class="form-group"><label>Choose date</label><input id="date" name="date" value="<?=$chosen_date
?>"></div>
<div class="form-group text-center"><input type="submit" class="btn btn-lg btn-info" value="Submit"></div>
</form>
<?php

//getting desirable JSON formatted file witch scoreboard from nba.com
$curl = 'http://stats.nba.com/stats/scoreboard/?LeagueID=00&gameDate=' . $date_m . '%2F' . $date_d . '%2F' . $date_y . '&DayOffset=0&r=' . rand(0, 99999999999999999);

//changing referer to http://stats.nba.com/scores, otherwise it's showing Access Denied

function file_get_contents_curl($curl) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_REFERER, 'http://stats.nba.com/scores');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $curl);
    $data = curl_exec($ch);
    curl_close($ch);
    
    return $data;
}
$url_final = file_get_contents_curl($curl);
$obj = json_decode($url_final);

//slice of array with scores only
$scoreboard = $obj->resultSets[1]->rowSet;

//this function checks wheter value exists in an array

function in_multidimensional_array($elem, $array, $field) {
    $top = sizeof($array) - 1;
    $bottom = 0;
    
    while ($bottom <= $top) {
        
        if ($array[$bottom][$field] == $elem) 
        return true;
        else 
        if (is_array($array[$bottom][$field])) 
        if (in_multidimensional_array($elem, ($array[$bottom][$field]))) 
        return true;
        $bottom++;
    }
    
    return false;
}

//checking if team played that night
$array = $scoreboard;
if (isset($chosen_date) && !in_multidimensional_array($team_abbreviation, $scoreboard, "4")) {
    echo '<div class="alert alert-danger">Selected team didn&rsquo;t play that night</div>';
}

$rowCount = 0;
if (in_multidimensional_array($team_abbreviation, $array, "4")) {
    foreach ($scoreboard as $rowSet) {
        $result = array_merge($scoreboard[$rowCount], $scoreboard[$rowCount + 1]);
        $rowCount++;
        
        if ($rowCount++ % 2 == 1 && $result[4] == $team_abbreviation || $result[4 + 28] == $team_abbreviation) {
            echo '<h4>Results from day ' . $date_d . '.' . $date_m . '.' . $date_y . ' for ' . $team_name . '</h4>';
            echo '<table class="table"><tr><th>Team</th><th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>' . ($result[11] ? '<th>OT</th>' : '') . ($result[12] ? '<th>OT2</th>' : '') . ($result[13] ? '<th>OT3</th>' : '') . ($result[14] ? '<th>OT4</th>' : '') . ($result[15] ? '<th>OT5</th>' : '') . ($result[16] ? '<th>OT6</th>' : '') . '<th>F</th></tr>';
            echo '<tr><td>' . $result[5] . ' ' . preg_replace($patterns, $replacements, $result[4]) . '</td><td>' . $result[7] . '</td><td>' . $result[8] . '</td><td>' . $result[9] . '</td><td>' . $result[10] . '</td>' . ($result[11] ? '<td>' . $result[11] . '</td>' : '') . ($result[12] ? '<td>' . $result[12] . '</td>' : '') . ($result[13] ? '<td>' . $result[13] . '</td>' : '') . ($result[14] ? '<td>' . $result[14] . '</td>' : '') . ($result[15] ? '<td>' . $result[15] . '</td>' : '') . ($result[16] ? '<td>' . $result[16] . '</td>' : '') . '<td>' . $result[21] . '</td></tr>';
            echo '<tr><td>' . $result[5 + 28] . ' ' . preg_replace($patterns, $replacements, $result[4 + 28]) . '</td><td>' . $result[7 + 28] . '</td><td>' . $result[8 + 28] . '</td><td>' . $result[9 + 28] . '</td><td>' . $result[10 + 28] . '</td>' . ($result[11 + 28] ? '<td>' . $result[11 + 28] . '</td>' : '') . ($result[12 + 28] ? '<td>' . $result[12 + 28] . '</td>' : '') . ($result[13 + 28] ? '<td>' . $result[13 + 28] . '</td>' : '') . ($result[14 + 28] ? '<td>' . $result[14 + 28] . '</td>' : '') . ($result[15 + 28] ? '<td>' . $result[15 + 28] . '</td>' : '') . ($result[16 + 28] ? '<td>' . $result[16 + 28] . '</td>' : '') . '<td>' . $result[21 + 28] . '</td></tr>';
            echo '</table>';
        }
    }
}
?>
</div></div>
</body>
