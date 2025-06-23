
<?php
require_once("adminn.php");

$rd='0000-00-00';
$td=date("Y-m-d");
$rb="";
$query = "SELECT branch_name FROM `service_info` ORDER BY branch_name ASC";
$resultbn = mysqli_query($link, $query);


$query = "SELECT imei FROM `4g` EXCEPT (SELECT imei FROM `4g_loan`where return_date='".$rd."')";
$resultsn = mysqli_query($link, $query);


if(isset($_POST['but_submit'])){

$sn= mysqli_real_escape_string($link,$_POST['old_sn']);
$bn= mysqli_real_escape_string($link,$_POST['branch_name']);
$takenby=mysqli_real_escape_string($link,$_POST['person']);



$sql = "INSERT INTO 4g_loan (imei,branch,date_taken,taken_by,return_date,return_by) VALUES ('$sn','$bn','$td','$takenby','$rd','$rb')";


if(mysqli_query($link, $sql)){
    echo "<script>
alert('Added Successfully');
window.location.href='4g_loan_admin.php';
</script>";
  
} 

else{
   echo "<script>
alert('something worong try again.  ');
window.location.href='4g_loan_admin.php';
</script>";
}
 

}

?>


<html>
    <head>
        <title> 4G Loan</title>
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="min.js"></script>
   <script type="text/javascript" src="all.js"></script>
<script type="text/javascript" src="jquery.inputmask.bundle.js"></script>
   
   
    </head>

    <body>
      <div  >
         <form method="post"  action="">
           <div style="width: 375px;height: 195px;border: 1px solid green; border-radius: 3px;padding: 5px;margin-top: 245px;margin-left: 80px;position: fixed;">
                  <label style="font-size:18px;color:blue;margin-left: 100px;"> <u> Loan:  </u></label>
                  <br>
                    
                    <div>

                      <label for="old_sn" style="font-size:15px;color:green;margin-left: 0px;">Choose IMEI:</label>      
            <select name="old_sn"  required style="font-size:18px;color:black;margin-left: 15px;width: 228px;"/>
              <option value="" selected disabled hidden>Please select ...</option>
            <?php while($row1 = mysqli_fetch_array($resultsn)):;?>
            <option value="<?php echo $row1[0];?>"><?php echo $row1[0]; ?>  
            </option>

            <?php endwhile;  
             ?>
</select>
                    
                        
                    </div>

   <label for="branch" style="font-size:15px;color:green;">Choose Branch:</label>      
            <select name="branch_name"  required />
           <option value="" selected disabled hidden>Please select ...</option>
            <?php while($row1 = mysqli_fetch_array($resultbn)):;?>
            <option value="<?php echo $row1[0];?>"><?php echo $row1[0]; ?>  
            </option>

            <?php endwhile;   ?>

        </select>


                    <div>
                    
                         <label for="im" style="font-size:15px;color:green;margin-left: 0px;">Taken By:</label>
                        <input type="text" class="textbox" id="txt_sn" name="person"  style="font-size:15px;margin-left: 40px;width: 228px;"required/>
 

                 

                    <div>
                    
                        <input type="submit"  value="Loan" name="but_submit" id="but_submit"  style="font-size:18px;color:white;margin-left: 185px;height: 25px;margin-top: 0px;background-color: black; border-radius: 3px;"/>
        
  
 </div>
            </form>
             <div>
    </body>
</html>


<script type="text/javascript">
var txtarea = document.getElementById("txt_sn");
txtarea.addEventListener("input", function() {
  var b4 = txtarea.value;
  txtarea.value = txtarea.value.replaceAll("%", "");

  


  if(txtarea.value != b4){
    console.log("You tried to enter %");
  }
})
</script>


<script type="text/javascript">
var txtarea = document.getElementById("txt_sn");
txtarea.addEventListener("input", function() {
  var b4 = txtarea.value;
  txtarea.value = txtarea.value.replaceAll(">", "");

  


  if(txtarea.value != b4){
    console.log("You tried to enter >");
  }
})
</script>
<script type="text/javascript">
var txtarea = document.getElementById("txt_sn");
txtarea.addEventListener("input", function() {
  var b4 = txtarea.value;
  txtarea.value = txtarea.value.replaceAll("<", "");

  


  if(txtarea.value != b4){
    console.log("You tried to enter <");
  }
})
</script>

<script type="text/javascript">
var txtarea = document.getElementById("txt_sn");
txtarea.addEventListener("input", function() {
  var b4 = txtarea.value;
  txtarea.value = txtarea.value.replaceAll("/", "");

  


  if(txtarea.value != b4){
    console.log("You tried to enter /");
  }
})
</script>
