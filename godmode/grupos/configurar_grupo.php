<?php

    global $config;
    
    check_login();
    
    if (give_acl($config["id_user"], 0, "UM")==0) {
        audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access User Management");
        require ("general/noaccess.php");
        exit;
    }
    
    $id_user = $config["id_user"];

	// Inic vars
	$id_grupo = "";
	$nombre = "";
	$creacion_grupo = get_parameter ("creacion_grupo",0);
		
	if (isset($_GET["id_grupo"])){
		// Conecto con la BBDD
		$id_grupo = get_parameter ("id_grupo", "1");
		$sql1='SELECT nombre, icon FROM tgrupo WHERE id_grupo = '.$id_grupo;
		$result=mysql_query($sql1);
		if ($row=mysql_fetch_array($result)){
			$nombre = $row["nombre"];
			$icono = $row["icon"];
		} else
			{
			echo "<h3 class='error'>".$lang_label["group_error"]."</h3>";
			echo "</table>";
			include ("general/footer.php");
			exit;
			}
	}

	echo "<h2>".$lang_label["group_management"]."</h2>";
	if (isset($_GET["creacion_grupo"])) {
        echo "<h3>".$lang_label["create_group"]."</h3>";
    }
	if (isset($_GET["id_grupo"])) {
        echo "<h3>".$lang_label["update_group"]."</h3>";
    }
	
    echo '<table width="450" cellpadding=4 cellspacing=4 class="databox"><form name="grupo" method="post" action="index.php?sec=users&sec2=godmode/grupos/lista_grupos">';

	if ($creacion_grupo == 1)
		echo "<input type='hidden' name='crear_grupo' value='1'>";
	else {
		echo "<input type='hidden' name='update_grupo' value='1'>";
		echo "<input type='hidden' name='id_grupo' value='".$id_grupo."'>";
	}

    echo '<tr><td class="datos">'.$lang_label["group_name"].'</td>';
    echo '<td class="datos">';
    echo '<input type="text" name="nombre" size="35" value="'.$nombre.'">';
    echo '</td></tr>';
    echo "<tr><td class='datos2'>";
	echo $lang_label["icon"];
	echo '<td class="datos2">';		
	echo '<select name="icon">';
	if ($icono != ""){
		echo '<option>' . $icono;
	}
		
	$ficheros = list_files ('images/groups_small/', "png", 1, 0);
	$size = count ($ficheros);
	for ($i = 0; $i < $size; $i++) {
		echo "<option>".substr($ficheros[$i],0,strlen($ficheros[$i])-4);
	}
	echo '</select>';
    echo '<tr><td colspan="3" align="right">';
    if (isset($_GET["creacion_grupo"])){
	    echo "<input name='crtbutton' type='submit' class='sub' 
	    value='".$lang_label["create"]."'>";
	} else {
	    echo "<input name='uptbutton' type='submit' class='sub' 
	    value='".$lang_label["update"]."'>";
	} 
    echo "</form></table>";

?>