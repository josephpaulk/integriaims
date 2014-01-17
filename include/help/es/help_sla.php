
<h1>Gesti&oacute;n de SLA</h1>
SLA es la manera de comprobar si la gesti&oacute;n de una incidencia funciona correctamente. El SLA es procesado peri&oacute;dicamente en Integria, usando las tareas programadas cuando se instala.
<br><br>
El SLA es procesado con determinados par&aacute;metros:
<br><ul>
<li>    <b>Nombre:</b> Es el texto que aparece en el combo de selecci&oacute;n para identificar el SLA. 

<li>    <b>Forzar:</b> Hace que el SLA env&iacute;e los emails donde no est&eacute; completado o solo notifique con una se&ntilde;al luminosa. 

<li>    <b>Tiempo min. de respuesta:</b> Muestra en horas el tiempo de respuesta que deber&iacute;a observar entre una notificaci&oacute;n (nuevo incidente o WU) del creador de la incidencia. Despu&eacute;s de este tiempo, el SLA ser&aacute; disparado. Por ejemplo, si es de 4 horas, y un nuevo incidente ha estado 4,1 horas, el SLA ser&aacute; disparado. Por ejemplo, si es un incidente viejo (una semana) y la &uacute;ltima WU es del creador del incidente y es de hace mas de 4 horas, tambi&eacute;n se disparar&aacute; el SLA.

<li>    <b>Tiempo max. de resoluci&oacute;n:</b> Muestra en horas el tiempo de vida m&aacute;ximo de un incidente. Si un incidente es m&aacute;s viejo que este valor y no ha sido cerrado o resuelto, el SLA ser&aacute; disparado.

<li>    <b>Tiempo max. de inactividad:</b> Muestra en horas el tiempo m&aacute;ximo que un incidente puede permanecer sin ninguna actualizaci&oacute;n.

<li>    <b>Nº max. de incidentes abiertos simult&aacute;neamente:</b> Muestra el n&uacute;m,ero total de incidentes que pueden haber simulot&aacute;neamente abiertos. Si hay m&aacute;s, el SLA ser&aacute; disparado. 

<li>    <b>SLA base:</b> Muestra si el SLA est&aacute; relacionado con otro (solo a nivel informativo). 

<li>    <b>Desabilitar el SLA los fines de semana:</b> El SLA no ser&aacute; calculado los fines de semana.

<li>    <b>Hora de inicio del SLA:</b> Hora a partir de la cual el SLA comienza a ser calculado (e.g.: 9 a.m). 

<li>    <b>Hora final del SLA:</b> Hora a partir de la cual el SLA no ser&aacute; calculado (e.g.: 18 hr). 

<li>    <b>Descripci&oacute;n:</b> Texto informativo para describir el SLA. 

<li>    <b>Tiempo max. de inactividad del incidente:</b> Tiempo m&aacute;xima de inactividad del incidente. Despu&eacute;s de este tiempo, Integria enviar&aacute; un email al responsable del incidente para recordarle que el incidente est&aacute; abierto.
</ul>
<h2>
¿Qu&eacute; significa "El SLA ser&aacute; disparado"?</h2>

Significa que el sistema enviar&aacute; una notificaci&oacute;n por email al propietario del incidente, notific&aacute;ndole de que un incidente no cumple los requesitos marcados en el SLA para el incidente implicado. Un incidente puede tener diferentes SLA simult&aacute;neamente, si est&aacute; asociado a diferentes objetos de inventario, y esos objetos de inventario est&aacute;n asociados a diferentes contratos, y esos contratos est&aacute;n asociados a diferentes SLA.
