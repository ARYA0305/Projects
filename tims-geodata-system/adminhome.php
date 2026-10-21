<?php       
	  session_start    ();      // starts session
	  include_once     ("dbinfo.php");
	  include_once     ("common.php");
	  global $db, $logged_in, $FirstName;
	  include_once ("user_login_check.php");  //Checks the login status	
?>
 <HTML>
  <HEAD>
<title>ONGC Kolkata : TIMS</title>
  <meta http-equiv="X-UA-Compatible" content="IE=8" />
       <LINK href="scripts/css/dropdownnew.css" rel="stylesheet" type="text/css" />
 <link rel="icon" href="ongclogo1.jpg" type="image/x-icon" />
  </HEAD> 
  <BODY class="myBody">
 <br/>
<img src="ONGCNewLogo.jpg" alt="ONGC" height="80px" width="179px">
<?php $cpfno=$_SESSION    [  'cpfno'       ] ;
	 // STEP 1. Establish connectivity to the database

$host    =   "localhost:3307"; 
$user    =   "root"; 
$pass    =   ""; 
$db      =   "gmskolkata"; 
      // open connection 
$connection = mysqli_connect($host, $user, $pass, $db);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

$result = mysqli_query($connection, "SELECT * FROM UserDetails WHERE CPFNo='$cpfno'");

$row = mysqli_fetch_assoc($result);
//echo $row['Designation'];
        ?>


<table class="user" align="right">
<tr>

<td><?php echo $row['FirstName']." ".$row['LastName'].", ".$row['Designation'].", ".$row['CPFNo']; ?></td>

</tr>
<tr align="center">

<td><a href="resetpw.php">Change Password</a> <a href="logout.php">Logout</a></td>

</tr>
</table>

<h3 style="color:darkblue" align="center" >Geo-Data Management System</h3>



<br /><br />
<div class="list">
<ul id="nav">
    <li><a href="#">Home</a>
        <ul class="nav first">
            <li><a href="contactus.php">Contact Us</a></li>
        </ul>
    </li>
    <li><a href="#">Geo-Data Entry Form</a>
		        <ul class="nav first">
	            <li><a href="form.php">New</a></li>
	            
				</ul>
	</li>
    <li><a href="#">Search Geo-Data</a>
	  <ul class="nav first">
	            <li><a href="search.php">Search</a>
				  
	</ul>
	</li>
	
        <!--<li><a href="#">Support	Files </a>
            <ul class="nav first">
	    <li><a href="upload_support_files.php">Upload</a></li>
	    <li><a href="view_upload_support_files.php">Download</a></li>
	    </ul>
        </li>-->
	<li><a href="#">Reports</a>
	<ul class="nav first">
	            <li><a href="get_2d_3d_data.php">Get 2D/3D R/P</a>
				  </li>
	            <li><a href="get_raw_2d_3d.php">Get 2D/3D R</a>
				  </li>
				  <li><a href="get_proc_2d_3d.php">Get 2D/3D P</a>
				  </li>
				  <li><a href="download_all_yearwise.php"> Date Received</a>
				  </li>
				  <li><a href="cart_wise_query.php"> Media-Wise</a>
				  </li>
				  <li><a href="2dproclistexcel.php"> 2D Processed </a>
				  </li>
				  <li><a href="3dproclistexcel.php"> 3D Processed</a>
				  </li>
				  <li><a href="2drawofflistexcel.php"> 2D Raw Off-Shore </a>
				  </li>
				  <li><a href="3drawofflistexcel.php"> 3D Raw Off-Shore</a>
				  </li>
				  <li><a href="2drawonlistexcel.php"> 2D Raw On-Land </a>
				  </li>
				  <li><a href="3drawonlistexcel.php"> 3D Raw On-Land</a>
				  </li>
				  <li><a href="3drawfullexcel.php"> 3D Raw Full</a>
				  </li>
				  <li><a href="2drawfullexcel.php"> 2D Raw Full</a>
				  </li>
				  <li><a href="3d_proc_more_edit.php"> Search 3D Proc</a>
				  </li>
				  <li><a href="2D_3D_VSP_RAW.php"> VSP Raw </a>
				  </li>
				  <li><a href="2d_3d_vspproc.php"> VSP Processed</a>
				  </li>
	</ul>
	
	<li><a href="#">MIS</a>
	
	<ul class="nav first">
	            <li><a href="#">Agencies</a>
				 <ul class="nav">
				    <li><a href="2d_3d_oths_data.php">New</a></li>
				    <li><a href="send_data_oths.php">Search/Edit</a></li>
				<li><a href="#">Reports</a>
				<ul class="nav">
					<li><a href="sent_data_oths_excel.php">Date-Wise</a></li>
					<li><a href="sent_data_oths_agency.php">Agency-Wise</a></li>
				</ul>
				</li>				    
				</ul>
			</li>
			<!--	<li><a href="#">Processed Data</a>
				  <ul class="nav">
				    <li><a href="rcvd_data_proc.php">Search/Report</a></li>
				    	 
				  </ul>
				</li>
				<li><a href="#">Raw Data</a>
				  <ul class="nav">
				    <li><a href="rcvd_data_raw.php">Search/Report</a></li>
				    	 
				  </ul>
				</li>-->
				<li><a href="#">Monthly Report</a>
				  <ul class="nav">
				    <li><a href="MIS_reports_page_01.php">Report</a></li>
				    	 
				  </ul>
				</li>
	</ul>
	</li>			
	<li><a href="#">Receive Data</a>
	<ul class="nav first">
	            <li><a href="receive.php">Receive</a></li>
	            <li><a href="recieve_search.php">Search</a></li>
				<li><a href="recieve_query.php">Date-Wise Report</a></li></ul>
				
	</li>
    <li><a href="#">Admin Tools</a>
	<ul class="nav first">
	            <li><a href="registeruser.php">New User</a>
				  <!--<ul class="nav">
				    <li><a href="user_register.php">New</a></li>
				    <li><a href="#">Edit</a></li>
				    <li><a href="#">Search</a></li>
				    <li><a href="#">Delete</a></li>
				  </ul>-->
				</li>
	      <!--      <li><a href="#">Type of Data</a>
				  <ul class="nav">
				    <li><a href="#">New</a></li>
				    <li><a href="#">Edit</a></li>
				    <li><a href="#">Search</a></li>
				    <li><a href="#">Delete</a></li>		    
	              </ul>
	            </li>
                <li><a href="#">Basin</a>
				  <ul class="nav">
				    <li><a href="#">New</a></li>
				    <li><a href="#">Edit</a></li>
				    <li><a href="#">Search</a></li>
				    <li><a href="#">Delete</a></li>		    
	              </ul>
	            </li>	
                <li><a href="#">Format</a>
				  <ul class="nav">
				    <li><a href="#">New</a></li>
				    <li><a href="#">Edit</a></li>
				    <li><a href="#">Search</a></li>
				    <li><a href="#">Delete</a></li>		    
	              </ul>
	            </li>	
				<li><a href="#">Type of Media</a>
				  <ul class="nav">
				    <li><a href="update_media_codes.php">New</a></li>
				    <li><a href="#">Edit</a></li>
				    <li><a href="#">Search</a></li>
				    <li><a href="#">Delete</a></li>		    
	              </ul>
	            </li>
				<li><a href="#">Location</a>
				  <ul class="nav">
				    <li><a href="#">New</a></li>
				    <li><a href="#">Edit</a></li>
				    <li><a href="#">Search</a></li>
				    <li><a href="#">Delete</a></li>		    
	              </ul>
	            </li>
				<li><a href="#">Proc Type</a>
				  <ul class="nav">
				    <li><a href="update_proc_types.php">New</a></li>
				    <li><a href="#">Edit</a></li>
				    <li><a href="#">Search</a></li>
				    <li><a href="#">Delete</a></li>		    
	              </ul>
	            </li>-->
	</ul>
	</li>
	
	
</ul>
<br class="ClearFloat"/>
<br/>
<br/><br/><br/><br/></div>

</body>

</html>
