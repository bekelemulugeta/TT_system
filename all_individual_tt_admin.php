

<?php
 include_once("adminn.php");
 include_once("config.php");
 include_once("all_report_admin.php");
 $query = "SELECT branch_name FROM `service_info` ORDER BY branch_name ASC";
$result111 = mysqli_query($link, $query);
if(isset($_POST['submitt'])){
    
$re='0000-00-00';
$start = mysqli_real_escape_string($link,$_POST['sdate']);
$end = mysqli_real_escape_string($link,$_POST['edate']); 
$branch= mysqli_real_escape_string($link,$_POST['branch_name']);
$queryyy =  "SELECT branch_name,service_number,tt,tt_reg_date,tt_resolved_date,status,remark FROM `tt_registration` WHERE  ((branch_name ='".$branch."') and (tt_reg_date between '$start' and '$end')) ORDER BY tt_reg_date ASC";

$quer =  "SELECT count(*) as alll FROM `tt_registration` WHERE  ((branch_name ='".$branch."') and (tt_reg_date between '$start' and '$end')) ";

$qu =  "SELECT count(*) as solved FROM `tt_registration` WHERE  ((branch_name ='".$branch."') and (tt_reg_date between '$start' and '$end') and (tt_resolved_date!='".$re."')) ";

$q =  "SELECT count(*) as unresolved FROM `tt_registration` WHERE  ((branch_name ='".$branch."') and (tt_reg_date between '$start' and '$end') and (tt_resolved_date='".$re."')) ";


$resultt = mysqli_query($link, $quer);
$alll=mysqli_fetch_array($resultt);
$all=$alll['alll'];

$resul = mysqli_query($link, $qu);
$resolvedd=mysqli_fetch_array($resul);
$resolved=$resolvedd['solved'];

$resu = mysqli_query($link, $q);
$unresolvedd=mysqli_fetch_array($resu);
$unresolved=$unresolvedd['unresolved'];


$result = mysqli_query($link, $queryyy);
 mysqli_close($link);

 ?> 

<html>
    <head>
        <title>Closed TTS</title>
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">
<script src="min.js"></script>
<link href="min.css" rel="stylesheet"/>
<link href="adminn.css" rel="stylesheet" type="text/css">
<style type="text/css">
        table{
    width: 100%;
    
   }

thead, tbody, tr, td, th { display: block; }

tr:after {
    content: ' ';
    display: block;
    visibility: hidden;
    clear: both;
}

thead th {
    height: 60px;


    /*text-align: left;*/
}

tbody {
    height: 170px;
    overflow-y: auto;
    margin-right:50px;
}

thead {
    /* fallback */
}


tbody td, thead th {
    width: 140px;
     height: 80px;
    float: left;
}
    </style>
 </head>
<body>

 
   <?php   

  if(mysqli_num_rows($result) > 0)
            {
              ?>  

<table id ="tblData" class="table  table-bordered" style="margin-top: 160px;position: fixed;">
    <thead>
        <tr>   
            <th Style="height:40px;">Branch Name</th>
            <th Style="width:150px;height:40px;">Service Number</th>
            <th Style="width:160px;height:40px;">TT</th>
              <th Style="width:140px;height:40px;">Registerd date</th>
              <th Style="width:130px;height:40px;">Closed date</th>
            <th Style="width:250px;height:40px;">Status</th>
            <th Style="width:260px;height:40px;">Remark</th>
                    </tr>  
    </thead>
    <tbody>


        <?php   

                   while($row = mysqli_fetch_array($result))  
                          {  
                          ?>  
                          <tr>  
                               <td><?php echo $row['0']; ?></td>  
                               <td Style="width:150px;"><?php echo $row['1']; ?></td>  
                               <td Style="width:160px;"><?php echo $row['2']; ?></td>  
                               <td Style="width:140px;"><?php echo $row['3']; ?></td>
                               <td Style="width:130px;"><?php echo $row['4']; ?></td>  
                               <td Style="width:250px;">
            <div style="max-width:250px; height:60px;  overflow-y: scroll;"><?php echo $row['5']; ?> 
             </div>
        </td>

                         <td Style="width:260px;">
            <div style="max-width:260px; height:60px;  overflow-y: scroll;"><?php echo $row['6']; ?> 
             </div>
        </td>

                          </tr>  

                          <?php                           
  
                          } 
                              ?>
<div align="center" style="margin-top: 410px;position: fixed;margin-left: 10px;">  
                    <button onclick="exportTableToExcel('tblData')">Export Table Data To Excel File</button>
                </div>  
        




    </tbody>
</table>



<div style="width:350px; height: 80px;margin-left: 500px;margin-top: 410px;position: fixed;">
<table id="report" style="width:100%; max-height: 60px;" class="table  table-bordered">
 <thead >
        <tr>   
            <th style="width:80px; height:35px;">Totall</th>
            <th style="width:80px; height:35px;">Closed</th>
            <th style="width:80px; height:35px;">Pending</th>
              
                    </tr>  
  </thead>
<tr>  
          <td style="width:80px; height:35px;"><?php echo $all; ?></td>  
          <td style="width:80px; height:35px;"><?php echo $resolved; ?></td>  
      <td style="width:80px; height:35px;"><?php echo $unresolved; ?></td>  
       </tr> 
</table>
</div>
</body>

  <?php 
}
else
{
    echo " <p style='margin-left:100px;color:red;'>No Report found</p>"; 
}
                          ?>

 
                 
<?php
}

 ?>




<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="all_individual_tts.css" rel="stylesheet" type="text/css">
    <title>All individual tts</title>
</head>
<body>
  <div  id ="calander"class="container" style="height: 150px;width:300px;position: fixed;margin-left: 600px;background-color: lightseagreen;margin-top: 200px;">

    <form  action="" method="post">
      <div>
             
             <select name="branch_name"  required />
             <option value="" selected disabled hidden>Please select ...</option>
            <?php while($row1 = mysqli_fetch_array($result111)):;?>
            <option value="<?php echo $row1[0];?>"><?php echo $row1[0]; ?>  
            </option>

            <?php endwhile;   ?>
        </select >
  </div>



<div>
                        <label for="ttt">FROM:</label> <input style="width:205px;" type="date" id="txt_uname" name="sdate" placeholder="tt reg date" required />
                    </div>
                     
                    <div>
                        <label for="tt">TO:</label><input style="width:205px;margin-left: 30px;" type="date"  id="txt_uname" name="edate" placeholder="tt reg date" required/>
                    </div>
     

<div style="padding: 0px;margin-top: 0px;border-radius: 10px;">
        <button type="submit" id="but_submit" name="submitt" style="width: 150px;margin-left: 75px;border-radius: 10px;background-color: rgb(72,79,92);">Each Branch</button>
        </div>
    </form>
  </div>
</body>

</html>

</html>





<script>
   function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    // Specify file name
    filename = 'individual branch report.xls';
    
    // Create download link element
    downloadLink = document.createElement("a");
    
    document.body.appendChild(downloadLink);
    
    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
    
        // Setting the file name
        downloadLink.download = filename;
        
        //triggering the function
        downloadLink.click();
    }
}
</script>