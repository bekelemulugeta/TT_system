
<?php 


 
include_once("config.php");
include_once("admin.php");
include_once ("all_service_info_update.php");
$queryyy =  "SELECT id,branch_name, service_number,service_type,bw ,wanip,lanip FROM `service_info` ORDER BY branch_name ASC";
$result = mysqli_query($link, $queryyy);
  
 ?> 

<html>
    <head>
        <title>update status</title>
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">
<link href="min.css" rel="stylesheet"/>
<script src="min.js"></script>
<script src="jquery-3.6.0.min.js"></script>
    <script src="jquery.table2excel.js"></script>

<style type="text/css">
        table {
    width: 100%;
border: 1px solid gray;
   }

thead, tbody, tr, td, th { display: block; }

tr:after {
    content: ' ';
    display: block;
    visibility: hidden;
    clear: both;
}

thead th {
    height: 40px;


    /*text-align: left;*/
}

tbody {
    max-height: 200px;
    overflow-y: auto;
    margin-right:25px;
}

thead {
    /* fallback */
}


tbody td, thead th {
    width: 185px;
      height: 40px;
    float: left;
    border: 1px solid gray;
}
    </style>
 </head>
<body>

<table class="table2excel table2excel_with_colors " style="margin-left: -115px;margin-top: 10px;position: fixed;    width: 98%;" >
  <br>

    <thead>
        <tr>   
                              

                               <th Style="width:330px;">Branch Name</th>  
                               <th Style="width:160px;">Service Number</th>  
                               <th Style="width:140px;">Service Type</th>  
                               <th Style="width:130px;">Band width</th>

                               <th >WAN IP</th>  
                               <th >LAN IP</th> 
                               <th class="noExl" Style="width:150px;">Action</th>


                          </tr>  
    </thead>
    <tbody>
        <?php   
                          while($row = mysqli_fetch_array($result))  
                          {  
                          ?>   
                          <tr id="<?php echo $row['0'] ?>">

                            <td Style="width:330px;"><?php echo $row['1']; ?></td>  
                               <td Style="width:160px;"><?php echo $row['2']; ?></td>  
                               <td Style="width:140px;"><?php echo $row['3']; ?></td>  
                               <td Style="width:130px;"><?php echo $row['4']; ?></td>
                               <td><?php echo $row['5']; ?></td>
                               <td><?php echo $row['6']; ?></td>
                              <td class="noExl" Style="width:150px;"><button class="btn btn-danger btn-sm remove">Delete</button></td>
                               
                          </tr>  
                          <?php                           
                          }  
                          ?>
    </tbody>
</table>


                     <button style="margin-top: -20px;margin-left: -120px;position: fixed;" class="exportToExcel">Export to XLS</button>






<script type="text/javascript">
    $(".remove").click(function(){
        var id = $(this).parents("tr").attr("id");


        if(confirm('Are you sure to delete this service information ?'))
        {
            $.ajax({
               url: 'all_service_del.php',
               type: 'GET',
               data: {id: id},
               error: function() {
                  alert('Something is wrong');
               },
               success: function(data) {
                    $("#"+id).remove();
                    alert("service information deleted successfully");  
               }
            });
        }
    });

  $(function() {
        $(".exportToExcel").click(function(e){
          var table = $(this).prev('.table2excel');
          if(table && table.length){
            var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
            $(table).table2excel({
              exclude: ".noExl",
              name: "Excel Document Name",
              filename: "Service Number list" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
              fileext: ".xls",
              exclude_img: true,
              exclude_links: true,
              exclude_inputs: true,
              preserveColors: preserveColors
            });
          }
        });
        
      });
</script>

</body>



</html>



