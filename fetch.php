

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Fetch Data in PHP MySQL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />

<style type="text/css">
    


        table {
    width: 100%;
     margin-right:0px;

   }

thead, tbody, tr, td, th { display: block; }

tr:after {
    content: ' ';
    display: block;
    visibility: hidden;
    clear: both;
}

thead th {
    height: 35px;


    /*text-align: left;*/
}

tbody {
    max-height: 460px;
    overflow-y: auto;
    margin-right:70px;
    width: 700px;
}

thead {
    /* fallback */
}


tbody td, thead th {
    width: 145px;
     height: 39px;
    float: left;
}
    </style>


</style>


</head>
<body>
    
    <div class="container mt-5" style="width:700px;" >
        <div class="row justify-content-center" style="width:700px;">
            <div class="col-md-6">
                <div class="card shadow" style="width:700px;">
                    <div class="card-header" style="width:700px;">
                        <h4 >Fetch data from database in PHP MySQL</h4>
                    </div>
                    <div class="card-body" style="background:blueviolet ;width: 700px;">

                        <table class="table table-bordered table-striped" >
                            <thead>
                                <tr>
                                    <th >Branch</th>
                                    <th style="width:200px;">TT</th>
                                    <th>Lan IP</th>
                                    <th>Registration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    include('config.php');

                                
 $query =  "SELECT branch_name, tt,lanip,tt_reg_date FROM `tt_registration` ORDER BY tt_reg_date ASC";

                                    $query_run = mysqli_query($link, $query);

                                    if(mysqli_num_rows($query_run) > 0) //Atleast 1 record is there or not
                                    {
                                        foreach($query_run as $row)
                                        {
                                            ?>
                                                <tr>
                                                    <td ><?= $row['branch_name'] ?></td>
                                                    <td style="width:200px;"><?= $row['tt'] ?></td>
                                                    <td><?= $row['lanip'] ?></td>
                                                    <td><?= $row['tt_reg_date'] ?></td>
                                                </tr>
                                            <?php
                                        }
                                    }
                                    else
                                    {
                                        ?>
                                            <tr>
                                                <td colspan="4">No Record Found</td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>