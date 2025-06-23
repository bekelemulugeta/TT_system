
<?php

include_once("admin.php");
include_once("config.php");

$date=date("Y-m-d");
$query = "SELECT department FROM `dgb` ORDER BY id ASC";
$result111 = mysqli_query($link, $query);

$query="SELECT Name FROM `user` where username='".$_SESSION['login_user']."' " ;
$resultr = mysqli_query($link, $query);
$row1 = mysqli_fetch_array($resultr);



$staf=$row1[0];


if(isset($_POST['submitt'])){

$branch_namee = mysqli_real_escape_string($link, $_POST['branch']);


$channel= mysqli_real_escape_string($link,$_POST['channel']);
$problem= mysqli_real_escape_string($link,$_POST['problem']);

$status= mysqli_real_escape_string($link,$_POST['status']);


$sql = "INSERT INTO support (branch,datee,channel,problem, status,it_staff) VALUES ('$branch_namee', '$date', '$channel' ,'$problem', '$status', '$staf')";

if(mysqli_query($link, $sql)){
    echo "<script>
alert('Added Successfully');
window.location.href='support.php';
</script>";
  
} 

else{
   echo "<script>
alert('something worong try again.  ');
window.location.href='support.php';
</script>";
}
 

}




?>

<html>
    <head>
        <title>support</title>
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="utf-8">

        <style type="text/css">
        .FBLabel, .FBInput {
    display:block;
    width:200px;
   height:70px;
    float:left;
    margin-bottom:10px;
margin-left:-50px;
}


        input {
  border: 2px solid currentcolor;
}
input:invalid {
  border: 2px dashed red;
}
input:invalid:focus {
  background-image: linear-gradient(pink, lightgreen);
}





    </style>
   
    </head>

    <body>





 <div class="container" >
        <form method="post" action="">
            <div id="uatbca" style="margin-top: 10px;margin-left:-190px;position: fixed;height: 288px;width: 400px;border: 1px solid gray;border border-radius: 3px;">
                  
                    <div> 

<label for="branch" style="font-size:15px;color:green;margin-left: 12px;">From:</label>       
            <select name="branch"  required   style="font-size:18px;color:black;margin-left: 20px;width: 228px;margin-top: 10px;" />
             <option value="" selected disabled hidden>Please select ...</option>
            <?php while($row1 = mysqli_fetch_array($result111)):;?>
            <option value="<?php echo $row1[0];?>"><?php echo $row1[0]; ?>  
            </option>

            <?php endwhile;  
             ?>
             

        </select>
      </div> 



          <div> 
              <label   style="font-size:15px;color:green;margin-left: 10px;" for="Channel">Channel:</label>      
            <select style="font-size:15px;margin-left: 0px;width: 228px;margin-top: 10px;" name="channel" required />

              
             <option value=" Office Phone " </option>
                <label for="Office Phone">Office Phone</label>
                 

                  
            <option value=" Zimbra " </option>
<label for="Zimbra">Zimbra</label>
               
    
            <option value="Private Phone" </option>
            <label for="Private Phone">Private Phone</label>
 
 <option value="In Person" </option>
        <label for="In person">In Person</label>       


                
        </select > 
      </div> 

        </select>

<div style="margin-top: 50px;margin-left: 10px;position: fixed;"> 

<textarea id="texta" rows="6" cols="40" name="problem" type="text"  required placeholder="write the problem..."></textarea>
                    </div>






      
          <div> 
              <label for="status" style="font-size:15px;color:green;margin-left: 12px;">Status:</label>      
            <select style="font-size:15px;margin-left: 10px;width: 228px;margin-top: 10px;"  name="status" required />

             <option value="Done" </option>
                  <label for="Done">Done</label>
         
           

            <option value="Pending" </option>
                
                <label for="Pending">Pending</label>
        </select > 
      </div> 
 </div>










                <div>
                        <input style="font-size:17px;color:green;margin-left: -50px;margin-top: 300px;" type="submit" value="Submit" name="submitt" id="but_submit" ></input>
                    </div>
</div >
            </form>
          </div>








    </body>









</html>



<script type="text/javascript">
var txtarea = document.getElementById("texta");
txtarea.addEventListener("input", function() {
  var b4 = txtarea.value;
  txtarea.value = txtarea.value.replaceAll("%", "");

  


  if(txtarea.value != b4){
    console.log("You tried to enter %");
  }
})


</script>


<script type="text/javascript">
var txtarea = document.getElementById("texta");
txtarea.addEventListener("input", function() {
  var b4 = txtarea.value;
  txtarea.value = txtarea.value.replaceAll(">", "");

  


  if(txtarea.value != b4){
    console.log("You tried to enter >");
  }
})


</script>



<script type="text/javascript">
var txtarea = document.getElementById("texta");
txtarea.addEventListener("input", function() {
  var b4 = txtarea.value;
  txtarea.value = txtarea.value.replaceAll("<", "");

  


  if(txtarea.value != b4){
    console.log("You tried to enter ,<");
  }
})


</script>

<script type="text/javascript">
var txtarea = document.getElementById("texta");
txtarea.addEventListener("input", function() {
  var b4 = txtarea.value;
  txtarea.value = txtarea.value.replaceAll("/", "");

  


  if(txtarea.value != b4){
    console.log("You tried to enter /");
  }
})
</script>

<?php

include_once("add_department.php");

include_once("pending_update.php");
include_once("pending.php");

?>




