<?php
// including the database connectivity code
   include_once     (    "dbinfo.php"     );
   
   function user_login_check()
	{
	
	global $db_con, $logged_in, $FirstName, $Privilege, $cpf, $out_file;
	global $logged_in_user_priv;
    global $gmskolkata_user_id;
	
	
		
		//if(isset($_COOKIE   [   "Userid"   ]))
		if(isset    ($_SESSION    [   "cpfno"    ])) 		{
			//$result = mysql_query($sql1 ,$db_con);
			$logged_in = 'true';
			$CPFNo     = $_SESSION   [   "cpfno"];		
			$result    = mysqli_query ( $db_con, "SELECT * FROM UserDetails where CPFNo = '$CPFNo'" );
			
			
			if ($myrow = mysqli_fetch_array  (  $result))  	{
				$FirstName            =  $myrow     [  "FirstName"  ];
				$Privilege            =  $myrow     [  "Privilege"  ];
				$cpf                  =  $_SESSION  [  "cpfno"      ];
				$logged_in_user_priv  =  $Privilege;
			    $gmskolkata_user_id          =  $cpf;		
                				
// Generate output file for storing the displayed data. File name is concatenation of
// (1) User-Login name, (2) IP-Address of the machine from which login was made (3) File-identifier GmsKolkata_data.doc (constant)
//  The file is stored in GmsKolkata_reports directory
				$system_name          =   $_SESSION  [  'ipadd'];
// The file-type	was changed to .xls form, so as to display/print in excel sheet format  -- 22-12-2010 --
//              $out_file             =   "GmsKolkata_reports/"  .  $FirstName   .  "_"  .  $system_name  .  "_GmsKolkata_data.doc";
                $out_file             =   "gmskolkata_reports/"  .  $FirstName   .  "_"  .  $system_name  .  "_gmskolkata_data.xls";

			}
	    } 
	    else { 
			$logged_in = 'false';	
			 }
		
}

function user_logout()
{
	setcookie  (  'CPFno',           '',     time( ) - 1);
	setcookie  (  'regusersite',     '',     time( ) - 1);
	setcookie  (  'access_level',    '',     time( ) - 1);
	header     (  "Location: login.php?loggedout=true");
	

	
	exit;
}
?>
