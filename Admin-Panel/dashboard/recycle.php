<?php
  ob_start();
  require_once "functions/myconfig.php";

  // If session variable is not set it will redirect to login page
  if(!isset($_SESSION['email']) || empty($_SESSION['email'])){
    header("location: login.php");
    exit;
  }

  $email = $_SESSION['email'];
  $sql = 'SELECT * FROM recycle_bin';
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
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />

    <style>
        .mail_listing {
            max-height: 500px; /* Set desired max-height */
            overflow: auto;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .mail_listing::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .mail_listing {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <!-- <div class="preloader">
        <div class="cssload-speeding-wheel"></div>
    </div> -->
    
    <?php include 'left-nav.php'; ?>
    <!-- Left navbar-header end -->
    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title"><?php echo $email; ?></h4>
                </div>
                <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                    <ol class="breadcrumb">
                        <li><a href="index.php">Dashboard</a></li>
                        <li class="active">Posts</li>
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
                            <div class="col-lg-12 col-md-9 col-sm-12 col-xs-12 mail_listing">
                                <div class="inbox-center">
                                    <?php
                                    if (isset($_GET['success'])) {
                                        echo '<div class="alert alert-success">
                                               <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                               <strong>DONE!! </strong><p> Page has been updated successfully.</p>
                                               </div>';
                                    } elseif (isset($_GET["deleted"])) {
                                        echo
                                            '<div class="alert alert-warning">
                                              <a href="#" class="close" data-dismiss="alert" aria-label="close"></a>
                                             <strong>DELETED!! </strong><p> The Page has been successfully deleted.</p>
                                        </div>';
                                    } elseif (isset($_GET["del_error"])) {
                                        echo
                                            '<div class="alert alert-danger">
                                              <a href="#" class="close" data-dismiss="alert" aria-label="close"></a>
                                             <strong>ERROR!! </strong><p> There was an error during deleting this record. Please try again.</p>
                                        </div>';
                                    }
                                    ?>
                                    <table id="example23" class="table table-hover display">
                                        <thead>
                                            <tr>
                                            <th><input type="checkbox" id="selectAll"><span class="m-1">All</span></th>
                                                <th>Title</th>
                                                <th>Slug</th>
                                                <th>Date</th>
                                                <th>Edit</th>
                                                <th>Restore</th>
                                                <th>Delete</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($row = mysqli_fetch_array($query)) {
                                                $row['date'] = date('d-M-Y', strtotime($row['date']));
                                                echo
                                                '<tr>
                                                 <td><input type="checkbox" name="delete[]" value="'.$row["id"].'"></td>
                                                    <td class="hidden-xs"><a href="edit_page.php?id=' . $row["id"] . '">' . $row["title"] . '</a></td>
                                                    <td class="max-texts">' . $row["slug"] . '</td>
                                                    <td class="text-right">' . $row["date"] . '</td>
                                                    <td class="text-right"><a href="edit_page.php?id=' . $row["id"] . '" class="btn btn-success text-white p-2  fs-3">Edit</a></td>
                                                    <td class="text-right"><a href="restore.php?id=' . $row["id"] . '" class="btn btn-success text-white p-2  fs-3">Restore</a></td>
                                                    <td class="text-right"><a href="delete_recycle.php?id=' . $row["id"] . '" class="btn btn-danger text-white p-2  fs-3">Delete</a></td>

                                                </tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <button id="deleteSelected" class="btn btn-danger">Delete Selected Items</button>
                                </div>
                               
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                </div>
            </div>
            <!-- /.row -->
            <?php include 'service-panel.php'; ?>
            <!-- /.right-sidebar -->
        </div>
        <!-- /.container-fluid -->
        <footer class="footer text-center"> 2024 &copy; Airlines Admin </footer>
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
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script>
      $(document).ready(function () {
    $('#example23').DataTable({
        dom: 'Blfrtip', 
        buttons: [
           //'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        lengthMenu: [[50, 200, 500, 999], [50, 200, 500, 999]]
    });
});
    </script>
     <script>
        document.getElementById('deleteSelected').addEventListener('click', function() {
    var checkboxes = document.querySelectorAll('input[name="delete[]"]:checked');
    var ids = [];
    checkboxes.forEach(function(checkbox) {
        ids.push(checkbox.value);
    });

    if (ids.length > 0) {
        var confirmation = confirm("Are you sure you want to delete the selected items?");
        if (confirmation) {
            window.location.href = 'delete_selected_recycle.php?ids=' + ids.join(',');
        }
    } else {
        alert("Please select at least one item to delete.");
    }
});
// Handle "Select All" checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('input[name="delete[]"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = event.target.checked;
    });
});
    </script>
</body>

</html>
