<?php       
	  session_start    ();      // starts session
	  include_once     (  "dbinfo.php"    );
	  include_once     (   "common.php"   );
	  global $db_con, $logged_in, $FirstName;
	  user_login_check (); //Checks the login status	
// This routine is invoked whenever general-data   	is to be entered
$cpfno=isset($_SESSION['cpfno']) ? $_SESSION['cpfno'] : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Create New User</title>
<link href="scripts/css/newuser.css" rel="stylesheet" type="text/css" />
<link rel="icon" href="ongclogo1.jpg" type="image/x-icon" />
  
</head> 


<body> 
<a href="adminhome.php">Home</a>
<br />   


<img src="ongclogo1.jpg" alt="ONGC" height="80px" width="80px">
<h3 style="color:darkblue" align="center" >Create New User Form</h3>




<div class="two">
<p>

<?php       

//This routine is called  whenever the 'admin' user wants to create a new user to the RIS system

if($_SERVER['REQUEST_METHOD'] == 'POST') {

 	 $cpfno 		=           trim($_POST   [   "cpfno"          ]);
	 $FirstName	    =           trim($_POST   [   "FirstName"      ]);
	 $LastName	    =           trim($_POST   [   "LastName"       ]);
	 $Designation   =           trim($_POST   [   "Designation"    ]);
	 $Privilege	    =           trim($_POST   [   "Privilege"      ]);
	 $Password	    =    md5   ($_POST   [   "Password"       ]);
	
// $host    =   "localhost"; 
// $user    =   "root"; 
// $pass    =   ""; 
// $db      =   "gmskolkata"; 
      // open connection 
// $db_con = mysqli_connect($host, $user, $pass) or die ("<br /> Unable to connect!"); 
//echo   "<br />Connected to tdms.";

// STEP 2. Select database 
// mysqli_select_db($db_con, $db) or die ("<br /> Unable to select database!"); 
//echo   "<br />Selected ris.";
// Initialise flags, for tables; Re-set the flag values, if the tables are updated

// STEP 3. Insert data in various tables
	 include_once (    "dbinfo.php"    );	   

echo   '<br /><br /><p2>User Data Repository</p2><br /><br />';
echo   "<br />";
echo   "<br />";


	 $query   = "INSERT INTO UserDetails 
	                     (  CPFNo, 
							FirstName,
							LastName, 
							Designation, 
							Password, 
							Privilege     ) 
					VALUES 
						  ( '$cpfno', 
							'$FirstName',
							'$LastName', 
							'$Designation',
							'$Password', 
							'$Privilege'   ) ";
	
	 $result  = mysqli_query   ($db_con, $query);
	 
   

	 
	 
	 if($result ){	
			echo   $FirstName." ".$LastName.", ".$Designation." With CPF No. ".$cpfno." Has Been Successfully Registered."; 
		
	 } 
	 else {
			$sql         =    "SELECT * FROM UserDetails WHERE CPFno='$cpfno'";
			$result      =    mysqli_query($db_con, $sql);
			if ($myrow   =   mysqli_fetch_array   ($result))  		{
				echo "User With CPF No. ".$cpfno." Is Already Registered As ".$Privilege.".";

			}
			else  	{	echo   "Error: Registration Failed. ".mysqli_error($db_con).".";	}
	 }
	 
// mysqli_close ($db_con);    
echo "<br /><br /> <br /><br /><br />";
}
?>
<form method="post" action="registeruser.php" >

<tr><td><input class="submit" type="submit" name="submit" value="Create Another User" /></td>
          </tr>

</form>
<br /><br /><br /><br /><br /><br /><br /><br /></p></div>
</body>

</html>
