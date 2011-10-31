<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

check_login ();

function load_file ($users_file, $group, $profile, $nivel) {
	$file_handle = fopen($users_file, "r");
	global $config;
	
	while (!feof($file_handle)) {
		$line = fgets($file_handle);
		
		preg_match_all('/(.*),/',$line,$matches);
		$values = explode(',',$line);
		
		$id_usuario = $values[0];
		$pass = $values[1];
		$pass = md5($pass);
		$nombre_real = $values[2];
		$mail = $values[3];
		$tlf = $values[4];
		$desc = $values[5];
		
		$value = array(
			'id_usuario' => $id_usuario,
			'nombre_real' => $nombre_real,
			'password' => $pass,
			'comentarios' => $desc,
			'direccion' => $mail,
			'telefono' => $tlf,
			'nivel' => $nivel);
			
			if (($id_usuario!='')&&($nombre_real!='')){
				if ($id_usuario == get_db_value ('id_usuario', 'tusuario', 'id_usuario', $id_usuario)){
					echo "<h3 class='error'>" . __ ('User '). $id_usuario . __(' already exists') . "</h3>";
				} else {
					$resul = process_sql_insert('tusuario', $value);
	
					if ($resul==false){
						$value2 = array(
							'id_usuario' => $id_usuario,
							'id_perfil' => $profile,
							'id_grupo' => $group,
							'assigned_by' => $config["id_user"]
						);
						
						if ($id_usuario!=''){
							process_sql_insert('tusuario_perfil', $value2);
						}
					}
				}
			}		
	}

	fclose($file_handle);
	return;
}


echo "<b>".__('IMPORT USERS FROM CSV')."</b>". print_help_tip (__("A CSV file stores tabular data (numbers and text) in plain-text form. The columns are separated by commas."), true);

$upload_file = (int) get_parameter('upload_file');
$group = (int)get_parameter('group');
$profile = (int)get_parameter('perfil', 1);
$nivel = (int)get_parameter('nivel');
if ($upload_file) {
	if ($_FILES["file"]["error"] == 0) {
		if ($_FILES["file"]["type"] != 'text/csv') {
			echo "<h3 class='error'>" . __ ('Unsupported file type') . "</h3>";
		}
		else {
			load_file ($_FILES["file"]["tmp_name"], $group, $profile, $nivel);
		}
	}
}

$table->width = '60%';
$table->size = array ();
$table->size[0] = '120px';
$table->align[3] = "right";
$table->data = array ();

$table->data[0][0] = combo_groups_visible_for_me ($config['id_user'], 'group', 0, 'TW', $id_group, true);

$table->data[1][0] = "<b>".__('Profiles')."</b>";
$table->data[2][0] = "<select name='perfil' class='w155'>";
	$sql='SELECT * FROM tprofile ORDER BY name';
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		$table->data[2][0] .= "<option value='".$row["id"]."'>".$row["name"];
	}
$table->data[2][0] .= '</select>';

$table->data[3][0] = "<b>".__('Global profile')."</b>";
$table->data[4][0] = __('Standard user').'&nbsp;<input type="radio" class="chk" name="nivel" value="0" checked>';
$table->data[4][0] .= "&nbsp;&nbsp;&nbsp;&nbsp;";
$table->data[4][0] .= __('External user').'&nbsp;<input type="radio" class="chk" name="nivel" value="-1">';

$table->data[5][0] = "<b>".__('Load file')."</b>";
$table->data[6][0] = '<input name="file" type="file" /><br />';
$table->data[6][3] = '<input type="submit" class="sub ok" value="' . __('Upload File') . '" />';
echo '<form enctype="multipart/form-data" action="index.php?sec=users&sec2=godmode/usuarios/import_from_csv" method="POST">';
print_input_hidden ('upload_file', 1);
print_table ($table);
echo '<div style="width: 700px" class="action-buttons">';

echo '</div>';
echo '</form>';

?>
