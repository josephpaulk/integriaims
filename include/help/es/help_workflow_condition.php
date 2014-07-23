<h1>Condiciones de reglas de flujo de trabajo</h1>

<p>
En general las reglas de workflow se dispararán UNA SOLA vez, de forma que si establece una regla para cambiar por ejemplo, el usuario asignado a la incidencia cuando una incidencia tenga más de 30 dias de vida, y el usuario asignado es X, pero luego manualmente alguien vuelve a poner ese usuario X, la regla NO se volverá a disparar. La única excepción de este comportamiento es cuando la condición es el tiempo de actualización.

</p>
<p>

Si establece una regla para que salte cuando el ticket lleva más de X tiempo sin actualizar, se creará automáticamente una acción por defecto para “actualizar el ticket”. Esto hará que no salte continuamente la condición. Pasado ese X tiempo, el sistema podrá ejecutar de nuevo la misma regla de Workflow. Esta es la excepción, ya que para ninguna otra condición (Prioridad, Propietaro, Estado, Creacion ó Grupo), se podrá volver a ejecutar una regla.

</p>
<p>
Caso típico de uso para este tipo de condición:
<br>
<br>
Necesita enviar un email de aviso a un coordinador, cuando una incidencia de prioridad muy alta y de un grupo determinado lleva más de 5 días sin actualizaciones. 
<br>
<br>
Simplemente tiene que rellenar en la condición “Match all fields”, el grupo específico y la prioriad muy alta, solo para incidencias asignadas. En “Time Update” escogeremos un mes.
</p>

<p>
<?php print_image("images/help/workflow_conditions.png", false); ?>
</p>

<p>
Al añadir la acción de enviar un mail, se creará automáticamente la accion de actualizar el ticket, que dejaremos tal cual, para actualizar el ticket y evitar que siga saltando la regla. 
</p>

<p>
<?php print_image("images/help/workflow_actions.png", false, false); ?>
</p>
<p>
<?php print_image("images/help/workflow_actions2.png", false, false); ?>
</p>
<p>
Al pasar una semana, si no se ha actualizado la incidencia, volverá a saltar la regla y asi de forma indefinida.
</p>

