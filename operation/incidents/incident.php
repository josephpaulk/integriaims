<?php

// Pandora FMS - the Free monitoring system
// ========================================
// Copyright (c) 2004-2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2005-2007 Artica Soluciones Tecnologicas
// Copyright (c) 2004-2007 Raul Mateos Martin, raulofpandora@gmail.com
// Copyright (c) 2006-2007 Jose Navarro jose@jnavarro.net
// Copyright (c) 2006-2007 Jonathan Barajas, jonathan.barajas[AT]gmail[DOT]com

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// Load global vars


$accion = "";
global $config;

if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_usuario =$_SESSION["id_usuario"];
if (give_acl($id_usuario, 0, "IR")!=1) {
	audit_db($id_usuario, $config["REMOTE_ADDR"], "ACL Violation","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

// Take input parameters

// Offset adjustment
if (isset($_GET["offset"]))
	$offset=$_GET["offset"];
else
	$offset=0;

// Delete incident
if (isset($_GET["quick_delete"])){
	$id_inc = $_GET["quick_delete"];
	$sql2="SELECT * FROM tincidencia WHERE id_incidencia=".$id_inc;
	$result2=mysql_query($sql2);
	$row2=mysql_fetch_array($result2);
	if ($row2) {
		$id_author_inc = $row2["id_usuario"];
		if ((give_acl($id_usuario, $row2["id_grupo"], "IM") ==1) OR ($_SESSION["id_usuario"] == $id_author_inc) ){
			borrar_incidencia($id_inc);
			echo "<h3 class='suc'>".$lang_label["del_incid_ok"]."</h3>";
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "Incident deleted","User ".$id_usuario." deleted incident #".$id_inc);
		} else {
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$_SESSION["id_usuario"]." try to delete incident");
			echo "<h3 class='error'>".$lang_label["del_incid_no"]."</h3>";
			no_permission();
		}
	}
}

// ---------
// Search
// ---------

$busqueda="";
if (isset($_POST["texto"]) OR (isset($_GET["texto"]))){
	if (isset($_POST["texto"])){
		$texto_form = clean_input ($_POST["texto"]);
		$_GET["texto"]=$texto_form; // Update GET vars if data comes from POST
	} else	// GET
		$texto_form = clean_input ($_GET["texto"]);

	$busqueda = "( titulo LIKE '%".$texto_form."%' OR descripcion LIKE '%".$texto_form."%' )";
}

if (isset($_POST["usuario"]) OR (isset($_GET["usuario"]))){
	if (isset($_POST["usuario"])){
		$usuario_form = clean_input ($_POST["usuario"]);
		$_GET["usuario"]=$usuario_form;
	} else // GET
		$usuario_form= clean_input ($_GET["usuario"]);

	if ($usuario_form != "--"){
		if (isset($_GET["texto"]))
			$busqueda = $busqueda." and ";
		$busqueda= $busqueda." id_usuario = '".$_GET["usuario"]."' ";
	}
}

if (isset($_POST["incident_id"])){
	$incident_id = $_POST["incident_id"];
	if ($incident_id != "")
		$busqueda = "id_incidencia = $incident_id";
}
	

// Filter
if ($busqueda != "")
	$sql1= "WHERE ".$busqueda;
else
	$sql1="";


if (isset($_GET["estado"]) and (!isset($_POST["estado"])))
	$_POST["estado"]=$_GET["estado"];
if (isset($_GET["grupo"]) and (!isset($_POST["grupo"])))
		$_POST["grupo"]=$_GET["grupo"];
if (isset($_GET["prioridad"]) and (!isset($_POST["prioridad"])))
		$_POST["prioridad"]=$_GET["prioridad"];


if (isset($_POST['estado']) OR (isset($_POST['grupo'])) OR (isset($_POST['prioridad']) ) ) {
		if ((isset($_POST["estado"])) AND ($_POST["estado"] != -1)){
	$_GET["estado"] = $_POST["estado"];
			if ($sql1 == "")
					$sql1='WHERE estado='.$_POST["estado"];
			else
					$sql1 =$sql1.' AND estado='.$_POST["estado"];
		}

		if ((isset($_POST["prioridad"])) AND ($_POST["prioridad"] != -1)) {
	$_GET["prioridad"]=$_POST["prioridad"];
				if ($sql1 == "")
						$sql1='WHERE prioridad='.$_POST["prioridad"];
				else
						$sql1 =$sql1.' and prioridad='.$_POST["prioridad"];
		}

		if ((isset($_POST["grupo"])) AND ($_POST["grupo"] != -1)) {
	$_GET["grupo"] = $_POST["grupo"];
				if ($sql1 == "")
						$sql1='WHERE id_grupo='.$_POST["grupo"];
				else
						$sql1 =$sql1.' AND id_grupo='.$_POST["grupo"];
		}
	}

$sql0="SELECT * FROM tincidencia ".$sql1." ORDER BY actualizacion DESC";
$sql1_count="SELECT COUNT(id_incidencia) FROM tincidencia ".$sql1;
$sql1=$sql0;
$sql1=$sql1." LIMIT $offset, ". $config["block_size"];

echo "<h2>".$lang_label["incident_manag"];
if (isset($_POST['operacion']))
	echo " -&gt; ".$lang_label["incident_view_filter"]." - ".$_POST['operacion']."</h2>";
else
	echo "</h2>";
echo "<div class=databox style='width: 500px'>";
echo "<form name='visualizacion' method='POST' action='index.php?sec=incidencias&sec2=operation/incidents/incident'>";

echo '<table border=0 width=400>';
echo "<tr>";
echo "<td>".$lang_label["f_state"];
echo "<td>".$lang_label["f_prio"];
echo "<td>".$lang_label["f_group"];
echo "<tr><td>";
echo '<select name="estado" onChange="javascript:this.form.submit();" class="w155">';
	// Tipo de estado (Type)
	// 0 - Abierta / Sin notas (Open without notes)
	// 1 - Abierta / Notas aniadidas  (Open with notes)
	// 2 - Descartada (Not valid)
	// 3 - Caducada (out of date)
	// 13 - Cerrada (closed)

if ((isset($_GET["estado"])) OR (isset($_GET["estado"]))){
	if (isset($_GET["estado"]))
		$estado = $_GET["estado"];
	if (isset($_POST["estado"]))
		$estado = $_POST["estado"];
	echo "<option value='".$estado."'>";
	switch ($estado){
		case -1: echo $lang_label["all_inc"]; break;
		case 1: echo $lang_label["opened_inc"]; break;
		case 13: echo $lang_label["closed_inc"]; break;
		case 2: echo $lang_label["rej_inc"]; break;
		case 0: echo $lang_label["new_inc"]; break;
		case 3: echo $lang_label["exp_inc"]; break;
	}
}

echo "<option value='-1'>".$lang_label["all_inc"];
echo "<option value='0'>".$lang_label["new_inc"];
echo "<option value='1'>".$lang_label["opened_inc"];
echo "<option value='13'>".$lang_label["closed_inc"];
echo "<option value='2'>".$lang_label["rej_inc"];
echo "<option value='3'>".$lang_label["exp_inc"];

echo "</select> ";
echo '<noscript><input type="submit" class="sub" value="'.$lang_label["show"].'" border="0"></noscript>	</td>';
echo '<td>';
echo '<select name="prioridad" onChange="javascript:this.form.submit();" class="w155">';

if ((isset($_GET["prioridad"])) OR (isset($_GET["prioridad"]))){ 
	if (isset($_GET["prioridad"]))
		$prioridad = $_GET["prioridad"];
	if (isset($_POST["prioridad"]))
		$prioridad = $_POST["prioridad"];
	echo "<option value=".$prioridad.">";
	switch ($prioridad){
		case -1: echo $lang_label["all"]." ".$lang_label["priority"]; break;
		case 0: echo $lang_label["informative"]; break;
		case 1: echo $lang_label["low"]; break;
		case 2: echo $lang_label["medium"]; break;
		case 3: echo $lang_label["serious"]; break;
		case 4: echo $lang_label["very_serious"]; break;
		case 10: echo $lang_label["maintenance"]; break;
	}
}
echo "<option value='-1'>".$lang_label["all"]." ".$lang_label["priority"]; // al priorities (default)
echo '<option value="0">'.$lang_label["informative"];
echo '<option value="1">'.$lang_label["low"];
echo '<option value="2">'.$lang_label["medium"];
echo '<option value="3">'.$lang_label["serious"];
echo '<option value="4">'.$lang_label["very_serious"];
echo '<option value="10">'.$lang_label["maintenance"];
echo "</select> <noscript>";
echo "<input type='submit' class='sub' value='".$lang_label["show"]."' border='0'></noscript>";
echo "</td>";


// Group combo
echo '<td><select name="grupo" onChange="javascript:this.form.submit();" class="w155">';
if ((isset($_GET["grupo"])) OR (isset($_GET["grupo"]))){ 
	if (isset($_GET["grupo"]))
		$grupo = $_GET["grupo"];
	if (isset($_POST["grupo"]))
		$grupo = $_POST["grupo"];
	echo "<option value=".$grupo.">";

	if ($grupo == -1)
		echo $lang_label["all"]." ".$lang_label["groups"]; // all groups (default)
	else
		echo dame_nombre_grupo($grupo);
}
$sqlcombo='SELECT * FROM tgrupo ORDER BY nombre';
$resultc=mysql_query($sqlcombo);
while ($rowc=mysql_fetch_array($resultc)){
	if (give_acl($id_usuario, $rowc["id_grupo"], "IR")==1)
		echo "<option value='".$rowc["id_grupo"]."'>".$rowc["nombre"];
}

echo "</select></td><td valign='middle'><noscript><input type='submit' class='sub' value='".$lang_label["show"]."' border='0'></noscript></td>";

// Pass search parameters for possible future filter searching by user
if (isset($_GET["usuario"]))
	echo "<input type='hidden' name='usuario' value='".$_GET["usuario"]."'>";
if (isset($_GET["texto"]))
	echo "<input type='hidden' name='texto' value='".$_GET["texto"]."'>";

echo "</table>";
echo "</form>";
echo "</div>";

$offset_counter=0;
// Prepare index for pagination
$incident_list[]="";
$result2=mysql_query($sql1);
$result2_count=mysql_query($sql1_count);
$row2_count = mysql_fetch_array($result2_count);

if ($row2_count[0] <= 0 ) {
	echo '<div class="nf">'.$lang_label["no_incidents"].'</div><br></table>';
} else {
	// TOTAL incidents
	$total_incidentes = $row2_count[0];
	$url = "index.php?sec=incidencias&sec2=operation/incidents/incident";

	// add form filter values for group, priority, state, and search fields: user and text
	if (isset($_GET["grupo"]))
		$url = $url."&grupo=".$_GET["grupo"];
	if (isset($_GET["prioridad"]))
		$url = $url."&prioridad=".$_GET["prioridad"];
	if (isset($_GET["estado"]))
		$url = $url."&estado=".$_GET["estado"];
	if (isset($_GET["usuario"]))
		$url = $url."&usuario=".$_GET["usuario"];
	if (isset($_GET["texto"]))
		$url = $url."&texto=".$_GET["texto"];
	if (isset($_GET["offset"] ))
		$url = $url."&offset=".$_GET["offset"];

	// Show pagination
	pagination ($total_incidentes, $url, $offset);
	echo '<br>';

	// -------------
	// Show headers
	// -------------
	echo "<table width='680' cellpadding=3 cellspacing=3 class='databox'>";
	echo "<tr>";
	echo "<th>Id";
	echo "<th>".$lang_label["incident"];
	echo "<th>".$lang_label["project"];
	echo "<th>".$lang_label["status"];
	echo "<th>".$lang_label["priority"];
	echo "<th>".$lang_label["resolution"];
	echo "<th width=82>".$lang_label["updated_at"];
	echo "<th width=70>".$lang_label["flags"];
	//echo "<th>".$lang_label["in_openedby"];
	//echo "<th>".$lang_label["delete"];
	
	$color = 1;

	// -------------
	// Show DATA TABLE
	// -------------
	while ($row2=mysql_fetch_array($result2)){ 
		$id_group = $row2["id_grupo"];
		$id_task = give_db_value ("id_task", "tincidencia", "id_incidencia", $row2["id_incidencia"]);
		$id_project = give_db_value ("id_project", "ttask", "id", $id_task);
		$project_name = give_db_value ("name", "tproject", "id", $id_project);
		if ((give_acl($id_usuario, $id_group, "IR") ==1) OR (user_belong_project ($id_user, $id_project)==1)) {
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
			}
			else {
				$tdcolor = "datos2";
				$color = 1;
			}
			
			echo "<tr>";
			echo "<td class='$tdcolor' align='left'>";
			echo "<font size=1pt><a href='index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=".$row2["id_incidencia"]."'><b>#".$row2["id_incidencia"]."</b></a></td>";

			// Title
			echo "<td class='$tdcolor'><a href='index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=".$row2["id_incidencia"]."'>".substr(clean_output ($row2["titulo"]),0,200);

			// Project
			
			echo "<td class='$tdcolor'>";
			echo substr($project_name,0,15);
			if (strlen($project_name) > 15)
				echo "...";
			// Tipo de estado  (Type)
			// (1,'New'), (2,'Unconfirmed'), (3,'Assigned'),
			// (4,'Re-opened'), (5,'Verified'), (6,'Resolved')
			// (7,'Closed');
			echo "<td class='$tdcolor' align='center'>";
			switch ($row2["estado"]) {
				case 1: echo $lang_label["status_new"];
							break;
				case 2: echo $lang_label["status_unconfirmed"];
							break;
				case 3: echo $lang_label["status_assigned"];
							break;
				case 4: echo $lang_label["status_reopened"];
							break;
				case 5: echo $lang_label["status_verified"];
							break;
				case 7: echo $lang_label["status_closed"];
							break;
				case 6: echo $lang_label["status_resolved"];
							break;
			}
			echo "<td class='$tdcolor' align='center'>";
			switch ( $row2["prioridad"] ){
			
				case 0: echo "<img src='images/flag_white.png'>"; break; // Informative
				case 1: echo "<img src='images/flag_green.png'>"; break; // Low
				case 2: echo "<img src='images/flag_yellow.png'>"; break; // Medium
				case 3: echo "<img src='images/flag_orange.png'>"; break; // Serious
				case 4: echo "<img src='images/flag_red.png'>"; break; // Very serious
				case 10: echo "<img src='images/flag_blue.png'>"; break; // Maintance
			}

			// Resolution
			echo "<td class='$tdcolor'>".give_db_value('name', 'tincident_resolution', 'id', $row2["resolution"]);

			// Update time
			echo "<td class='".$tdcolor."f9'>".human_time_comparation ( $row2["actualizacion"]);

			// Flags
			// Check for attachments in this incident
			echo "<td class='".$tdcolor."f9' align='center'>";
			$file_number = give_number_files_incident ($row2["id_incidencia"]);
			if ($file_number > 0)
				echo '<img src="images/disk.png" valign="bottom"  alt="'.$file_number.'">';

			// Check for notes
			$note_number = dame_numero_notas($row2["id_incidencia"]);
			if ($note_number > 0)
				echo '&nbsp;&nbsp;<img src="images/note.png" valign="bottom" alt="'.$note_number.'">';

			// Has mail notice activated ?
			$mail_check = give_db_value('notify_email', 'tincidencia', 'id_incidencia', $row2["id_incidencia"]);
			if ($mail_check> 0)
				echo '&nbsp;&nbsp;<img src="images/email_go.png" valign="bottom">';

			// Check for workunits
			$timeused = give_hours_incident ($row2["id_incidencia"]);
			if ($timeused > 0)
				echo '&nbsp;&nbsp;<img src="images/award_star_silver_1.png" valign="bottom">'.$timeused;

/*			
			// User who manage this incident
			echo "<td class='$tdcolor'>";
			echo "<a href='index.php?sec=usuario&sec2=operation/users/user_edit&ver=".$row2["id_usuario"]."'>".$row2["id_usuario"]."</a></td>";
			$id_author_inc = $row2["id_usuario"];
			if ((give_acl($id_usuario, $id_group, "IM") ==1) OR ($_SESSION["id_usuario"] == $id_author_inc) ){
			// Only incident owners or incident manager
			// from this group can delete incidents
				echo "<td class='$tdcolor' align='center'><a href='index.php?sec=incidentes&sec2=operation/incidents/incident&quick_delete=".$row2["id_incidencia"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a></td>";
			} else
				echo "<td class='$tdcolor' align='center'>";
*/
		}
	}
	echo "</table>";
}

/*
echo "<table cellpadding=3 cellspacing=3>";
echo "<tr><td valign='top'>";
echo "<b>".$lang_label["status"]."</b>";
?>
<table cellspacing=10 cellpadding=10 width=250 class='databox'><tr><td>
<img src='images/dot_yellow.gif'> - <?php echo give_db_value ('name', 'tincident_status', 'id',5) ?>
<td>
<img src='images/dot_orange.gif'> - <?php echo give_db_value ('name', 'tincident_status', 'id',4) ?>
<tr><td>
<img src='images/dot_green.gif'> - <?php echo give_db_value ('name', 'tincident_status', 'id',6) ?>
<td>
<img src='images/dot_lightgreen.gif'> - <?php echo give_db_value ('name', 'tincident_status', 'id',7) ?>
<tr><td>
<img src='images/dot_blue.gif'> - <?php echo give_db_value ('name', 'tincident_status', 'id',1) ?>
<td>
<img src='images/dot_red.gif'> - <?php echo give_db_value ('name', 'tincident_status', 'id',3) ?>
<tr><td colspan=2>
<img src='images/dot_white.gif'> - <?php echo give_db_value ('name', 'tincident_status', 'id',2) ?>
</table>

<?PHP
echo "<td valign='top' width='50'>";
echo "<td valign='top'>";
echo "<b>".$lang_label["priority"]."</b>";
?>

<table cellspacing=10 cellpadding=10 width=450 class='databox'>
<tr><td>
	<img src='images/flag_white.png'> - <?php echo $lang_label["informative"] ?>
	<td>
	<img src='images/flag_green.png'> - <?php echo $lang_label["low"] ?>
	<td>
	<img src='images/flag_yellow.png'> - <?php echo $lang_label["medium"] ?>
	<tr><td>
	<img src='images/flag_orange.png'> - <?php echo $lang_label["serious"] ?>
	<td>
	<img src='images/flag_red.png'> - <?php echo $lang_label["very_serious"] ?>
	<td>
	<img src='images/flag_blue.png'> - <?php echo $lang_label["maintenance"] ?>
</table>

 <?php echo "<b>".$lang_label["flags"]."</b>" ?>

<table cellspacing=10 cellpadding=10 width=450 class='databox'>
<tr>
		<td>
		<img src='images/disk.png'> <?php echo $lang_label["files"] ?>
		<td>
		<img src='images/note.png'> <?php echo $lang_label["notes"] ?>
		<td>
		<img src='images/email_go.png'> <?php echo $lang_label["email_notify"] ?>
		<td>
		<img src="images/award_star_silver_1.png" valign="bottom"> <?php echo $lang_label["workunits"] ?>
</table>

</table>

*/

?>