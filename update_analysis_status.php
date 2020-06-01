<?php
session_start();
if (!isset($_SESSION['login']) or !isset($_POST["aID"]) or !isset($_POST['status'])) {
    header( 'Location: /', true );
}
require_once "../config.php";
$status = $mysqli->real_escape_string($_POST["status"]);
$aID = $mysqli->real_escape_string($_POST["aID"]);
$added_by=$_SESSION["login"];
if($get_analysis_info=$mysqli->query("UPDATE analysis_results SET status='$status' WHERE id='$aID'")) {
    $result=$mysqli->query("INSERT into `log_change_analysis_status` (login, new_value, aid) VALUES ('$added_by', '$status', '$aID')");
    echo("okey");
}else {
    $query_log = log_error_query("update_water_status", "error_while_updating", $_SESSION['login'], "aID:$aID; query: 'UPDATE analysis_results SET status='$status' WHERE id='$aID''");
    $error_log_result = $mysqli->query($query_log);
    echo("<font color='red'>some error</font>");
}
?>