<?php       
session_start();
include_once("dbinfo.php");
include_once("common.php");
global $db_con, $logged_in, $FirstName;
user_login_check(); 
$cpfno=$_SESSION['cpfno'];
$logged_in_user_priv=$_SESSION['Privilege'];
$host    =   "localhost:3307"; 
$user    =   "root"; 
$pass    =   ""; 
$db      =   "gmskolkata"; 
$db_con = mysqli_connect($host, $user, $pass) or die ("<br /> Unable to connect!"); 
mysqli_select_db  ($db_con, $db) or die ("<br /> Unable to select database!"); 

$edit_id = mysqli_real_escape_string($db_con, $_GET['id']);
$edit_result = mysqli_query($db_con, "SELECT * FROM SeismicData WHERE db_id='$edit_id'");
$data = mysqli_fetch_assoc($edit_result);
echo $data[""];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Geo-Data Entry Form</title>
<link href="scripts/css/form.css" rel="stylesheet" type="text/css" />
<link rel="icon" href="ongclogo1.jpg" type="image/x-icon" />
<script>


function check_numeric  (field,  alert_txt)  {
    with (field) {
        var value;
            if (value <= 0 || value > 0 ) return true;
            else {alert (alert_txt);      return false;}
    }
} 

function validate_name(field, alerttxt){
with (field){
if(value == null || value == "")
{
alert(alerttxt);
return false;
}
else
{       
return true;     
}
}
}

function validate_year (field,   alerttxt) {
    with (field)  {
        if (value >= 1957 &&  value <= 2030) { return true;     }
        else {    alert(alerttxt);             return false;    }
     }
}

function validate_form(thisform){
with(thisform){
if ( validate_name ( basin,       "Select A Basin." )                           == false ) { basin.focus();       return false; }
if ( validate_name ( block,       "Enter The Name Of The Block." )              == false ) { block.focus();       return false; }
if ( validate_name ( cart_no,     "Enter The Cartridge No. Of Geo-Data Media." )      == false ) { cart_no.focus();      return false; }
if ( validate_name ( location,    "Enter The Location Of Geo-Data Media." )     == false ) { location.focus();    return false; }
if ( validate_name ( rack_no,     "Enter The Rack No. Of Geo-Data Media." )     == false ) { rack_no.focus();     return false; }
if ( validate_name ( shelf_no,    "Enter The Shelf No. Of Geo-Data Media." )    == false ) { shelf_no.focus();    return false; }
if ( validate_name ( box_no,      "Enter The Box No. Of Geo-Data Media." )      == false ) { box_no.focus();      return false; }
}  
}

function startupedit(){
    var dbData = <?php echo json_encode($data); ?>;
    if (dbData['acq_type'] && dbData['survey_type'] && dbData['data_type']){
        document.getElementsByName('acq_type')[0].value = dbData['acq_type'];
        document.getElementsByName('survey_type')[0].value = dbData['survey_type'];
        document.getElementsByName('data_type')[0].value = dbData['data_type'];
        createForm();
    }

    setTimeout(function() {
        for (var key in dbData) {
            if (key !== "acq_type" && key !== "survey_type" && key !== "data_type" && dbData[key] !== null && dbData[key] !== ""){
                
                if (key === "output") {
                    var options = document.getElementById('output').options;
                    var outputArr = dbData[key].replace(" and ", ", ").replace(/\.$/, "").split(", ");
                    for (var k=0; k<options.length; ++k){
                        for (var m=0; m<outputArr.length; ++m){
                            if (options[k].value == outputArr[m].trim()){
                                options[k].selected = true;
                            }
                        }
                    }
                    continue;
                }

                if (key === "cart_no") {
                    if (dbData[key].indexOf(" To ") !== -1) {
                        document.getElementById('CRCheck').checked = true;
                        cartRCheck();
                        var parts = dbData[key].split(" To ");
                        document.getElementsByName("cart_no_from")[0].value = parts[0];
                        document.getElementsByName("cart_no_to")[0].value = parts[1];
                    } else {
                        document.getElementById('CSCheck').checked = true;
                        cartSCheck();
                        document.getElementsByName("cart_no")[0].value = dbData[key];
                    }
                    continue;
                }

                if (key === "line_no") {
                    if (dbData[key].indexOf(" To ") !== -1) {
                        document.getElementById('LRCheck').checked = true;
                        lineRCheck();
                        var parts = dbData[key].split(" To ");
                        if (document.getElementsByName("line_no_from").length > 0) document.getElementsByName("line_no_from")[0].value = parts[0];
                        if (document.getElementsByName("line_no_to").length > 0) document.getElementsByName("line_no_to")[0].value = parts[1];
                    } else {
                        document.getElementById('LSCheck').checked = true;
                        lineSCheck();
                        if (document.getElementsByName("line_no").length > 0) document.getElementsByName("line_no")[0].value = dbData[key];
                    }
                    continue;
                }

                if (key === "swath_no") {
                    if (dbData[key].indexOf(" To ") !== -1) {
                        document.getElementById('SRCheck').checked = true;
                        swathRCheck();
                        var parts = dbData[key].split(" To ");
                        if (document.getElementsByName("swath_no_from").length > 0) document.getElementsByName("swath_no_from")[0].value = parts[0];
                        if (document.getElementsByName("swath_no_to").length > 0) document.getElementsByName("swath_no_to")[0].value = parts[1];
                    } else {
                        document.getElementById('SSCheck').checked = true;
                        swathSCheck();
                        if (document.getElementsByName("swath_no").length > 0) document.getElementsByName("swath_no")[0].value = dbData[key];
                    }
                    continue;
                }

                if (key === "date_rcvd_inhouse") {
                    var dParts = dbData[key].split('-'); 
                    if (dParts.length === 3 && dParts[0].length === 2) { 
                        document.getElementsByName(key)[0].value = dParts[2] + '-' + dParts[1] + '-' + dParts[0];
                    } else {
                        document.getElementsByName(key)[0].value = dbData[key];
                    }
                    continue;
                }

                if (document.getElementsByName(key).length > 0) {
                    document.getElementsByName(key)[0].value = dbData[key];
                }
            }
        }
        
        if (dbData['basin']) document.getElementById("basin").value = dbData['basin'];
        if (dbData['block']) document.getElementById("block").value = dbData['block'];
        if (dbData['area']) document.getElementById("area").value = dbData['area'];

    }, 100);
}


function lineRCheck(){
if (document.getElementById('LRCheck').checked){
document.getElementById('ifLF').style.visibility='visible';
document.getElementById('ifLT').style.visibility='visible';
document.getElementById('ifLS').style.visibility='hidden';
document.getElementById('LSCheck').checked = false;
}
}

function lineSCheck(){
if (document.getElementById('LSCheck').checked){
document.getElementById('ifLS').style.visibility='visible';
document.getElementById('ifLF').style.visibility='hidden';
document.getElementById('ifLT').style.visibility='hidden';
document.getElementById('LRCheck').checked = false;
}
}
function cartRCheck(){
if (document.getElementById('CRCheck').checked){
document.getElementById('ifCF').style.visibility='visible';
document.getElementById('ifCT').style.visibility='visible';
document.getElementById('ifCS').style.visibility='hidden';
document.getElementById('CSCheck').checked = false;

}
}

function cartSCheck(){
if (document.getElementById('CSCheck').checked){
document.getElementById('ifCS').style.visibility='visible';
document.getElementById('ifCF').style.visibility='hidden';
document.getElementById('ifCT').style.visibility='hidden';
document.getElementById('CRCheck').checked = false;
}
}

function swathRCheck (){
if (document.getElementById('SRCheck').checked){
document.getElementById('ifSF').style.visibility='visible';
document.getElementById('ifST').style.visibility='visible';
document.getElementById('ifSS').style.visibility='hidden';
document.getElementById('SSCheck').checked = false;
}
}

function swathSCheck (){
if (document.getElementById('SSCheck').checked){
document.getElementById('ifSS').style.visibility='visible';
document.getElementById('ifSF').style.visibility='hidden';
document.getElementById('ifST').style.visibility='hidden';
document.getElementById('SRCheck').checked = false;
}
}

function add_option(){
var d = document.getElementById("output");
var option = document.createElement("option");
option.text = document.getElementById("option").value;
option.value = document.getElementById("option").value;
d.add(option,d[1]);
d.scrollTo(0,0);
document.getElementById("option").value = "";
}

function clearR(){
document.getElementsByName("remarks")[0].value = "";
document.getElementsByName("reports")[0].value = "";
document.getElementsByName("iso_up")[0].value = null;
}

function dynamicForm1(){
if ((document.form.data_type.value != "") && (document.form.acq_type.value != "") && (document.form.survey_type.value != "")){
clearR();
createForm();
}
else if ((document.form.data_type.value != "") && (document.form.acq_type.value != "") && (document.form.survey_type.value == "")){
alert("Select Type of Survey.")
}

}

function dynamicForm2(){
if ((document.form.data_type.value != "") && (document.form.acq_type.value != "") && (document.form.survey_type.value != "")){
clearR();
createForm();
}
else if ((document.form.data_type.value != "") && (document.form.acq_type.value == "") && (document.form.survey_type.value != "")){
alert("Select Type of Acquisition.")
}

}

function dynamicForm3(){
if (document.form.data_type.value != ""){
if (document.form.acq_type.value == ""){
alert("Select Type of Acquisition.")
}
else if (document.form.survey_type.value == ""){
alert("Select Type of Survey.")
}
}
if ((document.form.data_type.value != "") && (document.form.acq_type.value != "") && (document.form.survey_type.value != "")){
clearR();
createForm();
}
}






function createForm(){

var basin = document.getElementById("basin").selectedIndex;
var block = document.getElementById("block").value;
var area = document.getElementById("area").value;



if ((document.form.acq_type.value == "Onland") && (document.form.survey_type.value == "2D") && (document.form.data_type.value == "Raw")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="line">
<td align="right">Line No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:lineRCheck();" name="line" value="range" id="LRCheck"></td>
<td><div id="ifLF" style="visibility:hidden"><input type="text" name="line_no_from"></div></td>
<td><div id="ifLT" style="visibility:hidden"><input type="text" name="line_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:lineSCheck();" name="line" value="specify" id="LSCheck"></td>
<td><div id="ifLS" style="visibility:hidden"><input type="text" name="line_no"></div></td>

<td align="right">Channels:</td>
<td><input type="text" name="channels"/></td>

</tr></div>
<tr>


<td align="right">Shot Point (From):</td>
<td><input type="text" name="sp_no_from"/></td>
<td align="right">Shot Point (To):</td>
<td><input type="text" name="sp_no_to"/></td>
<td align="right">FFID (From):</td>
<td><input type="text" name="ffid_no_from"/></td>
<td align="right">FFID (To):</td>
<td><input type="text" name="ffid_no_to"/></td>


</tr>

        
</table>
`;
document.getElementById("acqType").selectedIndex = "1";
document.getElementById("surveyType").selectedIndex = "1";
document.getElementById("dataType").selectedIndex = "1";
document.getElementById("agency").innerHTML = "Acquiring Agency:";
document.getElementById("notext").innerHTML = "Investigation No.:";
}







else if ((document.form.acq_type.value == "Offshore") && (document.form.survey_type.value == "2D") && (document.form.data_type.value == "Raw")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="line">
<td align="right">Line No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:lineRCheck();" name="line" value="range" id="LRCheck"></td>
<td><div id="ifLF" style="visibility:hidden"><input type="text" name="line_no_from"></div></td>
<td><div id="ifLT" style="visibility:hidden"><input type="text" name="line_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:lineSCheck();" name="line" value="specify" id="LSCheck"></td>
<td><div id="ifLS" style="visibility:hidden"><input type="text" name="line_no"></div></td>

<td align="right">Channels:</td>
<td><input type="text" name="channels"/></td>

</tr></div>
<tr>


<td align="right">Shot Point (From):</td>
<td><input type="text" name="sp_no_from"/></td>
<td align="right">Shot Point (To):</td>
<td><input type="text" name="sp_no_to"/></td>
<td align="right">FFID (From):</td>
<td><input type="text" name="ffid_no_from"/></td>
<td align="right">FFID (To):</td>
<td><input type="text" name="ffid_no_to"/></td>


</tr>
<tr>


<td align="right">Streamers:</td>
<td><input type="text" name="streamer"/>
<td align="right">Seq. Range (From):</td>
<td><input type="text" name="seq_no_from"/></td>
<td align="right">Seq. Range (To):</td>
<td><input type="text" name="seq_no_to"/></td>  



</tr>

        
</table>






`;
document.getElementById("acqType").selectedIndex = "2";
document.getElementById("surveyType").selectedIndex = "1";
document.getElementById("dataType").selectedIndex = "1";
document.getElementById("agency").innerHTML = "Acquiring Agency:";
document.getElementById("notext").innerHTML = "Contract No.:";
}


else if ((document.form.acq_type.value == "Onland") && (document.form.survey_type.value == "3D") && (document.form.data_type.value == "Raw")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="swath">
<td align="right">Swath No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:swathRCheck();" name="swath" value="range" id="SRCheck"></td>
<td><div id="ifSF" style="visibility:hidden"><input type="text" name="swath_no_from"></div></td>
<td><div id="ifST" style="visibility:hidden"><input type="text" name="swath_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:swathSCheck();" name="swath" value="specify" id="SSCheck"></td>
<td><div id="ifSS" style="visibility:hidden"><input type="text" name="swath_no"></div></td>




<td align="right">Channels:</td>
<td><input type="text" name="channels"/></td>

</tr></div>
<tr>


<td align="right">Shot Point (From):</td>
<td><input type="text" name="sp_no_from"/></td>
<td align="right">Shot Point (To):</td>
<td><input type="text" name="sp_no_to"/></td>
<td align="right">FFID (From):</td>
<td><input type="text" name="ffid_no_from"/></td>
<td align="right">FFID (To):</td>
<td><input type="text" name="ffid_no_to"/></td>


</tr>
<tr>
    

<td align="right">Inline (From):</td>
<td><input type="text" name="inline_no_from"/></td>
<td align="right">Inline (To):</td>
<td><input type="text" name="inline_no_to"/></td>   
<td align="right">Crossline (From):</td>
<td><input type="text" name="xline_no_from"/></td>
<td align="right">Crossline (To):</td>
<td><input type="text" name="xline_no_to"/></td>


</tr>

    

    
</table>
`;
document.getElementById("acqType").selectedIndex = "1";
document.getElementById("surveyType").selectedIndex = "2";
document.getElementById("dataType").selectedIndex = "1";
document.getElementById("agency").innerHTML = "Acquiring Agency:";
document.getElementById("notext").innerHTML = "Investigation No.:";
}


else if ((document.form.acq_type.value == "Offshore") && (document.form.survey_type.value == "3D") && (document.form.data_type.value == "Raw")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="swath">
<td align="right">Swath No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:swathRCheck();" name="swath" value="range" id="SRCheck"></td>
<td><div id="ifSF" style="visibility:hidden"><input type="text" name="swath_no_from"></div></td>
<td><div id="ifST" style="visibility:hidden"><input type="text" name="swath_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:swathSCheck();" name="swath" value="specify" id="SSCheck"></td>
<td><div id="ifSS" style="visibility:hidden"><input type="text" name="swath_no"></div></td>


<td align="right">Channels:</td>
<td><input type="text" name="channels"/></td>

</tr></div>
<tr>


<td align="right">Shot Point (From):</td>
<td><input type="text" name="sp_no_from"/></td>
<td align="right">Shot Point (To):</td>
<td><input type="text" name="sp_no_to"/></td>
<td align="right">FFID (From):</td>
<td><input type="text" name="ffid_no_from"/></td>
<td align="right">FFID (To):</td>
<td><input type="text" name="ffid_no_to"/></td>


</tr>
<tr>
    

<td align="right">Inline (From):</td>
<td><input type="text" name="inline_no_from"/></td>
<td align="right">Inline (To):</td>
<td><input type="text" name="inline_no_to"/></td>   
<td align="right">Crossline (From):</td>
<td><input type="text" name="xline_no_from"/></td>
<td align="right">Crossline (To):</td>
<td><input type="text" name="xline_no_to"/></td>


</tr>
<tr>


<td align="right">Streamers:</td>
<td><input type="text" name="streamer"/>
<td align="right">Seq. Range (From):</td>
<td><input type="text" name="seq_no_from"/></td>
<td align="right">Seq. Range (To):</td>
<td><input type="text" name="seq_no_to"/></td>  



</tr>

        
</table>






`;
document.getElementById("acqType").selectedIndex = "2";
document.getElementById("surveyType").selectedIndex = "2";
document.getElementById("dataType").selectedIndex = "1";
document.getElementById("agency").innerHTML = "Acquiring Agency:";
document.getElementById("notext").innerHTML = "Contract No.:";
}

else if ((document.form.acq_type.value == "Onland") && (document.form.survey_type.value == "4D") && (document.form.data_type.value == "Raw")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="swath">
<td align="right">Swath No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:swathRCheck();" name="swath" value="range" id="SRCheck"></td>
<td><div id="ifSF" style="visibility:hidden"><input type="text" name="swath_no_from"></div></td>
<td><div id="ifST" style="visibility:hidden"><input type="text" name="swath_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:swathSCheck();" name="swath" value="specify" id="SSCheck"></td>
<td><div id="ifSS" style="visibility:hidden"><input type="text" name="swath_no"></div></td>




<td align="right">Channels:</td>
<td><input type="text" name="channels"/></td>

</tr></div>
<tr>


<td align="right">Shot Point (From):</td>
<td><input type="text" name="sp_no_from"/></td>
<td align="right">Shot Point (To):</td>
<td><input type="text" name="sp_no_to"/></td>
<td align="right">FFID (From):</td>
<td><input type="text" name="ffid_no_from"/></td>
<td align="right">FFID (To):</td>
<td><input type="text" name="ffid_no_to"/></td>


</tr>
<tr>
    

<td align="right">Inline (From):</td>
<td><input type="text" name="inline_no_from"/></td>
<td align="right">Inline (To):</td>
<td><input type="text" name="inline_no_to"/></td>   
<td align="right">Crossline (From):</td>
<td><input type="text" name="xline_no_from"/></td>
<td align="right">Crossline (To):</td>
<td><input type="text" name="xline_no_to"/></td>


</tr>

    

    
</table>
`;
document.getElementById("acqType").selectedIndex = "1";
document.getElementById("surveyType").selectedIndex = "3";
document.getElementById("dataType").selectedIndex = "1";
document.getElementById("agency").innerHTML = "Acquiring Agency:";
document.getElementById("notext").innerHTML = "Investigation No.:";
}


else if ((document.form.acq_type.value == "Offshore") && (document.form.survey_type.value == "4D") && (document.form.data_type.value == "Raw")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="swath">
<td align="right">Swath No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:swathRCheck();" name="swath" value="range" id="SRCheck"></td>
<td><div id="ifSF" style="visibility:hidden"><input type="text" name="swath_no_from"></div></td>
<td><div id="ifST" style="visibility:hidden"><input type="text" name="swath_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:swathSCheck();" name="swath" value="specify" id="SSCheck"></td>
<td><div id="ifSS" style="visibility:hidden"><input type="text" name="swath_no"></div></td>


<td align="right">Channels:</td>
<td><input type="text" name="channels"/></td>

</tr></div>
<tr>


<td align="right">Shot Point (From):</td>
<td><input type="text" name="sp_no_from"/></td>
<td align="right">Shot Point (To):</td>
<td><input type="text" name="sp_no_to"/></td>
<td align="right">FFID (From):</td>
<td><input type="text" name="ffid_no_from"/></td>
<td align="right">FFID (To):</td>
<td><input type="text" name="ffid_no_to"/></td>


</tr>
<tr>
    

<td align="right">Inline (From):</td>
<td><input type="text" name="inline_no_from"/></td>
<td align="right">Inline (To):</td>
<td><input type="text" name="inline_no_to"/></td>   
<td align="right">Crossline (From):</td>
<td><input type="text" name="xline_no_from"/></td>
<td align="right">Crossline (To):</td>
<td><input type="text" name="xline_no_to"/></td>


</tr>
<tr>


<td align="right">Streamers:</td>
<td><input type="text" name="streamer"/>
<td align="right">Seq. Range (From):</td>
<td><input type="text" name="seq_no_from"/></td>
<td align="right">Seq. Range (To):</td>
<td><input type="text" name="seq_no_to"/></td>  



</tr>

        
</table>






`;
document.getElementById("acqType").selectedIndex = "2";
document.getElementById("surveyType").selectedIndex = "3";
document.getElementById("dataType").selectedIndex = "1";
document.getElementById("agency").innerHTML = "Acquiring Agency:";
document.getElementById("notext").innerHTML = "Contract No.:";
}





else if ((document.form.acq_type.value == "Onland") && (document.form.survey_type.value == "VSP") && (document.form.data_type.value == "Raw")){
document.getElementById("maintable").innerHTML = home+
`<tr>


<td align="right" >Well Name:</td>
<td><input type="text" name="well_name" style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()"/></td>
<td align="right">Well No.:</td>
<td><input type="text" name="well_no" style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()"/></td>
<td align="right">Type of Data:</td>
<td><select name="type_vsp_survey"/>
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM VSPTypeSurvey ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">Type Of Well:</td>
<td><select name="type_well">
<option value="" selected></option>
<option value="Vertical">Vertical</option>
<option value="Deviated">Deviated</option>
</select></td>


</tr>
<tr>


<td align="right">Depth (From):</td>
<td><input type="text" name="depth_from"/></td>
<td align="right">Depth (To):</td>
<td><input type="text" name="depth_to"/></td>
<td align="right">Depth Interval:</td>
<td><input type="text" name="depth_int"/></td>
<td align="right">Observer's Log:</td>
<td><select name="data_book">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr>
<tr>


<td align="right">Latitude:</td>
<td><input type="text" name="lat"></td>
<td align="right">Longitude:</td>
<td><input type="text" name="longi"/></td>
<td align="right">Easting:</td>
<td><input type="text" name="east"/></td>
<td align="right">Northing:</td>
<td><input type="text" name="north"/></td>


</tr>
<tr>


<td align="right">Line Ref.:</td>
<td><input type="text" name="line_ref"/></td>
<td align="right">No. of Levels:</td>
<td><input type="text" name="no_of_lev"/></td>  
<td align="right">No. of Geophones:</td>
<td><input type="text" name="no_geoph"/></td>
<td></td>
<td></td>


</tr>

        
</table>
`;
document.getElementById("acqType").selectedIndex = "1";
document.getElementById("surveyType").selectedIndex = "4";
document.getElementById("dataType").selectedIndex = "1";
document.getElementById("agency").innerHTML = "Acquiring Agency:";
document.getElementById("notext").innerHTML = "Investigation No.:";
}

else if ((document.form.acq_type.value == "Offshore") && (document.form.survey_type.value == "VSP") && (document.form.data_type.value == "Raw")){
document.getElementById("maintable").innerHTML = home+
`<tr>


<td align="right" >Well Name:</td>
<td><input type="text" name="well_name" style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()"/></td>
<td align="right">Well No.:</td>
<td><input type="text" name="well_no" style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()"/></td>
<td align="right">Type of Data:</td>
<td><select name="type_vsp_survey"/>
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM VSPTypeSurvey ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">Type Of Well:</td>
<td><select name="type_well">
<option value="" selected></option>
<option value="Vertical">Vertical</option>
<option value="Deviated">Deviated</option>
</select></td>


</tr>
<tr>


<td align="right">Depth (From):</td>
<td><input type="text" name="depth_from"/></td>
<td align="right">Depth (To):</td>
<td><input type="text" name="depth_to"/></td>
<td align="right">Depth Interval:</td>
<td><input type="text" name="depth_int"/></td>
<td align="right">Observer's Log:</td>
<td><select name="data_book">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr>
<tr>


<td align="right">Latitude:</td>
<td><input type="text" name="lat"></td>
<td align="right">Longitude:</td>
<td><input type="text" name="longi"/></td>
<td align="right">Easting:</td>
<td><input type="text" name="east"/></td>
<td align="right">Northing:</td>
<td><input type="text" name="north"/></td>


</tr>
<tr>


<td align="right">Line Ref.:</td>
<td><input type="text" name="line_ref"/></td>
<td align="right">No. of Levels:</td>
<td><input type="text" name="no_of_lev"/></td>  
<td align="right">No. of Geophones:</td>
<td><input type="text" name="no_geoph"/></td>
<td></td>
<td></td>


</tr>

        
</table>
`;
document.getElementById("acqType").selectedIndex = "2";
document.getElementById("surveyType").selectedIndex = "4";
document.getElementById("dataType").selectedIndex = "1";
document.getElementById("agency").innerHTML = "Acquiring Agency:";
document.getElementById("notext").innerHTML = "Contract No.:";
}



else if ((document.form.acq_type.value == "Onland") && (document.form.survey_type.value == "2D") && (document.form.data_type.value == "Processed")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="line">
<td align="right">Line No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:lineRCheck();" name="line" value="range" id="LRCheck"></td>
<td><div id="ifLF" style="visibility:hidden"><input type="text" name="line_no_from"></div></td>
<td><div id="ifLT" style="visibility:hidden"><input type="text" name="line_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:lineSCheck();" name="line" value="specify" id="LSCheck"></td>
<td><div id="ifLS" style="visibility:hidden"><input type="text" name="line_no"></div></td>

<td align="right">Reprocessed:</td>
<td><select name="reprocessed">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr></div>
<tr>


<td align="right">Shot Point (From):</td>
<td><input type="text" name="sp_no_from"/></td>
<td align="right">Shot Point (To):</td>
<td><input type="text" name="sp_no_to"/></td>
<td align="right">FFID (From):</td>
<td><input type="text" name="ffid_no_from"/></td>
<td align="right">FFID (To):</td>
<td><input type="text" name="ffid_no_to"/></td>


</tr>

<tr>


<td align="right">Year of Processing:</td>
<td><input type="text" name="processed_year"/></td>
<td align="right">Data Sent To GEOPIC:</td>
<td><select name="data_sent_geopic">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
<option value="Unknown">Unknown</option>
</select></td>



</tr>
<tr>


<td align="right">Processed Type:</td>
<td><select name="output[]" multiple="multiple" id="output">
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM ProcessedOutput ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">If Not Listed, Enter:</td>
<td><input type="text" id="option"/></td>
<td></td>
<td><button type="button" onclick="add_option()">Add Option To Processed Type</button></td>
<td></td>
<td></td>

</tr>
        
</table>
`;
document.getElementById("acqType").selectedIndex = "1";
document.getElementById("surveyType").selectedIndex = "1";
document.getElementById("dataType").selectedIndex = "2";
document.getElementById("agency").innerHTML = "Processing Agency:";
document.getElementById("notext").innerHTML = "Investigation No.:";
}


else if ((document.form.acq_type.value == "Offshore") && (document.form.survey_type.value == "2D") && (document.form.data_type.value == "Processed")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="line">
<td align="right">Line No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:lineRCheck();" name="line" value="range" id="LRCheck"></td>
<td><div id="ifLF" style="visibility:hidden"><input type="text" name="line_no_from"></div></td>
<td><div id="ifLT" style="visibility:hidden"><input type="text" name="line_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:lineSCheck();" name="line" value="specify" id="LSCheck"></td>
<td><div id="ifLS" style="visibility:hidden"><input type="text" name="line_no"></div></td>

<td align="right">Reprocessed:</td>
<td><select name="reprocessed">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr></div>
<tr>


<td align="right">Shot Point (From):</td>
<td><input type="text" name="sp_no_from"/></td>
<td align="right">Shot Point (To):</td>
<td><input type="text" name="sp_no_to"/></td>
<td align="right">FFID (From):</td>
<td><input type="text" name="ffid_no_from"/></td>
<td align="right">FFID (To):</td>
<td><input type="text" name="ffid_no_to"/></td>


</tr>
<tr>


<td align="right">Year of Processing:</td>
<td><input type="text" name="processed_year"/></td>
<td align="right">Data Sent To GEOPIC:</td>
<td><select name="data_sent_geopic">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
<option value="Unknown">Unknown</option>
</select></td>


</tr>
<tr>


<td align="right">Processed Type:</td>
<td><select name="output[]" multiple="multiple" id="output">
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM ProcessedOutput ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">If Not Listed, Enter:</td>
<td><input type="text" id="option"/></td>
<td></td>
<td><button type="button" onclick="add_option()">Add Option To Processed Type</button></td>
<td></td>
<td></td>

</tr>
        
</table>
`;
document.getElementById("acqType").selectedIndex = "2";
document.getElementById("surveyType").selectedIndex = "1";
document.getElementById("dataType").selectedIndex = "2";
document.getElementById("agency").innerHTML = "Processing Agency:";
document.getElementById("notext").innerHTML = "Contract No.:";
}


else if ((document.form.acq_type.value == "Onland") && (document.form.survey_type.value == "3D") && (document.form.data_type.value == "Processed")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="swath">
<td align="right">Swath No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:swathRCheck();" name="swath" value="range" id="SRCheck"></td>
<td><div id="ifSF" style="visibility:hidden"><input type="text" name="swath_no_from"></div></td>
<td><div id="ifST" style="visibility:hidden"><input type="text" name="swath_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:swathSCheck();" name="swath" value="specify" id="SSCheck"></td>
<td><div id="ifSS" style="visibility:hidden"><input type="text" name="swath_no"></div></td>

<td align="right">Reprocessed:</td>
<td><select name="reprocessed">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr></div>
<tr>
    

<td align="right">Inline (From):</td>
<td><input type="text" name="inline_no_from"/></td>
<td align="right">Inline (To):</td>
<td><input type="text" name="inline_no_to"/></td>   
<td align="right">Crossline (From):</td>
<td><input type="text" name="xline_no_from"/></td>
<td align="right">Crossline (To):</td>
<td><input type="text" name="xline_no_to"/></td>


</tr>
<tr>


<td align="right">Year of Processing:</td>
<td><input type="text" name="processed_year"/></td>
<td align="right">Data Sent To GEOPIC:</td>
<td><select name="data_sent_geopic">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
<option value="Unknown">Unknown</option>
</select></td>


</tr>

<tr>


<td align="right">Processed Type:</td>
<td><select name="output[]" multiple="multiple" id="output">
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM ProcessedOutput ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">If Not Listed, Enter:</td>
<td><input type="text" id="option"/></td>
<td></td>
<td><button type="button" onclick="add_option()">Add Option To Processed Type</button></td>
<td></td>
<td></td>

</tr>
        
</table>
`;
document.getElementById("acqType").selectedIndex = "1";
document.getElementById("surveyType").selectedIndex = "2";
document.getElementById("dataType").selectedIndex = "2";
document.getElementById("agency").innerHTML = "Processing Agency:";
document.getElementById("notext").innerHTML = "Investigation No.:";
}


else if ((document.form.acq_type.value == "Offshore") && (document.form.survey_type.value == "3D") && (document.form.data_type.value == "Processed")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="swath">
<td align="right">Swath No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:swathRCheck();" name="swath" value="range" id="SRCheck"></td>
<td><div id="ifSF" style="visibility:hidden"><input type="text" name="swath_no_from"></div></td>
<td><div id="ifST" style="visibility:hidden"><input type="text" name="swath_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:swathSCheck();" name="swath" value="specify" id="SSCheck"></td>
<td><div id="ifSS" style="visibility:hidden"><input type="text" name="swath_no"></div></td>

<td align="right">Reprocessed:</td>
<td><select name="reprocessed">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr></div>
<tr>
    

<td align="right">Inline (From):</td>
<td><input type="text" name="inline_no_from"/></td>
<td align="right">Inline (To):</td>
<td><input type="text" name="inline_no_to"/></td>   
<td align="right">Crossline (From):</td>
<td><input type="text" name="xline_no_from"/></td>
<td align="right">Crossline (To):</td>
<td><input type="text" name="xline_no_to"/></td>


</tr>
<tr>


<td align="right">Year of Processing:</td>
<td><input type="text" name="processed_year"/></td>
<td align="right">Data Sent To GEOPIC:</td>
<td><select name="data_sent_geopic">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
<option value="Unknown">Unknown</option>
</select></td>


</tr>
<tr>


<td align="right">Processed Type:</td>
<td><select name="output[]" multiple="multiple" id="output">
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM ProcessedOutput ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">If Not Listed, Enter:</td>
<td><input type="text" id="option"/></td>
<td></td>
<td><button type="button" onclick="add_option()">Add Option To Processed Type</button></td>
<td></td>
<td></td>

</tr>
        
</table>
`;
document.getElementById("acqType").selectedIndex = "2";
document.getElementById("surveyType").selectedIndex = "2";
document.getElementById("dataType").selectedIndex = "2";
document.getElementById("agency").innerHTML = "Processing Agency:";
document.getElementById("notext").innerHTML = "Contract No.:";
}


else if ((document.form.acq_type.value == "Onland") && (document.form.survey_type.value == "4D") && (document.form.data_type.value == "Processed")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="swath">
<td align="right">Swath No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:swathRCheck();" name="swath" value="range" id="SRCheck"></td>
<td><div id="ifSF" style="visibility:hidden"><input type="text" name="swath_no_from"></div></td>
<td><div id="ifST" style="visibility:hidden"><input type="text" name="swath_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:swathSCheck();" name="swath" value="specify" id="SSCheck"></td>
<td><div id="ifSS" style="visibility:hidden"><input type="text" name="swath_no"></div></td>

<td align="right">Reprocessed:</td>
<td><select name="reprocessed">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr></div>
<tr>
    

<td align="right">Inline (From):</td>
<td><input type="text" name="inline_no_from"/></td>
<td align="right">Inline (To):</td>
<td><input type="text" name="inline_no_to"/></td>   
<td align="right">Crossline (From):</td>
<td><input type="text" name="xline_no_from"/></td>
<td align="right">Crossline (To):</td>
<td><input type="text" name="xline_no_to"/></td>


</tr>
<tr>


<td align="right">Year of Processing:</td>
<td><input type="text" name="processed_year"/></td>
<td align="right">Data Sent To GEOPIC:</td>
<td><select name="data_sent_geopic">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
<option value="Unknown">Unknown</option>
</select></td>


</tr>
<tr>


<td align="right">Processed Type:</td>
<td><select name="output[]" multiple="multiple" id="output">
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM ProcessedOutput ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">If Not Listed, Enter:</td>
<td><input type="text" id="option"/></td>
<td></td>
<td><button type="button" onclick="add_option()">Add Option To Processed Type</button></td>
<td></td>
<td></td>

</tr>
        
</table>
`;
document.getElementById("acqType").selectedIndex = "1";
document.getElementById("surveyType").selectedIndex = "3";
document.getElementById("dataType").selectedIndex = "2";
document.getElementById("agency").innerHTML = "Processing Agency:";
document.getElementById("notext").innerHTML = "Investigation No.:";
}


else if ((document.form.acq_type.value == "Offshore") && (document.form.survey_type.value == "4D") && (document.form.data_type.value == "Processed")){
document.getElementById("maintable").innerHTML = home+
`<tr>

<div class="swath">
<td align="right">Swath No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:swathRCheck();" name="swath" value="range" id="SRCheck"></td>
<td><div id="ifSF" style="visibility:hidden"><input type="text" name="swath_no_from"></div></td>
<td><div id="ifST" style="visibility:hidden"><input type="text" name="swath_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:swathSCheck();" name="swath" value="specify" id="SSCheck"></td>
<td><div id="ifSS" style="visibility:hidden"><input type="text" name="swath_no"></div></td>

<td align="right">Reprocessed:</td>
<td><select name="reprocessed">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr></div>
<tr>
    

<td align="right">Inline (From):</td>
<td><input type="text" name="inline_no_from"/></td>
<td align="right">Inline (To):</td>
<td><input type="text" name="inline_no_to"/></td>   
<td align="right">Crossline (From):</td>
<td><input type="text" name="xline_no_from"/></td>
<td align="right">Crossline (To):</td>
<td><input type="text" name="xline_no_to"/></td>


</tr>
<tr>


<td align="right">Year of Processing:</td>
<td><input type="text" name="processed_year"/></td>
<td align="right">Data Sent To GEOPIC:</td>
<td><select name="data_sent_geopic">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
<option value="Unknown">Unknown</option>
</select></td>


</tr>
<tr>


<td align="right">Processed Type:</td>
<td><select name="output[]" multiple="multiple" id="output">
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM ProcessedOutput ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">If Not Listed, Enter:</td>
<td><input type="text" id="option"/></td>
<td></td>
<td><button type="button" onclick="add_option()">Add Option To Processed Type</button></td>
<td></td>
<td></td>

</tr>
        
</table>
`;
document.getElementById("acqType").selectedIndex = "2";
document.getElementById("surveyType").selectedIndex = "3";
document.getElementById("dataType").selectedIndex = "2";
document.getElementById("agency").innerHTML = "Processing Agency:";
document.getElementById("notext").innerHTML = "Contract No.:";
}


else if ((document.form.acq_type.value == "Onland") && (document.form.survey_type.value == "VSP") && (document.form.data_type.value == "Processed")){
document.getElementById("maintable").innerHTML = home+
`<tr>


<td align="right" >Well Name:</td>
<td><input type="text" name="well_name" style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()"/></td>
<td align="right">Well No.:</td>
<td><input type="text" name="well_no" style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()"/></td>
<td align="right">Type of Data:</td>
<td><select name="type_vsp_survey"/>
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM VSPTypeSurvey ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">Type Of Well:</td>
<td><select name="type_well">
<option value="" selected></option>
<option value="Vertical">Vertical</option>
<option value="Deviated">Deviated</option>
</select></td>


</tr>
<tr>


<td align="right">Depth (From):</td>
<td><input type="text" name="depth_from"/></td>
<td align="right">Depth (To):</td>
<td><input type="text" name="depth_to"/></td>
<td align="right">Depth Interval:</td>
<td><input type="text" name="depth_int"/></td>
<td align="right">Observer's Log:</td>
<td><select name="data_book">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr>
<tr>


<td align="right">Latitude:</td>
<td><input type="text" name="lat"></td>
<td align="right">Longitude:</td>
<td><input type="text" name="longi"/></td>
<td align="right">Easting:</td>
<td><input type="text" name="east"/></td>
<td align="right">Northing:</td>
<td><input type="text" name="north"/></td>


</tr>
<tr>


<td align="right">Line Ref.:</td>
<td><input type="text" name="line_ref"/></td>
<td align="right">No. of Levels:</td>
<td><input type="text" name="no_of_lev"/></td>  
<td align="right">No. of Geophones:</td>
<td><input type="text" name="no_geoph"/></td>
<td align="right">Reprocessed:</td>
<td><select name="reprocessed">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr>
<tr>


<td align="right">Year of Processing:</td>
<td><input type="text" name="processed_year"/></td>
<td align="right">K.B.:</td>
<td><input type="text" name="kb"/></td>
<td align="right">G.L.:</td>
<td><input type="text" name="gl"/></td>


</tr>
<tr>



<td align="right">Data Sent To GEOPIC:</td>
<td><select name="data_sent_geopic">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
<option value="Unknown">Unknown</option>
</select></td>


</tr>

        
</table>
`;
document.getElementById("acqType").selectedIndex = "1";
document.getElementById("surveyType").selectedIndex = "4";
document.getElementById("dataType").selectedIndex = "2";
document.getElementById("agency").innerHTML = "Processing Agency:";
document.getElementById("notext").innerHTML = "Investigation No.:";

}


else if ((document.form.acq_type.value == "Offshore") && (document.form.survey_type.value == "VSP") && (document.form.data_type.value == "Processed")){
document.getElementById("maintable").innerHTML = home+
`<tr>


<td align="right" >Well Name:</td>
<td><input type="text" name="well_name" style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()"/></td>
<td align="right">Well No.:</td>
<td><input type="text" name="well_no" style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()"/></td>
<td align="right">Type of Data:</td>
<td><select name="type_vsp_survey"/>
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM VSPTypeSurvey ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">Type Of Well:</td>
<td><select name="type_well">
<option value="" selected></option>
<option value="Vertical">Vertical</option>
<option value="Deviated">Deviated</option>
</select></td>


</tr>
<tr>


<td align="right">Depth (From):</td>
<td><input type="text" name="depth_from"/></td>
<td align="right">Depth (To):</td>
<td><input type="text" name="depth_to"/></td>
<td align="right">Depth Interval:</td>
<td><input type="text" name="depth_int"/></td>
<td align="right">Observer's Log:</td>
<td><select name="data_book">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr>
<tr>


<td align="right">Latitude:</td>
<td><input type="text" name="lat"></td>
<td align="right">Longitude:</td>
<td><input type="text" name="longi"/></td>
<td align="right">Easting:</td>
<td><input type="text" name="east"/></td>
<td align="right">Northing:</td>
<td><input type="text" name="north"/></td>


</tr>
<tr>


<td align="right">Line Ref.:</td>
<td><input type="text" name="line_ref"/></td>
<td align="right">No. of Levels:</td>
<td><input type="text" name="no_of_lev"/></td>  
<td align="right">No. of Geophones:</td>
<td><input type="text" name="no_geoph"/></td>
<td align="right">Reprocessed:</td>
<td><select name="reprocessed">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select></td>


</tr>
<tr>


<td align="right">Year of Processing:</td>
<td><input type="text" name="processed_year"/></td>
<td align="right">K.B.:</td>
<td><input type="text" name="kb"/></td>
<td align="right">G.L.:</td>
<td><input type="text" name="gl"/></td>


</tr>
<tr>


<td align="right">Data Sent To GEOPIC:</td>
<td><select name="data_sent_geopic">
<option value="" selected></option>
<option value="Yes">Yes</option>
<option value="No">No</option>
<option value="Unknown">Unknown</option>
</select></td>


</tr>

        
</table>
`;
document.getElementById("acqType").selectedIndex = "2";
document.getElementById("surveyType").selectedIndex = "4";
document.getElementById("dataType").selectedIndex = "2";
document.getElementById("agency").innerHTML = "Processing Agency:";
document.getElementById("notext").innerHTML = "Contract No.:";
}

document.getElementById("basin").selectedIndex = basin;
document.getElementById("block").value = block;
document.getElementById("area").value = area;

}

</script>
</head>

  
<body onload="startupedit();"> 

<?php if ($logged_in_user_priv=="admin"){ ?>
<a href="adminhome.php">Back</a>
<?php } else { ?>
<a href="home.php">Back</a>
<?php } ?>
<br/>

<img src="ONGCNewLogo.jpg" alt="ONGC" height="80px" width="179px">

<?php
$result = mysqli_query($db_con, "SELECT * FROM UserDetails WHERE CPFNO='$cpfno'");
$row = mysqli_fetch_assoc($result);
?>


<table class="user" align="right">
<tr>

<td><?php echo $row['FirstName']." ".$row['LastName'].", ".$row['Designation'].", ".$row['CPFNo']; ?></td>

</tr>
<tr align="center">

<td><a href="resetpw.php">Change Password</a> <a href="logout.php">Logout</a></td>

</tr>
</table>

<h3>Edit Geo-Data Entry Form</h3>
<div class="two">
<form name="form" action="update_form.php"  onSubmit="return validate_form(this)"  method="POST" enctype="multipart/form-data">
<input type="hidden" name="db_id" value="<?php echo $data['db_id']; ?>">

<table class="main" id= "maintable" align="center" border="0" cellpadding="2" cellspacing="2">


<tr>

<td align="right">Basin:</td>
<td><select name="basin" id="basin">
<option></option>

<?php
$results = mysqli_query($db_con, "SELECT * FROM Basin ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>

</select></td>
<td align="right">Block:</td>
<td><input type="text" name="block" style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()" id="block"/></td>
<td align="right">Area:</td>
<td><input type="text" name="area"  style="text-transform:uppercase" oninput="this.value = this.value.toUpperCase()" id="area"/></td>
<td align="right">Type of Acquisition:</td>
<td><select name="acq_type" onchange="javascript:dynamicForm1()" id="acqType">
<option></option>

<?php
$results = mysqli_query($db_con, "SELECT * FROM AcquisitionType ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>

</select></td>



</tr>               
<tr>


<td align="right">Type of Survey:</td>
<td><select name="survey_type" onchange="javascript:dynamicForm2()" id="surveyType">
<option></option>

<?php
$results = mysqli_query($db_con, "SELECT * FROM SurveyType ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>

</select></td>


<td align="right">Data Category:</td>
<td><select name="data_type" onchange="javascript:dynamicForm3()" id="dataType">
<option></option>

<?php
$results = mysqli_query($db_con, "SELECT * FROM DataType ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>

</select></td>

<td align="right" id="agency">Acquiring Agency:</td>
<td><input type="text" name="agency" /></td>
<td align="right" id="notext">Investigation No.:</td>
<td><input type="text" name="inv_con_no" /></td> 




</tr>
<tr>

<td align="right">Field Season:</td>
<td><input type="text" name="field_season" /></td>
<td align="right">Geophysical Party:</td>
<td><input type="text" name="gp_name" /></td>
<td align="right">Record Length (ms or m):</td>
<td><input type="text" name="rl" /></td>
<td align="right">Sampling Interval (ms or m):</td>
<td><input type="text" name="si" /></td>

</tr>
<tr>

<div class="cart">
<td align="right">Cart. No. &#10146;</td><td align="right">Range:<input type="radio" onclick="javascript:cartRCheck();" name="cart" value="range" id="CRCheck"></td>
<td><div id="ifCF" style="visibility:hidden"><input type="text" name="cart_no_from"></div></td>
<td><div id="ifCT" style="visibility:hidden"><input type="text" name="cart_no_to"></div></td>
<td align="right">Specify:<input type="radio" onclick="javascript:cartSCheck();" name="cart" value="specify" id="CSCheck"></td>
<td><div id="ifCS" style="visibility:hidden; display:inline-flex; align-items:center; white-space:nowrap; width:100%;"><input class="text_input" type="text" name="cart_no" style="width:65%;"><input class="button" type="button" name="button" value="Get ID" onclick="fetchNextID();" style="width:30%; margin-left: 3px;" /></div></td>
<script>
function fetchNextID() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "form_next_id.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementsByName("cart_no")[0].value = xhr.responseText;
        }
    };
    xhr.send();
}
</script>
<td align="right">No. of Carts.:</td>
<td><input type="text" name="cart_qty"/></td>


</tr>
<tr>


<td align="right">Format:</td>
<td> <select name="format"/>
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM Format ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>
<td align="right">Media:</td>
<td><select name="media">
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM Media ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>  

<td align="right">Set No.:</td>
<td><select name="set_no">
<option></option>

<?php
$results = mysqli_query($db_con, "SELECT * FROM SetNo ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>

</select>  
</td>   
<td align="right">Date Received:</td>
<td><input type="date" name="date_rcvd_inhouse" placeholder="DD-MM-YYYY"/></td>   


</tr>
<tr>


<td align="right">Location:</td>
<td><select name="location">
<option></option>
<?php
$results = mysqli_query($db_con, "SELECT * FROM Location ORDER BY db_id ASC");
while($result = mysqli_fetch_array($results)){
echo "<option value=\"".$result[1]."\">".$result[1]."</option>";
}
?>
</select></td>   
<td align="right">Rack No.:</td>
<td><input type="text" name="rack_no"/></td>
<td align="right">Shelf No.:</td>
<td><input type="text" name="shelf_no"/></td>
<td align="right">Box No.:</td>
<td><input type="text" name="box_no"/></td>


</tr>


</table>
<table class ="main" border="0" align="center" cellpadding="25" cellspacing="5" style="margin-left:60px; margin-right:150px;">       


<tr>


<td align="right">Remarks:</td>
<td><textarea rows="8" cols="22" name="remarks" style="font-size:19px; resize:none;"></textarea></td>
<td align="right">Reports:</td>
<td><textarea rows="8" cols="22" name="reports" style="font-size:19px; resize:none;"></textarea></td>
<td align="right">Additional Upload (PDF):</td>
<td><input type="file" name="iso_up" accept="application/pdf,application/vnd.ms-excel" /></td>
<td><input class="submit" type="submit" name="submit" value="Update" onclick="validate_form(this.form)" /></td>
</form>


</tr>

        
</table>
</div> 
</body>
<script>
var home = document.getElementById("maintable").innerHTML;
</script>
</html>