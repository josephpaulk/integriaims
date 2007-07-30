<?php
check_login();

echo "<img src='images/bunnyicon_small.gif' class='logoimage' border=0>";
echo "<div width=100%>";
echo "<span>";
$id_usuario = clean_input ($_SESSION["id_usuario"]);
echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&amp;ver=".$_SESSION["id_usuario"]."'>";
if (dame_admin($id_usuario)==1)
	echo "<img src='images/user_suit.png'> ";
else
	echo "<img src='images/user_green.png'> ";
echo $lang_label["has_connected"].' [ <b><font color="#ffffff">'. $id_usuario. '</b></font> ]</a>';
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<a href='index.php?bye=bye'><img src='images/lock.png'> ". $lang_label["logout"]."</A>";
echo "</span>";
echo "</div>";
?>