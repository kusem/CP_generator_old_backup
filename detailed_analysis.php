<?php

session_start();
if (!isset($_SESSION['login'])) {
    header( 'Location: /', true );
}else {
    if (isset($_GET["aid"])) {
        $aid = $_GET['aid'];

        require_once "config.php";

        if (isset($_SESSION['language'])) {
            require_once "lang/" . $_SESSION['language'] . ".php";
        } else if (isset($_GET["language"])) {
            require_once "lang/" . $_GET['language'] . ".php";
        } else {
            require_once "lang/ua.php";
        }
        if(!isset($_SESSION['analysis_id_list'][$aid])){
            //log_error_query("company_doesnt_exists", "company_doesnt_exists", $_SESSION['login'], "original_id:".$aid);
            //print_r($_SESSION['analysis_id_list']);
            header('Location: /', true);
            die("Permission denied.");
        }

        $result = $mysqli->query("SELECT * FROM  `analysis_results` WHERE id='$aid'");
        $result_table = "";
        $result_array = array();


        if ($result) {
            $row = $result->fetch_assoc();
            //echo "<pre>";print_r($row);echo "</pre>";
            if ($row['turbidity'] != "") {
                $turbidity = floatval(str_replace(",",".",$row['turbidity']));
                $result_table .= "<tr><td><p align='center'>";
                if ($turbidity < 0.58) {
                    $result_table .= "<font color='green'><i class=\"fa fa-check\"></i></font>";
                } else {
                    $result_table .= "&nbsp;<font color='red'><i class=\"fa fa-exclamation\"></i></font>";
                };
                $result_table .= "</p></td><td>" . $detailed_analysis['turbidity'] . "</td><td>" . $row['turbidity'] . "</td><td>0,58</td><td>0,58</td></tr >";

                $result_array["turbidity"] = htmlspecialchars(strval($row['turbidity']));
            };
            if ($row['smell'] != "") {
                $smell = $row['smell'];
                $result_table .= "<tr><td><p align='center'>";
                if ($smell == 0) {
                    $result_table .= "<font color='green'><i class=\"fa fa-check\"></i></font>";
                } elseif ($smell < 3 and $smell > 0) {
                    $result_table .= "<font color='#FF8C00'><i class=\"fa fa-question\"></i></font>";
                } else {
                    $result_table .= "<font color='red'><i class=\"fa fa-exclamation\"></i></font>";
                };
                $result_table .= "</p></td><td>" . $detailed_analysis['smell'] . "</td><td>" . $row['smell'] . "</td><td>2</td><td>2</td></tr >";

                $result_array["smell"] = htmlspecialchars(strval($row['smell']));
            };
            if ($row['color'] != "") {
                $color = $row['color'];
                $result_table .= "<tr><td><p align='center'>";
                if ($color <20) {
                    $result_table .= "<font color='green'><i class=\"fa fa-check\"></i></font>";
                } else {
                    $result_table .= "<font color='red'><i class=\"fa fa-exclamation\"></i></font>";
                };
                $result_table .= "</p></td><td>" . $detailed_analysis['color'] . "</td><td>" . $row['color'] . "</td><td>20</td><td>20</td></tr >";

                $result_array["color"] = htmlspecialchars(strval($row['color']));
            };
            if ($row['Fe'] != "") {
                $Fe = floatval(str_replace(",",".",$row['Fe']));
                $result_table .= "<tr><td><p align='center'>";
                if ($Fe < 0.2) {
                    $result_table .= "<font color='green'><i class=\"fa fa-check\"></i></font></span>";
                } else {
                    $result_table .= "<font color='red'><i class=\"fa fa-exclamation\"></i></font>";
                };
                $result_table .= "</p></td><td>" . $detailed_analysis['Fe'] . "</td><td>" . $row['Fe'] . "</td><td>0,2</td><td>0,2</td></tr >";
//print_r($Fe);
                $result_array["Fe"] = htmlspecialchars(strval($row['Fe']));

            };
            if ($row['hardness'] != "") {
                $hardness = floatval(str_replace(",",".",$row['hardness']));
                $result_table .= "<tr><td><p align='center'>";
                if ($hardness < 1.5) {
                    $result_table .= "<font color='green'><i class=\"fa fa-check\"></i></font>";
                } elseif ($hardness < 7.0 and $hardness >= 1.5) {
                    $result_table .= "<font color='#FF8C00'><i class=\"fa fa-question\"></i></font>";
                } else {
                    $result_table .= "<font color='red'><i class=\"fa fa-exclamation\"></i></font>";
                };
                $result_table .= "</p></td><td>" . $detailed_analysis['hardness'] . "</td><td>" . $row['hardness'] . "</td><td>7,0</td><td>1,5</td></tr >";
                $result_array["hardness"] = strval($row['hardness']);

            };
            if ($row['nitrates'] != "") {
                $nitrates = floatval(str_replace(",",".",$row['nitrates']));
                $result_table .= "<tr><td><p align='center'>";
                if ($nitrates < 50) {
                    $result_table .= "<font color='green'><i class=\"fa fa-check\"></i></font>";
                } else {
                    $result_table .= "<font color='red'><i class=\"fa fa-exclamation\"></i></font>";
                };
                $result_table .= "</p></td><td>" . $detailed_analysis['nitrates'] . "</td><td>" . $row['nitrates'] . "</td><td>50</td><td>-</td></tr >";
                $result_array["nitrates"] = htmlspecialchars(strval($row['nitrates']));
            };
            if ($row['TDS'] != "") {
                $TDS = floatval($row['TDS']);
                $result_table .= "<tr><td><p align='center'>";
                if ($TDS < 1000) {
                    $result_table .= "<font color='green'><i class=\"fa fa-check\"></i></font></span>";
                } else {
                    $result_table .= "<font color='yellow'><i class=\"fa fa-exclamation\"></i></font>";
                };
                $result_table .= "</p></td><td>" . $detailed_analysis['TDS'] . "</td><td>" . $row['TDS'] . "</td><td>1000</td><td>1000</td></tr >";
                $result_array["TDS"] = htmlspecialchars(strval($row['TDS']));
            };
            $status=$row['status'];
            $source="";
            if ($row['source'] != "") {
                $source = $row['source'];
            }else{
                //if($row['source'] == "well_tube"){
                //    $source = "well_tube";
                //}elseif ($row['source'] == "well_room"){$source = "well_room";}
                $source = "water_source_not_set";
            };
            if ($row['description'] != "") {
                $description = $row['description'];
            }else{
                $description = "description_not_set";
            };
            if ($row['owner_login'] != "") {
                $owner_login = $row['owner_login'];
                $owner_name_query = $mysqli->query("SELECT * FROM users_list WHERE login='$owner_login'");
                if($owner_name_query){
                    while($result= $owner_name_query -> fetch_assoc()){
                        $owner_name=$result["name"];
                    }
                }else{
                    $owner_name="owner_name_not_set";
                }
            }else{
                $owner_login = "owner_login_not_set";
            };
            require_once "base_scripts/analysis_file_check.php";
            $owner_id = $row['owner_id'];

//парсим дату - определяем квартал
            $analysis_date = date_parse($row["analysis_date"]);
            $analysis_date_quart = 0;
            if ($analysis_date) {
                if ($analysis_date['month'] > 0 and $analysis_date['month'] < 4) {
                    $analysis_date_quart = 1;
                } elseif ($analysis_date['month'] > 3 and $analysis_date['month'] < 7) {
                    $analysis_date_quart = 2;
                } elseif ($analysis_date['month'] > 6 and $analysis_date['month'] < 10) {
                    $analysis_date_quart = 3;
                } elseif ($analysis_date['month'] > 9 and $analysis_date['month'] < 13) {
                    $analysis_date_quart = 4;
                } else {
                    $analysis_date_quart = 0;
                };
            }

            $scheme = array(); // массив с принципиальной схемой

            //echo "<pre>";print_r($result);echo "</pre>";
        }
        ?>

        <!DOCTYPE html>
        <html lang="uk">

        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <!-- Meta, title, CSS, favicons, etc. -->
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <title><?php echo $detailed_analysis['analysis_result_number'] . " " . $row['terrasoft_id']; ?> </title>

            <!-- Bootstrap core CSS -->

            <link href="css/bootstrap.min.css" rel="stylesheet">

            <link href="fonts/css/font-awesome.min.css" rel="stylesheet">
            <link href="css/animate.min.css" rel="stylesheet">


            <link rel="stylesheet" href="remodal/remodal.css">
            <link rel="stylesheet" href="remodal/remodal-default-theme.css">
            <!-- Custom styling plus plugins -->
            <link href="css/custom.css" rel="stylesheet">
            <link href="css/icheck/flat/green.css" rel="stylesheet">


            <script src="js/jquery.min.js"></script>

            <!--[if lt IE 9]>
            <script src="../assets/js/ie8-responsive-file-warning.js"></script>
            <![endif]-->
            <link href="/js/notify/pnotify.css" rel="stylesheet">
            <link href="/js/notify/pnotify.buttons.css" rel="stylesheet">
            <link href="/js/notify/pnotify.nonblock.css" rel="stylesheet">
            <script src="/js/notify/pnotify.js"></script>
            <script src="/js/notify/pnotify.buttons.js"></script>
            <script src="/js/notify/pnotify.nonblock.js"></script>
            <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->
            <style media='print' type='text/css'>
                .remodal-bg.with-red-theme.remodal-is-opening,
                .remodal-bg.with-red-theme.remodal-is-opened {
                    filter: none;
                }

                .noprint {
                    display: none;
                }

                * {
                    -webkit-print-color-adjust: exact !important;
                }

            </style>
            <style>
                a[href$=".pdf"] {
                    padding: 0 0 0 20px;
                    background: transparent url('images/fileformats/pdf_small.png') no-repeat left top;
                }

                a[href$=" . doc"] {
                    padding: 0 0 0 20px;
                    background: transparent url('images/fileformats/docx_small.png') no-repeat left top;
                }

                a[href$=".docx"] {
                    padding: 0 0 0 20px;
                    background: transparent url('images/fileformats/docx_small.png') no-repeat left top;
                }
            </style>
        </head>


        <body class="nav-md">


        <div class="container body">


            <div class="main_container">

                <div class="col-md-3 left_col">
                    <div class="left_col scroll-view">

                        <div class="navbar nav_title" style="border: 0;">
                            <object type="image/svg+xml" data="images/ecosoft-color-logo.svg"></object>
                        </div>
                        <div class="clearfix"></div>


                        <br/>

                        <!-- sidebar menu -->
                        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

                            <div class="menu_section">
                                <?php include("menu.php"); ?>
                            </div>

                        </div>
                        <!-- /sidebar menu -->

                    </div>
                </div>


                <!-- page content -->
                <div class="right_col" role="main">
                    <div class="">




                        <div class="row">

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2><?php echo $detailed_analysis['head']; ?></h2>

                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <table border="0">
                                        <?php


        $check_if_there_is_change_status_story=$mysqli->query("SELECT * FROM log_change_analysis_status WHERE aid='$aid'");
        $change_status_story="";
        if($check_if_there_is_change_status_story->num_rows > 0){
            while($fetched_status_array = $check_if_there_is_change_status_story->fetch_assoc() ){
                $change_status_story.="<br>".$analysis_list[$fetched_status_array['new_value']]." - ".$fetched_status_array['timestamp'];
            }
            echo("<script>
$( document ).ready(function() {
new PNotify({
                                  title: '".$analysis_list['title_log_change_notification']."',
                                  type: 'info',
                                  text: '".$analysis_list['content_log_change_notification'].$change_status_story."',
                                  styling: 'bootstrap3'
                              });
                              });
</script>");
        }
            echo "<tr class=\"noprint\"><td><b>".$analysis_list['status'].":</b></td><td> <ul class=\"nav nav-pills\" role=\"tablist\">
                    <li role=\"presentation\" class=\"dropdown\">
                      <a id=\"drop0\" href=\"#?status=$status\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" role=\"button\" aria-expanded=\"false\" style='padding:0px 0px 0px 0px;'>
                                  &nbsp;&nbsp;&nbsp;".$analysis_list[$status]."<span class=\"caret\"></span>
                              </a>
                      <ul id=\"menu0\" class=\"dropdown-menu animated fadeInDown\" role=\"menu\">
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?status=analysis_ready\" class=\"changeanalysisstatus\">".$analysis_list['analysis_ready']."</a></li>
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?status=CP_ready\" class=\"changeanalysisstatus\">".$analysis_list['CP_ready']."</a></li>
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?status=CP_sent\" class=\"changeanalysisstatus\">".$analysis_list['CP_sent']."</a></li>
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?status=sold\" class=\"changeanalysisstatus\">".$analysis_list['sold']."</a></li>
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?status=hold\" class=\"changeanalysisstatus\">".$analysis_list['hold']."</a></li>
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?status=CP_not_sold\" class=\"changeanalysisstatus\">".$analysis_list['CP_not_sold']."</a></li>
                      </ul>
                    </li>
                    </ul>
                    <script>
                    
                    $('.changeanalysisstatus').bind('click',function(){
   var url = ($(this).attr('href'));
   var status = getURLParameter(url, 'status');
    update_status(status);
});


function getURLParameter(url, name) {
    return (RegExp(name + '=' + '(.+?)(&|$)').exec(url)||[,null])[1];
}
                   
function update_status(status) {
    $.ajax({
       type:'post',
       url:'/base_scripts/update_analysis_status.php',
       data:{'status':status,'aID':$aid},
        async:false,
         success:function(datanew){
           if(datanew=='okey'){
               location.reload();
           }
         }
    });
}
</script>
</td></tr>";



                                        echo("<tr class=\"noprint\"><td><b>" . $analysis_list['water_source'] . ": </b></td><td> ");
                                        $check_if_there_is_change_story=$mysqli->query("SELECT * FROM log_change_water_source WHERE aid='$aid'");
                                        $change_story="";
                                        if($check_if_there_is_change_story->num_rows > 0){
                                            while($fetched_array = $check_if_there_is_change_story->fetch_assoc() ){
                                                $change_story.="<br>".$report_text[$fetched_array['new_value']]." - ".$fetched_array['timestamp'];
                                            }
                                            echo("

<script>
$( document ).ready(function() {
new PNotify({
                                  title: '".$detailed_analysis['title_log_change_notification']."',
                                  text: '".$detailed_analysis['content_log_change_notification'].$change_story."',
                                  styling: 'bootstrap3'
                              });
                              });
</script>");
                                        }
                                        //echo("TEST");print_r($source);print_r($status);
                                            echo "<ul class=\"nav nav-pills\" role=\"tablist\">
                    <li role=\"presentation\" class=\"dropdown\">
                      <a id=\"drop1\" href=\"#?source=$source\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" role=\"button\" aria-expanded=\"false\" style='padding:0px 0px 0px 0px;'>
                                  &nbsp;&nbsp;&nbsp;".$report_text[$source]."<span class=\"caret\"></span>
                              </a>
                      <ul id=\"menu1\" class=\"dropdown-menu animated fadeInDown\" role=\"menu\">
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?source=well\" class=\"changeanalysisinfo\">".$report_text['well']."</a></li>
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?source=aqueduct\" class=\"changeanalysisinfo\">".$report_text['aqueduct']."</a></li>
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?source=well_tube\" class=\"changeanalysisinfo\">".$report_text['well_tube']."</a></li>
                        <li role=\"presentation\"><a role=\"menuitem\" tabindex=\"-1\" href=\"#?source=well_room\" class=\"changeanalysisinfo\">".$report_text['well_room']."</a></li>
                      </ul>
                    </li>
                    </ul>
                    <script>
                    
                    $('.changeanalysisinfo').bind('click',function(){
   var url = ($(this).attr('href'));
   var source = getURLParameter(url, 'source');
    update_source(source);
});


function getURLParameter(url, name) {
    return (RegExp(name + '=' + '(.+?)(&|$)').exec(url)||[,null])[1];
}
                   
function update_source(source) {
    $.ajax({
       type:'post',
       url:'/base_scripts/update_analysis_information.php',
       data:{'source':source,'aID':$aid},
        async:false,
         success:function(datanew){
           if(datanew=='ok'){
               location.reload();
           }
         }
    });
}
</script>";

                                            echo"</td></tr>";
                                        //}
                                        if(isset($row["customer_name"])){echo "<tr><td><b>" . $detailed_analysis['customer'] . ":</b></td><td> &nbsp;&nbsp;&nbsp;" . $row["customer_name"] . "</td></tr>";}
                                        echo "<tr><td><b>" . $detailed_analysis['date'] . ":</b></td><td>&nbsp;&nbsp;&nbsp; " . $row["analysis_date"] . "</td></tr>";
                                        echo "<tr><td><b>" . $detailed_analysis['number_of_protocol'] . ":</b></td><td>&nbsp;&nbsp;&nbsp; " . $row["terrasoft_id"] . "</td></tr>";
                                        if(isset($owner_name) AND $owner_name!="owner_name_not_set") {
                                            echo "<tr><td><b>" . $report_text['owner_name'] . ": </b></td><td>&nbsp;&nbsp;&nbsp;" . $owner_name . "</td></tr>";
                                        }
                                        if(isset($description) AND $description!="description_not_set") {
                                            echo "<tr><td><b>" . $report_text['description'] . ": </b></td><td>&nbsp;&nbsp;&nbsp;" . $description . "</td></tr>";
                                        }else{
                                            echo "<tr><td><b>" . $analysis_list['description'] . ": </b></td><td>&nbsp;&nbsp;&nbsp;<font color=\"red\">" . $report_text["description_not_set"] . "</font></td></tr>";
                                        }
                                        ?>
                                        </table>
                                        <br>
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th width="30px;"></th>
                                                <th>Показник</th>
                                                <th>Виміряно</th>
                                                <th>ДСанПін 2.2.171-10</th>
                                                <th>Вода для технічних потреб</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            echo $result_table;
                                            ?>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>


                            <div class="clearfix"></div>

                        </div>


                        <!--/2nd block-->
                        <!--files list-->

                        <div class="row noprint">

                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2><?php echo $detailed_analysis['filelist_title']; ?></h2>

                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content" id="filelist">
                                        <?php
                                        require_once("base_scripts/print_files_in_dir.php");
                                        ?>
                                    </div>
                                </div>
                            </div>




                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2><?php echo $scheme_generator['title']; ?></h2>

                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <div id="add_cp_response">
                                            <form id="cp_generator">
                                                <h2>Уточнення ТЗ</h2>
                                                <p>Для підбору обладнання необхідно знати кількість с/в.<br> Якщо с/в більше, аніж запропоновано нижче - зверніться до інженера.</p>
                                                <br>
                                                <div class="btn-group" data-toggle="buttons">
                                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" value="0.6"name="consumption">1 с/в (0.6 м3/ч)</input>
                                                </label>
                                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" value="1.2" name="consumption">2 с/в (1.2 м3/ч)</input>
                                                </label>
                                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" value="1.8" name="consumption">3 с/в (1.8 м3/ч)</input>
                                                </label>
                                                <br>
                                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" value="2.4" name="consumption">4 с/в (2.4 м3/ч)</input>
                                                </label>
                                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                                    <input type="radio" value="3.0" name="consumption">5 с/в (3.0 м3/ч)</input>
                                                </label>
                                                </div>
                                            <!---->
                                            <button id="button" type="submit" class="nav navbar-right panel_toolbox btn btn-danger">&nbsp;&nbsp;&nbsp;<?php echo $scheme_generator["generator_button"];?></button>
                                            <!---->
                                        </form>

                                        </div>
                                                <script>
                                                    $(document).ready(function () {
                                                        $( "#cp_generator" ).submit(function ( event ) {

                                                            var $jsonobj =<?php echo json_encode($result_array);?>;
                                                            $jsonobj.analysis_filepath = "<?php echo $analysis_filepath;?>";
                                                            $jsonobj.company_id = "<?php echo $company_id;?>";
                                                            $jsonobj.terrasoft_id = "<?php echo $row["terrasoft_id"];?>";
                                                            $jsonobj.source = "<?php echo $source;?>";
                                                            $jsonobj.consumption = $("input[name=consumption]:checked").val();
                                                            $.post('base_scripts/generate_cp.php', $jsonobj).done(function (data) {
                                                                $('#add_cp_response').html(data);
                                                            });
                                                            event.preventDefault();
                                                        });
                                                    });
                                                </script>
                                            </div>


                                        </div>

                                    </div>
                                </div>
                            </div>


                            <div class="clearfix"></div>

                        </div>
                        <!--end block-->

                    </div>


                </div>
                <!-- /page content -->
            </div>

        </div>

        <div id="custom_notifications" class="custom-notifications dsp_none">
            <ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group">
            </ul>
            <div class="clearfix"></div>
            <div id="notif-group" class="tabbed_notifications"></div>
        </div>

        <script src="js/bootstrap.min.js"></script>
        <!-- bootstrap progress js -->
        <script src="js/progressbar/bootstrap-progressbar.min.js"></script>
        <script src="js/nicescroll/jquery.nicescroll.min.js"></script>
        <!-- icheck -->
        <script src="js/icheck/icheck.min.js"></script>

        <script src="js/custom.js"></script>

        </body>

        </html>
        <?php
    } else {
        header('Location: /analysis_list.php', true);
    }
}