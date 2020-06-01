<?php
session_start();
//phpinfo();
require_once ("base_scripts/PasswordHash.php");
$t_hasher = new PasswordHash(8, false);

/*
$entered_pass = 'cegth8';
$hash = $t_hasher->HashPassword($entered_pass);

print 'Hash: ' . $hash . "\n";
die();
*/

if (!isset($_SESSION['user_session'])) {
    require_once("config.php");
    require_once("lang/ua.php");
    function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    if (isset($_POST["login"]) and $_POST["login"] == $mysqli->real_escape_string($_POST['login'])) { //логин не содержит спец.символов
        $login = $mysqli->real_escape_string($_POST['login']);
        //проверяем пароль по той же логике
        //echo("<br>POST[\"password\"]: ".$_POST["password"] < $settings['min_password_length']."<br>settings['min_password_length']:".$settings['min_password_length']);
        if ($_POST["password"] == $mysqli->real_escape_string($_POST['password'])) {
            $password = $mysqli->real_escape_string($_POST['password']);
            $hash_pass = $t_hasher->HashPassword($password, false);
            $query = "SELECT *  FROM `users_list` WHERE `login`='$login'";

            //echo($query);
            $result = $mysqli->query($query);
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            //echo '<pre>';print_r($rows);echo '</pre>';
            if(!isset($rows[0]['password'])){$rows[0]['password']="";}
            $pass_from_DB = $rows[0]['password'];
            //echo $pass_from_DB;

            if ($t_hasher->CheckPassword($password, $pass_from_DB)) {
                //echo("ok");
                foreach ($rows[0] as $key=>$value) {
                    $_SESSION[$key]=$value;
            }
                $company_list="";
            if(isset($_SESSION['company_id']) and $_SESSION['company_id']!=='') {
                $company_list = $_SESSION['company_id'].",".$_SESSION['companies_where_is_admin'];
            }else {
                $company_list = $_SESSION['companies_where_is_admin'];
            }
                if($companies_array = $mysqli->query("SELECT id, name_eng_underscore, name_original FROM company_list WHERE id IN ($company_list)")) {
                    //print_r($companies_array);
                    while ($row = $companies_array->fetch_assoc()) {
                        $_SESSION["visible_companies"][$row["id"]]["name_eng_underscore"] = $row['name_eng_underscore'];
                        $_SESSION["visible_companies"][$row["id"]]["name_original"] = $row['name_original'];
                    }
                }

                $query_text = "INSERT INTO `login_log` (`user_id`, `user_ip`) VALUES (\"" . $rows[0]['id'] . "\",\"" . get_client_ip() . "\")";
                if($result = $mysqli->query($query_text)) {
                    {
                        header("Location: https://kp.ecosoft.ua/analysis_list.php");

                    }
                    echo("ok");
                }
            } else {
                echo("<p style='color:red'>" . $login_text['pass_error'] . "</p>");
            }
        } else {
            echo("<p style='color:red'>" . $login_text['pass_error'] . "</p>");
        }

    } else {
        echo("<p style='color:red'>" . $login_text['login_error'] . "</p>");
    }
}else{
    die("Do not try to hack me or I will call police.");
}
if (isset($_SESSION['login']))

?>