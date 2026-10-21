<html>
<head>
<meta content="IE=8" http-equiv="X-UA-Compatible" />
<style type="text/css">
h1 {color:red;}
 
h3 {color:blue;}
p {color:green;
margin-left:20px;}
</style>
</head>
<body>

<h1 align="center">Development and Administration</h1>
<h3 align="center"> Software Section, RCC, Kolkata</h3>
<?php
if ($logged_in_user_priv="admin"){
?>
<a href="adminfirstpage.php"> Back </a>
<?php
}
else {
?>
<a href="firstpage.php"> Back </a>
<?php
}
?>

<hr />
<h3 align="left"> Contacts:</h3>
   <p>  Mr. D.N. Bain, GM(Prog) - 9999999999</p>
<p>  Mrs. Rakhi Kumari, Geophysicist (S) - 9999999999 (Most Constructive Inputs)</p>
</body>
</html>
