<h1>Roles de proyecto y tarea</h1>

<p>
El perfil de gestor de proyecto permite el acceso a todas las
opciones del proyecto, aunque el usuario no sea el propietario.
</p>

<table>
<th>Nombre</th>
<th>Coste</th>
<?php
$sql='SELECT * FROM trole ORDER BY id';
$result=mysql_query($sql);
while ($row=mysql_fetch_array($result)){
	echo "<tr><td valign='top'>".$row["name"]."</td>";
	echo '<td valign="top" align="center">'.$row["cost"].'</td></tr>';
}
?>
</table>
