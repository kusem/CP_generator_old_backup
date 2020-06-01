<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: /', true);
} else {
    //echo($_SERVER['DOCUMENT_ROOT']);
    require_once("config.php");
    if (isset($_SESSION['language'])) {
        require_once "lang/" . $_SESSION['language'] . ".php";
    } else {
        require_once "lang/ua.php";
    }
    $query_additional = "";
    $companies_id = "";
    if (isset($_GET['companies_id'])) {
        $companies_id = $_GET['companies_id'];
        $query_additional .= "AND ";
    }
    if (isset($_COOKIE["DRSd"])
    and !empty($_COOKIE["DRSd"]) 
    and !empty($_COOKIE["DRSm"]) 
    and !empty($_COOKIE["DRSy"]) 
    and !empty($_COOKIE["DREd"]) 
    and !empty($_COOKIE["DREm"]) 
    and !empty($_COOKIE["DREy"])
    ) { //DateRangeStartDay
        $DRSd = $_COOKIE["DRSd"];
        $DRSm = $_COOKIE["DRSm"];
        $DRSy = $_COOKIE["DRSy"];
        $DREd = $_COOKIE["DREd"];
        $DREm = $_COOKIE["DREm"];
        $DREy = $_COOKIE["DREy"];
        $date_limit = "'$DRSy-$DRSm-$DRSd' AND '$DREy-$DREm-$DREd'";
    } else {
        $date_day = date("d");
        $date_month = date("m");
        $date_year = date("Y");
        $DRSd = "01";
        $DRSm = $date_month;
        $DRSy = $date_year;
        $DREd = $date_day;
        $DREm = $date_month;
        $DREy = $date_year;
        echo("<script>var DRSd = \"$DRSd\", DRSm= \"$DRSm\", DRSy= \"$DRSy\", DREd= \"$DREd\", DREm= \"$DREm\", DREy= \"$DREy\";</script>");
        //echo($date_start."<br>".$date_end);
        //$date_limit = "'" . (intval($date_year)) . "-$date_month-01' AND '$date_year-$date_month-$date_day'";
        $date_limit = "'" . (intval(2017)) . "-09-01' AND '$date_year-$date_month-$date_day'";
    }
}

//$query = "SELECT * FROM  `analysis_results` WHERE analysis_date BETWEEN $date_limit AND owner_login IN (SELECT login FROM users_list WHERE company_id IN(".$_SESSION['companies_where_is_admin'].")) ORDER BY id DESC";
if(isset($_SESSION['companies_where_is_admin'][0])){
    $query_company_list_add=" OR company_id IN(".$_SESSION['companies_where_is_admin'].")";
}else{
    $query_company_list_add="";
}
$query = "SELECT * FROM users_list ul
inner join (
SELECT * FROM `analysis_results` as ar WHERE analysis_date BETWEEN $date_limit AND owner_login IN 
(SELECT ul.login FROM users_list as ul WHERE owner_login='".$_SESSION['login']."' $query_company_list_add) ORDER BY id DESC) ar
on ul.login=ar.owner_login";

if (isset($_GET["limit"])) {
    $limit = " LIMIT " . intval($_GET["limit"]);
} else {
    $limit = " LIMIT 10";
}
//$query .= $limit;
//echo $query;
$result = $mysqli->query($query);
//echo("<pre>");print_r( $_SESSION);echo("</pre><br>");
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Список аналізів</title>

    <!-- Bootstrap core CSS -->

    <link href="css/bootstrap.min.css" rel="stylesheet">

    <link href="fonts/css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animate.min.css" rel="stylesheet">

    <!-- Custom styling plus plugins -->
    <link href="css/custom.css" rel="stylesheet">
    <link href="css/icheck/flat/green.css" rel="stylesheet">
    <link href="css/datatables/tools/css/dataTables.tableTools.css" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        fieldset.scheduler-border {
            border: 1px groove #ddd !important;
            padding: 0 5px 5px 5px !important;
            margin: 0 5px !important;
            -webkit-box-shadow: 0px 0px 0px 0px #000;
            box-shadow: 0px 0px 0px 0px #000;
        }

        legend.scheduler-border {
            color: white;
            width: auto; /* Or auto */
            margin-bottom: -10px !important;
            padding: 10px 10px; /* To give a bit of padding on the left and right */
            border-bottom: none !important;
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


                    <div class="menu_section">
                        <h3>
                            <div align="center">Фільтри</div>
                        </h3>
                        <!--
                        <form action="analysis_list.php">
                            <fieldset class="scheduler-border">
                                <legend class="scheduler-border">Контрагенти:</legend>
                                <p style="padding: 10px;color:white">
                                    <?php foreach($_SESSION["visible_companies"] as $key=>$value){
                                        echo("<input type=\"checkbox\" name=\"counterparty[]\" id=\"$key\" value=\"$key\"
                                           data-parsley-mincheck=\"2\" required class=\"flat\"/> ".$value["name_original"]."<br/>");
                                    } ?>
                                </p>
                            </fieldset>
                        </form>
                        -->

                        <script type="text/javascript"
                                src="//cdn.jsdelivr.net/momentjs/latest/moment-with-locales.min.js"></script>
                        <form class="form-horizontal" action="analysis_list.php">
                            <fieldset class="scheduler-border">
                                <legend class="scheduler-border">Період</legend>
                                <div class="control-group">
                                    <div class="controls">
                                        <div class="input-prepend" align="center">
                                            <input type="text" style="width: 90%" name="reservation"
                                                   id="reservation" class="form-control" value="<?php echo "$DRSd-$DRSm-$DRSy : $DREd-$DREm-$DREy"; ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                        <!-- bootstrap-datetimepicker -->
                        <script src="js/datepicker/daterangepicker.js"></script>
                        <?php
                        //date_range_start
                        //date_range_end;
                        ?>
                        <script>
                            $('input[name="reservation"]').daterangepicker({
                                    locale: {
                                        format: 'DD-MM-YYYY',
                                        "separator": " : ",
                                        <?php
                                        echo "startDate: 01-".date('m') ."-". DATE("Y");
                                        echo ", endDate: " . date('d-m-Y') . ",";?>
                                        <?php include_once("lang/" . $_SESSION['language'] . "_rangepicker.lang");?>
                                    }
                                });
                            $('#reservation').on('apply.daterangepicker', function (ev, picker) {
                                document.cookie = "DRSd="+picker.startDate.format('DD');
                                document.cookie = "DRSm="+picker.startDate.format('MM');
                                document.cookie = "DRSy="+picker.startDate.format('YYYY');
                                document.cookie = "DREd="+picker.endDate.format('DD');
                                document.cookie = "DREm="+picker.endDate.format('MM');
                                document.cookie = "DREy="+picker.endDate.format('YYYY');
                                location.reload();
                            });
                        </script>
                    </div>
                </div>
                <!-- /sidebar menu -->
            </div>
        </div>

        <!-- page content -->
        <div class="right_col" role="main">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2><?php echo $analysis_list['title']; ?></h2>

                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <div id="load_handler" align=center>
                                <?php $DataTable["data_loading"] ?>
                            </div>
                            <div id="content_table" style="display:none;">

                                <table id="analysis_list_table"
                                       class="table table-striped table-bordered responsive-utilities " style="font-size: 13px;">
                                    <thead>
                                    <tr class="headings">
                                        <th><?php echo $analysis_list['date']; ?> </th>
                                        <th><?php echo $analysis_list['ID']; ?> </th>
                                        <th><?php echo $analysis_list['place']; ?> </th>
                                        <?php //echo "<th><?php echo $analysis_list['consumption'] </th>" ;?>
                                        <th><?php echo $analysis_list['manager_name']; ?> </th>
                                        <th><?php echo $analysis_list['company_name']; ?> </th>
                                            <!-- <th><?php //echo $analysis_list['water_quality'];?> </th>-->
                                        <th><?php echo $analysis_list['status']; ?> </th>
                                            <!-- <th class="no-link last"><span class="nobr"><?php //echo $analysis_list['action'];?></span>-->
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <?php
                                    if ($result) {
                                        while ($row = $result->fetch_assoc()) {
                                            if(!isset($_SESSION['analysis_id_list'][$row["id"]])){
                                                $_SESSION['analysis_id_list'][$row["id"]]=1;
                                            }
                                            echo "<tr class='even pointer'>";
                                            echo "    <td class=' '>" . $row['analysis_date'] . "</td>";
                                            echo "    <td class=' '>" . $row['terrasoft_id'] . "</td>";
                                            echo "    <td class=' '><a href='detailed_analysis.php?aid=" . $row['id'] . "'>" . $row['description'] . "</a></td>";
                                            //echo "    <td class=' '>". $row['consumption'] . "</a></td>";
                                            echo "    <td class=' last'>" . $row['name'] . "</td>";
                                            echo "    <td class=' last'>";
                                            if(isset($_SESSION['visible_companies'][$row['company_id']]['name_original'])){echo($_SESSION['visible_companies'][$row['company_id']]['name_original']);} ;
                                            echo("</td>");
                                            echo "    <td class=' last'>" . $analysis_list[$row['status']] . "</td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                <!-- Datatables -->
                                <script
                                        type="text/javascript"
                                        src="DataTables/datatables.min.js"></script>
                                <script>
                                    $(document).ready(function () {
                                        $('body .dropdown-toggle').dropdown();
                                        $('#analysis_list_table').DataTable({
                                            "language": {
                                                "url": "lang/<?php echo $_SESSION['language'];?>_datatables.lang"
                                            },
                                            dom: 'Bfrtip',

                                            colReorder: true,
                                            buttons: [{
                                                extend: 'excel',
                                                text: '<?php echo $DataTable["Excel_download_button"];?>'
                                            }]
                                        });
                                        $('#load_handler').css('display', 'none');
                                        $('#content_table').css('display', 'block');

                                    });

                                </script>
                            </div>
                        </div>
                    </div>
                </div>
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


</body>

</html>
<?php

?>
