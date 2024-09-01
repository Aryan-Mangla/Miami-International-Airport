
<?php
    
    ob_start();
    require_once "functions/db.php";

    // Initialize the session



    // If session variable is not set it will redirect to login page

    if(!isset($_SESSION['email']) || empty($_SESSION['email'])){

      header("location: login.php");

      exit;
    }

    $email = $_SESSION['email'];

    $sql = 'SELECT * FROM comment_section';

    $query = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../plugins/images/icon.png">
    <title>Company Admin</title>
    <!-- Bootstrap Core CSS -->
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="../plugins/bower_components/bootstrap-extension/css/bootstrap-extension.css" rel="stylesheet">
    <!-- Menu CSS -->
    <link href="../plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.css" rel="stylesheet">
    <!-- morris CSS -->
    <link href="../plugins/bower_components/morrisjs/morris.css" rel="stylesheet">
    <!-- animation CSS -->
    <link href="css/animate.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <!-- color CSS -->
    <link href="css/colors/blue.css" id="theme" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
    <!-- Preloader -->
    <!-- <div class="preloader">
        <div class="cssload-speeding-wheel"></div>
    </div> -->
    <div id="wrapper">
      
        <!-- Left navbar-header -->
<?php include 'left-nav.php'; ?>
        
        <!-- Left navbar-header end -->
        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row bg-title">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title"><?php echo $email;?></h4> </div>
                    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
                        <ol class="breadcrumb">
                            <li><a href="index.php">Dashboard</a></li>
                            <li class="active">Comments</li>
                        </ol>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <!-- row -->
                <div class="row">
                    <!-- Left sidebar -->
                    <div class="col-md-12">
                        <div class="white-box">
                            <!-- row -->
                            <div class="row">
                               
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mail_listing">
                                    <div class="inbox-center">


                                        <?php
                                        
                                        if (isset($_GET["id"])) {
                                            // Check if delete action is triggered
                                            $id = $_GET['id'];
                                            $sql = "DELETE FROM comment_section WHERE id = $id";
                                        
                                            // Execute the query
                                            if (mysqli_query($conn, $sql)) {
                                                // If deletion is successful, display success message
                                                echo '<div class="alert alert-warning">
                                                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                        <strong>DELETED!!</strong> The comment has been successfully deleted.
                                                      </div>';
                                            } else {
                                                // If deletion fails, display error message
                                                echo '<div class="alert alert-danger">
                                                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                                        <strong>ERROR!!</strong> There was an error during deleting this record. Please try again.
                                                      </div>';
                                            }
                                        }
                                ?>
                                                        <h4>Recent Posts Comments (<b style="color: orange;"><?php echo mysqli_num_rows($query);?></b>)</h4>
                                                        <?php 
                                                             if (mysqli_num_rows($query)==0) {
                                                                echo "<i style='color:brown;'>No Comments Yet :( </i> ";}
                                                        ?>
                                                    
                                                         <div class="comment-center">

                                                             <?php
                                                while ($row = mysqli_fetch_array($query))
                                                        {
                                                            $blogid = $row["id"];
                                                            $sql2 = "SELECT * FROM comment_section WHERE id='$blogid'";
                                                              $query2 = mysqli_query($conn, $sql2);

                                                              while ($row2 = mysqli_fetch_assoc($query2)) {

                                                echo
                                              '<div class="comment-body">
                                              <div class="mail-contnet">
                                                  <b> User Name: </b> <b>'.$row["name"].'</b>';
                                                  echo '<h6 class="card-subtitle mb-2 fs-1 text-muted"><span style="color: gold;">' . str_repeat('★', $row['rating']) . '</span><span style="color: lightgray;">' . str_repeat('☆', 5 - $row['rating']) . '</span></h6>';

                                                  echo'    <div class="mail-desc">
                                                      <p class="p-0 m-0"> Comment: </p>
                                                      '.$row["comment"].'
                                                  </div>
                                                  <span class="time pull-right">'.$row["date"].'</span>
                                                  <div class="d-flex justify-content-center my-3 ">
                                                  <a href="functions/edit_comment.php?id='.$row["id"].'" class="btn mx-2">Edit</a>
                                                  <a href="comments.php?id='.$row["id"].'" class="btn mx-2">Delete</a>';
                                                  if ($row["approved"] == 1) {
                                                    echo '<a href="disapprove_comment.php?id=' . $row["id"] . '" class="btn  mx-2">Approved</a>';

                                                } else {
                                                    echo '<a href="approve_comment.php?id=' . $row["id"] . '" class="btn  mx-2">Approve</a>';
                                                }
                                           echo'   </div>
                                              </div>
                                         
                                          </div>';

                                                            } }

                                                            ?>

                                                    </div>
                                                                            
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-7 m-t-20"> Showing 1 - <?php echo mysqli_num_rows($query);?> </div>
                                        <div class="col-xs-5 m-t-20">
                                            <div class="btn-group pull-right">
                                                <button type="button" class="btn btn-default waves-effect"><i class="fa fa-chevron-left"></i></button>
                                                <button type="button" class="btn btn-default waves-effect"><i class="fa fa-chevron-right"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>
                    </div>
                </div>
                <!-- /.row -->
                <!-- .right-sidebar -->
                <div class="right-sidebar">
                    <div class="slimscrollright">
                        <div class="rpanel-title"> Service Panel <span><i class="ti-close right-side-toggle"></i></span> </div>
                        <div class="r-panel-body">
                            <ul>
                                <li><b>Layout Options</b></li>
                                <li>
                                    <div class="checkbox checkbox-info">
                                        <input id="checkbox1" type="checkbox" class="fxhdr">
                                        <label for="checkbox1"> Fix Header </label>
                                    </div>
                                </li>
                                <li>
                                    <div class="checkbox checkbox-warning">
                                        <input id="checkbox2" type="checkbox" checked="" class="fxsdr">
                                        <label for="checkbox2"> Fix Sidebar </label>
                                    </div>
                                </li>
                                <li>
                                    <div class="checkbox checkbox-success">
                                        <input id="checkbox4" type="checkbox" class="open-close">
                                        <label for="checkbox4"> Toggle Sidebar </label>
                                    </div>
                                </li>
                            </ul>
                            <ul id="themecolors" class="m-t-20">
                                <li><b>With Light sidebar</b></li>
                                <li><a href="javascript:void(0)" theme="default" class="default-theme">1</a></li>
                                <li><a href="javascript:void(0)" theme="green" class="green-theme">2</a></li>
                                <li><a href="javascript:void(0)" theme="gray" class="yellow-theme">3</a></li>
                                <li><a href="javascript:void(0)" theme="blue" class="blue-theme working">4</a></li>
                                <li><a href="javascript:void(0)" theme="purple" class="purple-theme">5</a></li>
                                <li><a href="javascript:void(0)" theme="megna" class="megna-theme">6</a></li>
                                <li><b>With Dark sidebar</b></li>
                                <br/>
                                <li><a href="javascript:void(0)" theme="default-dark" class="default-dark-theme">7</a></li>
                                <li><a href="javascript:void(0)" theme="green-dark" class="green-dark-theme">8</a></li>
                                <li><a href="javascript:void(0)" theme="gray-dark" class="yellow-dark-theme">9</a></li>
                                <li><a href="javascript:void(0)" theme="blue-dark" class="blue-dark-theme">10</a></li>
                                <li><a href="javascript:void(0)" theme="purple-dark" class="purple-dark-theme">11</a></li>
                                <li><a href="javascript:void(0)" theme="megna-dark" class="megna-dark-theme">12</a></li>
                            </ul>
                           
                        </div>
                    </div>
                </div>
                <!-- /.right-sidebar -->
            </div>
            <!-- /.container-fluid -->
            <footer class="footer text-center"> 2024 &copy; Company Admin </footer>
        </div>
        <!-- /#page-wrapper -->
    </div>
    <!-- /#wrapper -->
    <!-- jQuery -->
    <script src="../plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="bootstrap/dist/js/tether.min.js"></script>
    <script src="bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../plugins/bower_components/bootstrap-extension/js/bootstrap-extension.min.js"></script>
    <!-- Menu Plugin JavaScript -->
    <script src="../plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js"></script>
    <!--slimscroll JavaScript -->
    <script src="js/jquery.slimscroll.js"></script>
    <!--Wave Effects -->
    <script src="js/waves.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="js/custom.min.js"></script>
    <!--Style Switcher -->
    <script src="../plugins/bower_components/styleswitcher/jQuery.style.switcher.js"></script>
</body>

</html>
