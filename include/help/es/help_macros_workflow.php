<h1>Macros</h1>

<p>
El asunto y el cuerpo del email pueden formarse mediante macros. A continuación se explican todas las macros disponibles:
</p>
<br>

<p>

<li>_incident_id_ = ID del ticket
<li>_incident_title_ = Título del ticket
<li>_creation_timestamp_ = Fecha/Hora de la creación del ticke
<li>_group_ = Grupo asignado al ticket
<li>_update_timestamp_ = La última vez que se actualizó el ticket
<li>_author_ = Creador del ticket
<li>_owner_ = Usuario que controla el ticket
<li>_priority_ = Prioridad del ticket
<li>_access_url_ = Ruta de acceso del ticket
<li>_sitename_ = Nombre del sitio, tal y como se haya definido en el setup
<li>_fullname_ = Nombre completo del usuario que recibe el correo
<li>_username_ = Nombre del usuario que recibe el correo (login name)
<li>_status_ = Estado del ticket
<li>_resolution_ = Resolución del ticket
<li>_incident_epilog_ = Epílogo del ticket
<li>_incident_closed_by_ = Usuario que cierra el ticket
<li>_incident_owner_email_: Email del usuario propietario.</li>
<li>_incident_group_email_: Email del grupo asignado.</li>
<li>_incident_author_email_: Email del usuario creador del ticket.</li>

</p>

<p>
<b>Ejemplo de Para:</b>
<br>
_incident_owner_email_
</p>

<p>
<b>Ejemplo de Asunto:</b>
<br>
Incident #_incident_id_ _incident_title_ 
</p>

<p>
<b>Ejemplo de Cuerpo de mensaje:</b>
<br>
Ticket #_incident_id_ ((_incident_title_))
<br>
   _access_url_
<br>
===================================================
<br>
    ID          : #_incident_id_ - _incident_title_
<br>
    CREATED ON  : _creation_timestamp_
<br>
    LAST UPDATE : _update_timestamp_
<br>
    GROUP       : _group_
<br>
    AUTHOR      : _author_
<br>
    ASSIGNED TO : _owner_
<br>
    PRIORITY    : _priority_
<br>
   
===================================================
<br>

_incident_main_text_
<br>
===================================================
<br>
</p>



