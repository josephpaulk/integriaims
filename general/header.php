<?php

echo "<img src='images/frits_logo.gif' class='logoimage' border=0>";

echo "<div width=100%>";
echo "<span>";
if (isset ($_SESSION["id_usuario"])){
	$id_usuario = entrada_limpia ($_SESSION["id_usuario"]);
	echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&amp;ver=".$_SESSION["id_usuario"]."'>";
	if (dame_admin($id_usuario)==1)
		echo "<img src='images/user_suit.png'> ";
	else
		echo "<img src='images/user_green.png'> ";
	echo $lang_label["has_connected"].' [ <b>'. $id_usuario. '</b> ]</a>';
	echo "</span>";
echo "<span>";
echo "</span>";
	echo "<span>";
	echo "<a href='index.php?sec=main'><img src='images/information.png' valign='bottom'> ". $lang_label["information"]."</A>";
	echo "</span>";
echo "<span>";
echo "</span>";
	echo "<span>";
	echo "<a href='index.php?bye=bye'><img src='images/lock.png'> ". $lang_label["logout"]."</A>";
	echo "</span>";
}
echo "</div>";
?>