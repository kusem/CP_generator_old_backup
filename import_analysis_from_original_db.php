<?php


session_start();
$sql_old_host = '89.184.79.52';
$sql_old_db = 'voda_org';
$sql_old_user = '';  //удалил для git
$sql_old_userpass = ''; // удалил git
$mysqli_old_db = new mysqli($sql_old_host, $sql_old_user, $sql_old_userpass, $sql_old_db); //подключаемся к старой БД для проверки и копирования анализов
require_once ("PasswordHash.php");
$t_hasher = new PasswordHash(8, false);

$login_array = array();

if (mysqli_connect_errno()) {
    printf("Подключение к серверу MySQL #1 невозможно. Код ошибки: %s\n", mysqli_connect_error());
    exit;
} else {
    //printf("old db connected ");
}

require_once("../config.php");
if (mysqli_connect_errno()) {
    printf("Подключение к серверу MySQL #2 невозможно. Код ошибки: %s\n", mysqli_connect_error());
    exit;
} else {
    //printf(" <br> new db connected");
}


//получаем АйДи последнего анализа в базе
//for ($i = 0; $i < 10; $i++) { //для заполнения базы
$query_last_analysis_in_base = "SELECT `original_analysis_id` FROM `analysis_results` WHERE `id`=(SELECT MAX(id) FROM `analysis_results`)";
$last_analysis_ID_query_response = $mysqli->query($query_last_analysis_in_base);
if ($last_analysis_ID_query_response->num_rows == 0) {
    $last_id = 0;
    die();
}
while ($row = $last_analysis_ID_query_response->fetch_assoc()) {
    $last_id = $row["original_analysis_id"];
}
//printf("<br> original_analysis_id = $last_id <br>");





//$temp_result=$mysqli_old_db->query("SELECT * FROM `analysis` WHERE `protocol_number`='060818-19'");
//while ($row = $temp_result->fetch_assoc()) {print_r($row);}


//получаем непосредственно анализы воды
$query = "SELECT `analysis_id`, `protocol_number`, `login`, `dates_analysis`, `source_water`, `TS_ID` FROM `analysis` WHERE `analysis_id`>'$last_id' ORDER BY `analysis_id` ASC LIMIT 1000";
if (!$result = $mysqli_old_db->query($query)) {
    printf("pizdeTS");
    die();
}

$analysis_rows = array();
$analysis_id_list = array();
$analysis_full_data = array();
//$bot_notification = array();
//print_r($result);

while ($row = $result->fetch_assoc()) {
    $analysis_rows[] = $row;
    $login_array[] = $row["login"]; //для дальнейшего поиска информации по пользователям, упоминающимся в анализах
    //echo("got new analysis!<br>");
    //print_r(strpos($row["source_water"], ""));
    if (strpos($row["source_water"], "скважина") !== false) {
        $source = "well";
    } elseif (strpos($row["source_water"], "водопровод") !== false) {
        $source = "aqueduct";
    } elseif (strpos($row["source_water"], "колодец") !== false) {
        $source = "well_room";
    } elseif (strpos($row["source_water"], "бювет") !== false) {
        $source = "well_tube";
    } else {
        $source = "";
    }
    //print_r($login_array);echo("<pre>");print_r($row);echo("</pre>");

    $analysis_full_data[$row["login"]][$row["analysis_id"]] = array(
        "protocol_number" => $mysqli->real_escape_string($row["protocol_number"]),
        "login" => $row["login"], //чтоб не морочиться с наследствиями ключ->значение
        "dates_analysis" => $row["dates_analysis"],
        "source_water" => $row["source_water"],
        "source" => $source,
        "TS_ID" => $row["TS_ID"],
    );
    $analysis_id_list[] = $mysqli->real_escape_string($row["analysis_id"]);

//print_r($analysis_id_list);

    /*
    if($bot_notification[$row["login"]]){
        $bot_notification[$row["login"]][]=$row['protocol_number'];
    }else{
        $bot_notification[]=$row["login"];
        $bot_notification[$row["login"]]=$row['protocol_number'];
    }*/
}
//echo("<pre>");print_r($analysis_full_data);echo("</pre>");
$result->close();
//echo("<hr>analysis_id_array <br><pre>");print_r($analysis_full_data);echo("<br>");

//получаем интересующие нас данные о владельцах анализов воды
if (!empty($login_array)) {
    $query = "SELECT `login`, `uid`, `password`, `email`, `name` FROM `pm_users` WHERE `login` in('" . implode('\',\'', array_unique($login_array)) . "')";
    $result = $mysqli_old_db->query($query);
    while ($row = $result->fetch_assoc()) {
        $rows_users[] = $row;                           //ДОБАВИТЬ АВТОМАТИЧЕСКОЕ ДОБАВЛЕНИЕ ПОЛЬЗОВАТЕЛЯ
        $login_to_uid[$row["login"]]["uid"] = $row["uid"];
        $login_to_uid[$row["login"]]["password"] = $t_hasher->HashPassword($row['password'], false);
    }
    $result->close();
}
//echo("<pre>");print_r($rows_users);echo("</pre>");




$query_get_analysis = "SELECT `analysis_id`,`value1`, `field_id` FROM `analysis_values` WHERE `analysis_id` IN (" . implode(',', $analysis_id_list) . ")";
if (!$result = $mysqli_old_db->query($query_get_analysis)) {
    //echo($query_get_analysis);
    //printf("Ошибка: %s\n", $mysqli_old_db->error);
    die();
}
while ($row = $result->fetch_assoc()) {
    //print_r($row);
    //echo("<br>");
    $analysis_values[$row["analysis_id"]][$row["field_id"]] = $row["value1"];
}
//echo("<hr>analysis_values <br><pre>");print_r($analysis_values);echo("<br>".$query."<br>");

//генерируем строку значений для добавления последних анализов
$final_analysis_template = array();
//echo("tyt <br>");
foreach ($analysis_full_data as $user_login => $rest_array_AID) { //проходимся по массиву со значениями полученных анализов
    foreach ($rest_array_AID as $analysis_number => $analysis_additional_data) {
        $values_checked = "";
        if (isset($analysis_values[$analysis_number]["166"])) {
            $values_checked .= '","' . $analysis_values[$analysis_number]["166"];
        } else {
            $values_checked .= '","';
        }
        if (isset($analysis_values[$analysis_number]["168"])) {
            $values_checked .= '","' . $analysis_values[$analysis_number]["168"];
        } else {
            $values_checked .= '","';
        }
        if (isset($analysis_values[$analysis_number]["169"])) {
            $values_checked .= '","' . $analysis_values[$analysis_number]["169"];
        } else {
            $values_checked .= '","';
        }
        if (isset($analysis_values[$analysis_number]["172"])) {
            $values_checked .= '","' . $analysis_values[$analysis_number]["172"];
        } else {
            $values_checked .= '","';
        }
        if (isset($analysis_values[$analysis_number]["176"])) {
            $values_checked .= '","' . $analysis_values[$analysis_number]["176"];
        } else {
            $values_checked .= '","';
        }
        if (isset($analysis_values[$analysis_number]["183"])) {
            $values_checked .= '","' . $analysis_values[$analysis_number]["183"];
        } else {
            $values_checked .= '","';
        }
        if (isset($analysis_values[$analysis_number]["187"])) {
            $values_checked .= '","' . $analysis_values[$analysis_number]["187"];
        } else {
            $values_checked .= '","';
        }


        $final_analysis_template [] = '(' . $analysis_number . ',"' . $analysis_additional_data["dates_analysis"] . '","' . $analysis_additional_data["protocol_number"] . '","' . $user_login . '","' . $analysis_additional_data["login"] . '","' . addslashes($analysis_additional_data["source_water"]) . $values_checked . '","analysis_ready","' . $analysis_additional_data["source"] . '", "' . $analysis_additional_data["TS_ID"] . '")';
    };

}
//echo("<pre>");print_r($final_analysis_template);echo("</pre>ddd");

$query_insert_new_analysis = "INSERT  INTO `analysis_results` (`original_analysis_id`,`analysis_date`, `terrasoft_id`, `owner_id`, `owner_login`, `description`, `color`, 
`turbidity`, `TDS`, `hardness`, `nitrates`, `Fe`,  `smell`,`status`, `source`, `TS_ID`)
VALUES " . implode(",", $final_analysis_template) . " ON DUPLICATE KEY UPDATE owner_id=owner_id;";
//echo("<br>" . $query_insert_new_analysis . "<br>");

//echo("<pre>");print_r($login_to_uid);echo("</pre>");

if (!$do_the_final_query = $mysqli->query($query_insert_new_analysis)) {
    echo($query_insert_new_analysis);
    printf("Ошибка: %s\n", $mysqli->error);
    die();
}


require_once ("copy_users.php");

require ("create_viber_notifications.php");
//}

//}

//echo($query_insert_new_analysis);
//$do_the_final_query->close();

//echo("<br><hr><br>");print_r($rows_users);echo("</pre>");

