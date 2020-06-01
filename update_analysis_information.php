<?php
session_start();
if (!isset($_SESSION['login']) or !isset($_POST["aID"]) or !isset($_POST['source'])) {
    header( 'Location: /', true );
}
require_once "../config.php";
$source = $mysqli->real_escape_string($_POST["source"]);
$aID = $mysqli->real_escape_string($_POST["aID"]);
$added_by=$_SESSION["login"];
if($get_analysis_info=$mysqli->query("UPDATE analysis_results SET source='$source' WHERE id='$aID'")) {
    $result=$mysqli->query("INSERT into `log_change_water_source` (login, new_value, aid) VALUES ('$added_by', '$source', '$aID')");
    echo("ok");
}else {
    $query_log = log_error_query("update_water_source", "error_while_updating", $_SESSION['login'], "aID:$aID; query: 'UPDATE analysis_results SET source='$source' WHERE id='$aID''");
    $error_log_result = $mysqli->query($query_log);
    echo("<font color='red'>some fucking error</font>");
}
?>