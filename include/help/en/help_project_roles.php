<h1>Project and task roles</h1>

<p>
The project manager profile allows access to all the
project options, even though the user was not the owner.
</p>

<table>
<th>Name</th>
<th>Cost</th>
<?php
$sql='SELECT * FROM trole ORDER BY id';
$result=mysql_query($sql);
while ($row=mysql_fetch_array($result)){
	echo "<tr><td valign='top'>".$row["name"]."</td>";
	echo '<td valign="top" align="center">'.$row["cost"].'</td></tr>';
}
?>
</table>
