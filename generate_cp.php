<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /', true);
} else {
    if (isset($_POST)) {
        function DieDieDieMyDarling($error_text)
        { //выводит ошибку, убивает скрипт.
            die("<p style='color:red'>" . $error_text . "</p>");
        }

        require_once "../config.php";
        if (isset($_SESSION['language'])) {
            require_once "../lang/" . $_SESSION['language'] . ".php";
        } else if (isset($_GET["language"])) {
            require_once "../lang/" . $_GET['language'] . ".php";
        } else {
            require_once "../lang/ua.php";
        }


        $no_cp = 0;
        //$owner_id = $user_id;
        //if (isset($_POST["full_filename"])){$full_filename = $_POST["full_filename"];} else {echo("error full_filename");};

            if (isset($_POST["company_id"])) {
                $company_id = $_POST["company_id"];
            } else {
                DieDieDieMyDarling($scheme_generator["error_no_company_id"] . "error_no_needed_data-company_id");
            };
        if (isset($_POST["consumption"])) {
            $consumption = $_POST["consumption"];
        } else {
            DieDieDieMyDarling($scheme_generator["error_no_consumption"] . "error_no_needed_data-consumption");
        };
        if (isset($_POST["turbidity"])) {
            $turbidity = $_POST["turbidity"];
        } else {
            DieDieDieMyDarling($scheme_generator["error_no_needed_data"] . "error_no_needed_data-turbidity");
        };
        if (isset($_POST["smell"])) {
            $smell = $_POST["smell"];
        } else {
            $smell = 0;
        };
        if (isset($_POST["Fe"])) {
            $Fe = $_POST["Fe"];
        } else {
            DieDieDieMyDarling($scheme_generator["error_no_needed_data"] . "error_no_needed_data-Fe");
        };
        if (isset($_POST["hardness"])) {
            $hardness = $_POST["hardness"];
        } else {
            DieDieDieMyDarling($scheme_generator["error_no_needed_data"] . "error_no_needed_data-");
        };
        if (isset($_POST["nitrates"])) {
            $nitrates = $_POST["nitrates"];
        } else {
            DieDieDieMyDarling($scheme_generator["error_no_needed_data"] . "error_no_needed_data-");
        };
        if (isset($_POST["TDS"])) {
            $TDS = $_POST["TDS"];
        } else {
            DieDieDieMyDarling($scheme_generator["error_no_needed_data"] . "error_no_needed_data-TDS");
        };
        if (isset($_POST["color"])) {
            $color = $_POST["color"];
        } else {
            DieDieDieMyDarling($scheme_generator["error_no_needed_data"] . "error_no_needed_data-color");
        };
        if (isset($_POST["source"]) and ($_POST["source"] != "water_source_not_set")) {
            $source = $_POST["source"];
        } else {
            DieDieDieMyDarling($scheme_generator["error_no_needed_data"] . "error_no_needed_data-source");
        };
        if (isset($_POST["analysis_filepath"])) {
            $analysis_filepath = $_POST["analysis_filepath"];
        } else {
            DieDieDieMyDarling("error error_crytical_error" . "error_no_needed_data-analysis_filepath");
        };
        if (isset($_POST["terrasoft_id"])) {
            $terrasoft_id = $_POST["terrasoft_id"];
        } else {
            DieDieDieMyDarling($scheme_generator["error_crytical_error"] . "error_no_needed_data-terrasoft_id");
        };
        //echo("<pre>");print_r($_POST);echo("</pre>");

        $ballon_text = "";
        $error = 0;
        $water_seems_like_being_good=0;
        $report = "<b>1. Механічний фільтр</b> </br> &nbsp;&nbsp;";
        if ($Fe < 0.2 and $source=="aqueduct" ) {
            if ($consumption > 1.5) {
                $report.="Ecosoft FM BB20 із картриджем зі спіненого поліпропілену.";
                $scheme["mechanics"] = "bb20";
            } else {
                $report.="Honeywell FK06 3/4 із вбудованим редуктором тиску.";
                $scheme["mechanics"] = "HW";
            }
        }else {
            $report.="Ecosoft FM BB20 із картриджем зі спіненого поліпропілену.";
            $scheme["mechanics"] = "bb20";
        }



        if($turbidity<0.58){$water_seems_like_being_good++;}
        $report .= "<br><b>2. Реагентна очистка</b>";
        if ($hardness < $CP_generator_settings['min_hardness_to_generate_cp'] and $Fe < $CP_generator_settings['min_fe_to_generate_cp']) {
            $report .= "<br>&nbsp;&nbsp;Вода для технічних потреб - не потребує реагентної водопідготовки";
            $no_cp = 1;
            if ($hardness > 0.5) {
                $report .= "<br>&nbsp;&nbsp;Перед бойлерами, котлами, пральними машинами необхідно встановити засоби для попередження утворення накипу (Ecozon).";
            }
        } elseif ($hardness >= $CP_generator_settings['min_hardness_to_generate_cp'] or $Fe >= $CP_generator_settings['min_fe_to_generate_cp']) { //настройки с config.php
            $query_reagent_select =
                "SELECT * FROM `filter_materials_characteristics` 
WHERE `max_input_hardness_1`>=" . str_replace(",", ".", $hardness) . " 
        AND `max_input_iron`>=" . str_replace(",", ".", $Fe) . " 
        AND `max_input_ppm`>".str_replace(",", ".", $TDS)." 
        AND `max_input_color`>".str_replace(",", ".", $color)." 
        ORDER BY price";
            //echo ($query_reagent_select);
            $result = $mysqli->query($query_reagent_select);
            if ($result) {
                $row = $result->fetch_assoc();                         //выбираем самый дешевый
                $reagent_name = $row['name'];

                // БЕРЁМ МАКСИМАЛЬНУЮ СКОРОСТЬ ФИЛЬТРАЦИИ ДЛЯ ДАННОЙ ЗАГРУЗКИ
                $query_maxspeed_select = "SELECT `max_filtration_speed`, `code` FROM `filter_materials` WHERE `name`=\"" . $row['name'] . "\"";
                $result = $mysqli->query($query_maxspeed_select);
                $row = $result->fetch_assoc();
                $max_filtration_speed = $row["max_filtration_speed"];
                $scheme['filter_reagent_type'] = $row['code']; //код Ecosoft для фильтрующего материала
                $reagent_code = $row['code'];

                // ВЫБИРАЕМ БАЛЛОН
                $consumption_old = $consumption; //берём небольшой запас производительности - например, для стиралки
                //echo($reagent_code);
                if ($consumption < 2.40) {
                    if ($reagent_code == "fk") {
                        $consumption = $consumption + 0.15; //берём небольшой запас производительности - например, для стиралки
                        //echo($consumption);
                    } else {
                        $consumption = $consumption + 0.05; //берём небольшой запас производительности - например, для стиралки
                        }
                }else {
                    $consumption = $consumption + 0.05;
                }
                //echo($consumption);

                $bb_carbon = 0;
                if ($source == "well" OR $source == "well_tube" OR $source == "well_room" OR $consumption >= 1.9) {
                    $query_balloon_select = "SELECT diameter,height FROM `ballons` WHERE `type`=\"ballon\" AND `cross_section`*$max_filtration_speed>=" . $consumption;
                    $scheme["mechanics"] = "bb20";
                } else {
                    $query_balloon_select = "SELECT diameter,height FROM `ballons` WHERE `type`=\"cab\" AND `cross_section`*$max_filtration_speed>=" . $consumption;
                    $bb_carbon = 1;
                }
                //echo($query_balloon_select);
                $result = $mysqli->query($query_balloon_select);
                if ($result) {
                    $row = $result->fetch_assoc();
                    $report .= "<br>&nbsp; &nbsp;Установка " . mb_strtoupper($reagent_code) . " " . $row["diameter"] . 'x' . $row["height"] . " (завантаження " . $reagent_name . ")";


                    $scheme['filter_reagent_size'] = $row["diameter"] . $row["height"];
                    $reagent_dia = $row["diameter"];
                    $reagent_hig = $row["height"];
                } else {
                    $query_log = log_error_query("generate_cp", "some_shit_has_happened", $_SESSION['login'], "TS_ID:$terrasoft_id;error_no_needed_data-query_balloon_select");
                    $mysqli->query($query_log);
                    DieDieDieMyDarling($scheme_generator["error_ask_ts_big_ballon"]);
                }
            } else {
                $query_log = log_error_query("generate_cp", "cant_find_reagent_matherial", $_SESSION['login'], "TS_ID:$terrasoft_id ;query:$query_reagent_select");
                $mysqli->query($query_log);
                DieDieDieMyDarling($scheme_generator["error_ask_ts_big_ballon"]);
            }
        } else {
            $query_log = log_error_query("generate_cp", "no_needed_reagent_select", $_SESSION['login'], "TS_ID:$terrasoft_id ;some shit is going on, dunno WTF");
            $mysqli->query($query_log);
            DieDieDieMyDarling($scheme_generator["error_ask_ts_big_ballon"]);
        } //."error_no_needed_data-"
        $report .= "<br><b>3. Сорбційна очистка</b> <br>";

        if ($source != "") {
            if ($source == "well" OR $source == "well_tube" or $source == "well_room") {
                $query_reagent_select = "SELECT `name`,`code` FROM `filter_materials` WHERE `type`  like '%H2S%' ";
                $scheme['filter_carbon_type_url'] = "fpc";
            } else {
                $query_reagent_select = "SELECT `name`,`code` FROM `filter_materials` WHERE `type`  like '%Cl%' ";
                $scheme['filter_carbon_type_url'] = "fpa";
            }
            //echo("<br>query_reagent_select: ".$query_reagent_select."<br>");
            $result = $mysqli->query($query_reagent_select);
            //echo("<pre>");print_r($result);echo("</pre>");

            if ($result) {
                $row = $result->fetch_assoc();
                $result_matherials = $row["name"] . "<br>";
                //echo("result_matherials:$result_matherials");
                $reagent_name = $row['name'];
                //echo("reagent_name:$reagent_name");
                /*$result = $mysqli->query($query_maxspeed_select);
                echo("query_maxspeed_select:".$query_maxspeed_select);
                $row = $result->fetch_assoc();
                */
                $scheme['filter_carbon_type'] = $row['code'];
                //echo("<pre>");print_r($scheme);echo("</pre>");

                if ($no_cp == 0 AND $bb_carbon == 0) {
                    $query_balloon_select = "SELECT * FROM `ballons` WHERE `type`=\"ballon\" AND `cross_section`*$max_filtration_speed>" . $consumption;
                    $result = $mysqli->query($query_balloon_select);
                    $row = $result->fetch_assoc();
                    if ($row["diameter"]) {
                        $ballon_text = '&nbsp; &nbsp;Установка = ' . $row["diameter"] . 'x' . $row["height"];
                        $report .= $ballon_text;
                    } else { //баллон размером больше, чем 1665
                        $query_log = log_error_query("generate_cp", "error_ask_ts_big_ballon", $_SESSION['login'], "big_ballon; consumption: $consumption_old");
                        $mysqli->query($query_log);
                        DieDieDieMyDarling($scheme_generator["error_ask_ts_big_ballon"]);
                    }
                } elseif ($no_cp == 0 AND $bb_carbon == 1) {
                    //$scheme["carbon_needed"]="";
                    $report .= "Картриджний вугільний фільтр покращить органолептичні показники води.";
                    if($source == "well" OR $source == "well_tube" or $source == "well_room"){
                        $scheme['filter_carbon_type'] = "2bb20-CENT";
                        }else{
                        //if($consumption<0.8){
                        //    $scheme['filter_carbon_type'] = "bb20-KUDH";
                        //}else{
                            $scheme['filter_carbon_type'] = "2bb20-KUDH";
                        //}
                    }
                } else {
                    $report .= "Для підбору сорбційної очистки зверніться до інженера.";
                }
            } else {
                DieDieDieMyDarling($scheme_generator["error_crytical_error"]);
            }
            if ($smell < 2) {
                $report .= "<br><font color='#32cd32'>&nbsp;&nbsp; Зверніть увагу - запах у межах норми - немає необхідності у встановленні вугілля</font>";
                $scheme['carbon_needed'] = "-opt";
            } else {
                $scheme['carbon_needed'] = "";
            }
        } else {
            die("Не вказано джерело вихідної води");
        }
        $report .= "<br><b>4. Зворотній осмос</b>";
        if ($nitrates >= 50) {
            if ($nitrates > 80 and $nitrates < 140) {
                $report .= "<br>&nbsp;&nbsp;Оскільки в воді наявні нітрати у великій кількості, то необхідно встановити побутову систему зворотнього осмосу з помпою.";
                $scheme["osmosis"] = "pure-pump";
            } elseif ($nitrates <= 80 and $nitrates > 49) {
                $report .= "<br>&nbsp;&nbsp;Оскільки в воді наявні нітрати, то необхідно встановити побутову систему зворотнього осмосу";
                $scheme["osmosis"] = "pure";
            } elseif ($nitrates >= 140) {
                $report .= "<br>&nbsp;&nbsp;Вирішення проблеми нітратів за такої концентрації можливо тільки на промисловій системі зворотнього осмосу. Для підготування схеми отримання питної води - зверніться до інженера. 
                                                <br><br>&nbsp;&nbsp;<font color='red'><b>Запропонована схема готує воду технічної якості - осмос на питній воді покращить якість питної води, але не забезпечить відповідність нормам. Вживання такої води може нанести шкоду здоров'ю</b></font>";
                $scheme["osmosis"] = "pure-pump";
            }

        } else {
            $report .= "<br>&nbsp;&nbsp;Система зворотнього осмосу допоможе завжди мати якісну та смачну питну воду.";
            $scheme["osmosis"] = "pure";
        }
        if ($no_cp == 0) { //Если схему сгенерировали без ошибок - выводим результат
            $generated_final_filename = $scheme["mechanics"] . "_" . $scheme["filter_reagent_type"] . "-" . $scheme["filter_reagent_size"] . "_" . $scheme["filter_carbon_type"];
            if ($scheme["carbon_needed"] == "-opt" and $bb_carbon=0) {
                $generated_final_filename .= "-opt";
            }
            $generated_final_filename .= "_" . $scheme["osmosis"] . ".docx";
            $cp_template_dir = "blanks/" . $scheme['filter_reagent_type'] . "_" . $scheme['filter_carbon_type'] . "/";
            //echo($analysis_filepath."<br>");
            $sanyzli = $consumption_old / 0.6;
            $new_file_name =  $_SERVER['DOCUMENT_ROOT']."/".$analysis_filepath . "/cp_" . $terrasoft_id . "_(" . $sanyzli . "-c.y.)_" . $generated_final_filename;

            //echo ($new_file_name."<br>");
            $templateFile = $_SERVER['DOCUMENT_ROOT']."/blanks/cp/" . $scheme["filter_reagent_type"] . "_" . $scheme["filter_carbon_type_url"] . "/" . $generated_final_filename;
            //echo("new_file_name: ".$new_file_name);

            if (!file_exists($new_file_name)) {
                //echo("templateFile: $templateFile <br>");
                if (file_exists( $templateFile)) {
                    if (copy($templateFile,
                        $new_file_name)) {
                        echo("<font color='red'>" . $CP_generator['CP_generated'] . "</font><br><br>");
                        echo('<script>
                                            $(document).ready(function() {
$("#filelist").html(\'');
                        require("print_files_in_dir.php");
                        echo('\')});</script>');
                    }
                } else {
                    //echo("<br>SITE_ROOT: ". $templateFile);
                    $query_log = log_error_query("generate_cp", "CP_example_doesnt_exist", $_SESSION['login'], "TS_ID:$terrasoft_id;CP_filename_needed:$generated_final_filename");
                    $mysqli->query($query_log);
                    DieDieDieMyDarling($scheme_generator["error_CP_example_doesnt_exist"]);
                }
            } else {
                DieDieDieMyDarling($scheme_generator["error_CP_exists"]);
            }

            if($mysqli->query("INSERT into `log_CP_generator` (aid, cp_scheme, partner_id, analysis_descr, source, consumption) VALUES ('$terrasoft_id', '$generated_final_filename', '$company_id', '".addslashes($report)."','".$report_text[$source]."','$consumption')")) {
                echo($report);
            }

        }else {
            $query_log = log_error_query("generate_cp", "water_seems_like_being_good", $_SESSION['login'], "TS_ID:$terrasoft_id");
            $mysqli->query($query_log);
            DieDieDieMyDarling($scheme_generator["water_seems_like_being_good"]);
        }

    } else {
        if ($_SESSION['login']) {
            $login = $_SESSION['login'];
        } else {
            $login = "";
        }
        $query_log = log_error_query("generate_cp", "no_post_query", $login, "no_POST_query");
        $mysqli->query($query_log);
        DieDieDieMyDarling($scheme_generator["error_crytical_error"]);
    }
}


?>







