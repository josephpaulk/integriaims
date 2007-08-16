<?php

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

// Load global vars
global $config;

if (check_login() != 0) {
	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated acces","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_user =$_SESSION["id_usuario"];

echo "<h2>".$lang_label["users_"]."</h2>";

echo '<table cellpadding="4" cellspacing="4" width="100%" class="databox_color">';
echo "<th>".$lang_label["user_ID"];
echo "<th>".$lang_label["last_contact"];
echo "<th>".$lang_label["profile"];
echo "<th>".$lang_label["name"];
echo "<th>".$lang_label["description"];

$color = 1;

		$resq1=mysql_query("SELECT * FROM tusuario");
		while ($rowdup=mysql_fetch_array($resq1)){
			$nombre=$rowdup["id_usuario"];
			$nivel =$rowdup["nivel"];
			$comentarios =$rowdup["comentarios"];
			$fecha_registro =$rowdup["fecha_registro"];
			$avatar = $rowdup["avatar"];
			
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
				$tip = "tip";
			}
			else {
				$tdcolor = "datos2";
				$color = 1;
				$tip = "tip2";
			}
			echo "<tr><td class='$tdcolor'><a href='index.php?sec=users&sec2=operation/users/user_edit&ver=".$nombre."'><b>".$nombre."</b></a>";
			echo "<td class='".$tdcolor."f9' width=150>".$fecha_registro;
			echo "<td class='$tdcolor' width=60>";
			echo "<img src='images/avatars/".$avatar."_small.png'>";

			$sql1='SELECT * FROM tusuario_perfil WHERE id_usuario = "'.$nombre.'"';
			$result=mysql_query($sql1);
			echo "<a href='#' class='$tip'>&nbsp;<span>";
			if (mysql_num_rows($result)){
				while ($row=mysql_fetch_array($result)){
					echo dame_perfil($row["id_perfil"])."/ ";
					echo dame_grupo($row["id_grupo"])."<br>";
				}
			}
			else { echo $lang_label["no_profile"]; }
			echo "</span></a>";
			echo "<td class='$tdcolor' width='100'>".substr($rowdup["nombre_real"],0,16);
			echo "<td class='$tdcolor'>".$comentarios;
		}
	


echo "</table>";
?>


<h3><?php echo $lang_label["definedprofiles"] ?><a href='help/<?php echo $help_code ?>/chap2.php#21' target='_help' class='help'>&nbsp;<span><?php echo $lang_label["help"] ?></span></a></h3>

<table cellpadding=3 cellspacing=3 border=0 class='databox_color'>
<?php

	$query_del1="SELECT * FROM tprofile";
	$resq1=mysql_query($query_del1);
	echo "<tr>";
/*
  `name`
  `ir` tinyint(1) NOT NULL default '0',
  `iw` tinyint(1) NOT NULL default '0',
  `im` tinyint(1) NOT NULL default '0',
  `um` tinyint(1) NOT NULL default '0',
  `dm` tinyint(1) NOT NULL default '0',
  `fm` tinyint(1) NOT NULL default '0',
  `ar` tinyint(1) NOT NULL default '0',
  `aw` tinyint(1) NOT NULL default '0',
  `am` tinyint(1) NOT NULL default '0',
  `pr` tinyint(1) NOT NULL default '0',
  `pw` tinyint(1) NOT NULL default '0',
  `tw` tinyint(1) NOT NULL default '0',
  `tm` tinyint(1) NOT NULL default '0',
*/
  
	echo "<th width='180px'><font size=1>".$lang_label["profiles"];
	echo "<th width='40px'><font size=1>IR<a href='#' class='tipp'>&nbsp;<span>".$help_label["IR"]."</span></a>";
	echo "<th width='40px'><font size=1>IW<a href='#' class='tipp'>&nbsp;<span>".$help_label["IW"]."</span></a>";
	echo "<th width='40px'><font size=1>IM<a href='#' class='tipp'>&nbsp;<span>".$help_label["IM"]."</span></a>";
	
	echo "<th width='40px'><font size=1>UM<a href='#' class='tipp'>&nbsp;<span>".$help_label["UM"]."</span></a>";
	echo "<th width='40px'><font size=1>DM<a href='#' class='tipp'>&nbsp;<span>".$help_label["DM"]."</span></a>";
	echo "<th width='40px'><font size=1>FM<a href='#' class='tipp'>&nbsp;<span>".$help_label["FM"]."</span></a>";

	echo "<th width='40px'><font size=1>AR<a href='#' class='tipp'>&nbsp;<span>".$help_label["AR"]."</span></a>";
	echo "<th width='40px'><font size=1>AW<a href='#' class='tipp'>&nbsp;<span>".$help_label["AW"]."</span></a>";
	echo "<th width='40px'><font size=1>AM<a href='#' class='tipp'>&nbsp;<span>".$help_label["AM"]."</span></a>";

	echo "<th width='40px'><font size=1>PR<a href='#' class='tipp'>&nbsp;<span>".$help_label["PR"]."</span></a>";
	echo "<th width='40px'><font size=1>PW<a href='#' class='tipp'>&nbsp;<span>".$help_label["PW"]."</span></a>";

	echo "<th width='40px'><font size=1>TW<a href='#' class='tipp'>&nbsp;<span>".$help_label["TW"]."</span></a>";
	echo "<th width='40px'><font size=1>TM<a href='#' class='tipp'>&nbsp;<span>".$help_label["TM"]."</span></a>";
	$color = 1;
	while ($rowdup=mysql_fetch_array($resq1)){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		}
		else {
			$tdcolor = "datos2";
			$color = 1;
		}
		$id_perfil = $rowdup["id"];
		$nombre=$rowdup["name"];
		
		$ir = $rowdup["ir"];
		$iw = $rowdup["iw"];
		$im = $rowdup["im"];

		$um = $rowdup["um"];
		$dm = $rowdup["dm"];
		$fm = $rowdup["fm"];

		$ar = $rowdup["ar"];
		$aw = $rowdup["aw"];
		$am = $rowdup["am"];

		$pr = $rowdup["pr"];
		$pw = $rowdup["pw"];
		$tw = $rowdup["tw"];
		$tm = $rowdup["tm"];

		echo "<tr><td class='$tdcolor"."_id'>".$nombre;
		
		echo "<td class='$tdcolor'>";
		if ($ir == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($iw == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($im == 1) echo "<img src='images/ok.png' border=0>";

		echo "<td class='$tdcolor'>";
		if ($um == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($dm == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($fm == 1) echo "<img src='images/ok.png' border=0>";
	// agenda
		echo "<td class='$tdcolor'>";
		if ($ar == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($aw == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($am == 1) echo "<img src='images/ok.png' border=0>";
	// Project
		echo "<td class='$tdcolor'>";
		if ($pr == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($pw == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($tw== 1) echo "<img src='images/ok.png' border=0>";

		echo "<td class='$tdcolor'>";
		if ($tm== 1) echo "<img src='images/ok.png' border=0>";

	}
echo "</div></table>";
?>
