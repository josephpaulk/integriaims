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

$id_incident = get_parameter ('id');
$title = give_db_value ("titulo", "tincidencia", "id_incidencia", $id_incident);


if (! give_acl($config["id_user"], 0, "IW")) {
	return;
}

echo "<div id='upload_control'>";
echo '<form method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_detail" id="form-add-file" enctype="multipart/form-data">';
echo '<table cellpadding="4" cellspacing="4" border="0" width="100%" class="databox_color">';
echo "<tr>";
echo '<td class="datos">'.lang_string ('filename').'</td><td class="datos">';
echo '<input type="file" name="userfile" value="userfile" class="sub" size="40">';
echo '<tr><td class="datos2">'.lang_string ('description').'</td><td class="datos2" colspan=3><input type="text" name="file_description" size=47>';
echo "</td></tr></table>";

print_submit_button (lang_string ('Upload'), 'upload', false, 'class="sub next"');
print_input_hidden ('id', $id_incident);
print_input_hidden ('upload_file', 1);
echo "</form>";
echo '</div>';

if (defined ('AJAX'))
	echo "<strong>AJAX support in this dialog still doesn't work</strong>"
?>
