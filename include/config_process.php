<?PHP

// Integria 1.1 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

// Integria version
$config["build"]="80525";
$config["version"]="v1.1";
$config["build_version"] = $config["build"];

// Read remaining config tokens from DB
if (! mysql_connect($config["dbhost"],$config["dbuser"],$config["dbpass"])){ 

//Non-persistent connection. If you want persistent conn change it to mysql_pconnect()
    exit ('<html><head><title>Integria Error</title>
    <link rel="stylesheet" href="./include/styles/integria.css" type="text/css">
    </head><body><div align="center">
    <div id="db_f">
        <div>
        <a href="index.php"><img src="images/integria_white.png" border="0"></a>
        </div>
    <div id="db_ftxt">
        <h1 id="log_f" class="error">Integria Error DB-001</h1>
        Cannot connect with Database, please check your database setup in the 
        <b>./include/config.php</b> file and read documentation.<i><br><br>
        Probably any of your user/database/hostname values are incorrect or 
        database is not running.</i><br><br><font class="error">
        <b>MySQL ERROR:</b> '. mysql_error().'</font>
        <br>&nbsp;
    </div>
    </div></body></html>');
}
mysql_select_db($config["dbname"]);
if($result2=mysql_query("SELECT * FROM tconfig")){
    while ($row2=mysql_fetch_array($result2)){
        switch ($row2["token"]) {
        case "language_code": $config["language_code"]=$row2["value"];
                        break;
        case "block_size": $config["block_size"]=$row2["value"];
                        break;
        case "graph_res": $config["graph_res"]=$row2["value"];
                        break;
        case "style": $config["style"]=$row2["value"];
                        break;
        }
    }
} else {
     exit ('<html><head><title>Integria Error</title>
             <link rel="stylesheet" href="./include/styles/integria.css" type="text/css">
             </head><body><div align="center">
             <div id="db_f">
                 <div>
                 <a href="index.php"><img src="images/integria_white.png" border="0"></a>
                 </div>
             <div id="db_ftxt">
                 <h1 id="log_f" class="error">Integria Error DB-002</h1>
                 Cannot load configuration variables. Please check your database setup in the
                 <b>./include/config.php</b> file and read documentation.<i><br><br>
                  Probably database schema is created but there are no data inside it or you have a problem with DB access credentials.
                 </i><br>
             </div>
             </div></body></html>');
}   

if (!isset($config["language_code"]))
    $config["language_code"] = "en";

if ($config["language_code"] == 'ast_es') {
    $help_code='ast';
    }
else $help_code = substr($config["language_code"],0,2);

?>
