<?PHP

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ADD FILE CONTROL
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

global $config;
if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_inc = give_parameter_get("id",-1);
$title = give_db_value ("titulo", "tincidencia", "id_incidencia", $id_inc);


if (give_acl($config["id_user"], 0, "IW")==1){

	echo "<h3><img src='images/disk.png'>&nbsp;&nbsp;";
	echo $lang_label["upload_file"]."</A></h3>";

	echo "<div id='upload_control'>";
	echo "<table cellpadding=4 cellspacing=4 border=0 width='700' class='databox_color'>";
	echo "<tr>";
	echo '<td class="datos">'.$lang_label["filename"].'</td><td class="datos">';
	echo '<form method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_detail&id='.$id_inc.'&upload_file=1" enctype="multipart/form-data">';
	echo '<input type="file" name="userfile" value="userfile" class="sub" size="40">';
	echo '<tr><td class="datos2">'.$lang_label["description"].'</td><td class="datos2" colspan=3><input type="text" name="file_description" size=47>';
	echo "</td></tr></table>";
	echo '<input type="submit" name="upload" value="'.$lang_label["upload"].'" class="sub next">';
	echo "</form>";
	echo '</div><br>';
}

?>