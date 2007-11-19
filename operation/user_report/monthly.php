<?PHP

global $config;
include ("include/calendar.php");

if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

$id_grupo = give_parameter_get ("id_grupo",0);
$id_user=$_SESSION['id_usuario'];
if (give_acl($id_user, $id_grupo, "PR") != 1){
 	// Doesn't have access to this page
	audit_db($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}
$id = give_parameter_get ("id","");
if (($id != "") && ($id != $id_user)){
	if (give_acl($id_user, 0, "PW"))
		$id_user = $id;
	else {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No permission access", "Trying to access user workunit report");
		require ("general/noaccess.php");
		exit;
	}
	
}


// Get parameters for actual Calendar show
$time = time();
$month = give_parameter_get ( "month", date('n', $time));
$year = give_parameter_get ( "year", date('y', $time));

$today = date('j',$time);
$days_f = array();
$first_of_month = gmmktime(0,0,0,$month,1,$year);
$days_in_month=gmdate('t',$first_of_month);
$locale = $config["language_code"];

echo "<h1>".lang_string("Monthly report for")." $id_user</h1>";

// Generate calendar
echo "<div style='float: left;'>";
echo generate_work_calendar ($year, $month, $days_f, 3, NULL, 1, $pn, $id_user);
echo "</div>";

?>