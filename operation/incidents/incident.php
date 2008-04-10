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
	$email_notify = give_db_value ("notify_email", "tincidencia", "id_incidencia", $id_inc);
	// Email notify to all people involved in this incident
	if ($email_notify == 1){ 
		mail_incident ($id_inc, 3);
	}

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

$texto_form = get_parameter ("texto","");
$incident_form = get_parameter ("incident_id", "");
$usuario_form = get_parameter("usuario","");

// Search tokens for search filter
if ($texto_form != "")
	$busqueda = "( titulo LIKE '%".$texto_form."%' OR descripcion LIKE '%".$texto_form."%' )";

if ($usuario_form != ""){
	if ($texto_form != "")
		$busqueda = $busqueda." and ";
	$busqueda= $busqueda." id_usuario = '".$usuario_form."' ";
}
if ($incident_form != ""){
	$busqueda = "id_incidencia = $incident_form";
}
	
// Filter tokens add to search
if ($busqueda != "")
	$sql1= "WHERE ".$busqueda;
else
	$sql1="";

$filter_estado = get_parameter("estado", -1);
$filter_grupo = get_parameter("grupo", -1);
$filter_prioridad = get_parameter("prioridad", -1);

if (($filter_estado != -1) OR ($filter_grupo != -1) OR ($filter_prioridad != -1)){
    if ($filter_estado != -1){
		if ($sql1 == "")
			$sql1='WHERE estado='.$filter_estado;
		else
			$sql1 =$sql1.' AND estado='.$filter_estado;
	}
    if ($filter_prioridad != -1){
		if ($sql1 == "")
			$sql1='WHERE prioridad='.$filter_prioridad;
		else
			$sql1 =$sql1.' and prioridad='.$filter_prioridad;
	}
    if ($filter_grupo > 1){
		if ($sql1 == "")
			$sql1='WHERE id_grupo='.$filter_grupo ;
		else
			$sql1 =$sql1.' AND id_grupo='.$filter_grupo;
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
echo "<div class=databox style='width: 800px'>";
echo "<form name='visualizacion' method='POST' action='index.php?sec=incidents&sec2=operation/incidents/incident'>";

if ($usuario_form != "")
    echo "<input type='hidden' name='usuario' value='$usuario_form'>";
if ($texto_form != "")
    echo "<input type='hidden' name='texto' value='$texto_form'>";


echo '<table border=0 cellpadding=0 cellspacing=8>';
echo "<tr>";
echo "<td>".$lang_label["f_state"];
echo "<td>";
echo '<select name="estado" onChange="javascript:this.form.submit();" class="w155">';

if ($filter_estado > 0){
	echo "<option value='".$filter_estado."'>";
    switch ($filter_estado){
        case 0: echo $lang_label["all_inc"]; 
                break;
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
	}
}

echo "<option value='-1'>".$lang_label["all_inc"];
echo "<option value='1'>".$lang_label["status_new"];
echo "<option value='2'>".$lang_label["status_unconfirmed"];
echo "<option value='3'>".$lang_label["status_assigned"];
echo "<option value='4'>".$lang_label["status_assigned"];
echo "<option value='5'>".$lang_label["status_verified"];
echo "<option value='7'>".$lang_label["status_closed"];
echo "<option value='6'>".$lang_label["status_resolved"];

echo "</select> ";
echo '<noscript><input type="submit" class="sub" value="'.$lang_label["show"].'" border="0"></noscript>	</td>';

echo "<td>".$lang_label["f_prio"];
echo '<td>';
echo '<select name="prioridad" onChange="javascript:this.form.submit();" class="w155">';

if ($filter_prioridad > 0){ 
	echo "<option value=".$filter_prioridad.">";
	switch ($filter_prioridad){
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

echo "<td>".$lang_label["f_group"];
// Group combo
echo '<td><select name="grupo" onChange="javascript:this.form.submit();" class="w155">';
if ($filter_grupo > 0){
	echo "<option value=".$filter_grupo.">";

	if ($filter_grupo == 1)
		echo $lang_label["all"]." ".$lang_label["groups"]; // all groups (default)
	else
		echo dame_nombre_grupo($filter_grupo);
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
	$url = "index.php?sec=incidents&sec2=operation/incidents/incident";

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

	// -------------
	// Show headers
	// -------------
	echo "<table cellpadding=4 cellspacing=4 class='databox' width=850>";
	echo "<tr>";
	echo "<th>Id";
	echo "<th>".$lang_label["incident"];
	echo "<th>".$lang_label["group"];
	echo "<th>".lang_string ("status")."<br>".lang_string("resolution");
	echo "<th>".$lang_label["priority"];
	echo "<th width=82>".$lang_label["updated_at"];
	echo "<th width=70>".$lang_label["flags"];
	// echo "<th>".$lang_label["assigned_to"];
	echo "<th>".$lang_label["delete"];
	
	$color = 1;

	// -------------
	// Show DATA TABLE
	// -------------
	while ($row2=mysql_fetch_array($result2)){ 
		$id_group = $row2["id_grupo"];
        $group_name = give_db_value ("nombre", "tgrupo", "id_grupo", $id_group);
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

            switch ($row2["estado"]) {
                case 1: $tdcolor = "datos_red";
                            break;
                case 2: $tdcolor = "datos_red";
                            break;
                case 3: $tdcolor = "datos_yellow";
                            break;
                case 4: $tdcolor = "datos_yellow";
                            break;
                case 5: $tdcolor = "datos_yellow";
                            break;
                case 7: $tdcolor = "datos_green";
                            break;
                case 6: $tdcolor = "datos_green";
                            break;
            }

			echo "<tr>";
			echo "<td class='$tdcolor' align='left'>";
			echo "<font size=1pt><a href='index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=".$row2["id_incidencia"]."'><b>#".$row2["id_incidencia"]."</b></a></td>";

			// Title
			echo "<td class='$tdcolor'><a href='index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=".$row2["id_incidencia"]."'>".substr(clean_output ($row2["titulo"]),0,200);

            // group
            echo "<td class='".$tdcolor."f9'>";
            echo $group_name;

			// Tipo de estado  (Type)
			// (1,'New'), (2,'Unconfirmed'), (3,'Assigned'),
			// (4,'Re-opened'), (5,'Verified'), (6,'Resolved')
			// (7,'Closed');
			echo "<td class='".$tdcolor."f9' align='center'><b>";
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

			// Resolution
			// echo "<td class='$tdcolor'>";
			echo "</b><br>";
			echo give_db_value('name', 'tincident_resolution', 'id', $row2["resolution"]);

			echo "<td class='$tdcolor' align='center'>";
			switch ( $row2["prioridad"] ){
				case 0: echo "<img src='images/flag_white.png' title='Informative'>"; break; // Informative
				case 1: echo "<img src='images/flag_green.png' title='Low'>"; break; // Low
				case 2: echo "<img src='images/flag_yellow.png' title='Medium'>"; break; // Medium
				case 3: echo "<img src='images/flag_orange.png' title='Serious'>"; break; // Serious
				case 4: echo "<img src='images/flag_red.png' title='Very serious'>"; break; // Very serious
				case 10: echo "<img src='images/flag_blue.png' title='Maintance'>"; break; // Maintance
			}

			// Update time
			echo "<td class='".$tdcolor."f9'>".human_time_comparation ( $row2["actualizacion"]);

			// Flags

            echo "<td class='$tdcolor' align='center'>";
            // People participant
            echo "<a href='#' class='tip_people'><span>";
            $people_involved = people_involved_incident ($row2["id_incidencia"]);
            while (sizeof($people_involved)>0){
                echo array_pop ($people_involved). " <br>";
            }
            echo "</span></a>";

			// Check for attachments in this incident
			$file_number = give_number_files_incident ($row2["id_incidencia"]);
			if ($file_number > 0)
				echo '<img src="images/disk.png" valign="bottom"  alt="'.$file_number.'">';

			// Has mail notice activated ?
			$mail_check = give_db_value('notify_email', 'tincidencia', 'id_incidencia', $row2["id_incidencia"]);
			if ($mail_check> 0)
				echo '&nbsp;&nbsp;<img src="images/email_go.png" valign="bottom">';

			// Check for workunits
			$timeused = give_hours_incident ($row2["id_incidencia"]);;
			if ($timeused > 0){
				echo '&nbsp;&nbsp;<img src="images/award_star_silver_1.png" valign="bottom">'.$timeused;
			}

			// echo "<a href='index.php?sec=usuario&sec2=operation/users/user_edit&ver=".$row2["id_usuario"]."'>".$row2["id_usuario"]."</a></td>";
			
			if ((give_acl($id_usuario, $id_group, "IM") ==1) OR ($_SESSION["id_usuario"] ==  $row2["id_usuario"]) ){
			// Only incident owners or incident manager
			// from this group can delete incidents
				echo "<td class='$tdcolor' align='center'><a href='index.php?sec=incidents&sec2=operation/incidents/incident&quick_delete=".$row2["id_incidencia"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a></td>";
			} else
				echo "<td class='$tdcolor' align='center'>";

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
