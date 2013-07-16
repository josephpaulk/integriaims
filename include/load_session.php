<?   

function mysql_session_open ($save_path, $session_name) {  
	
	global $config;     
	mysql_pconnect($config['dbhost'],$config['dbuser'],$config['dbpass']);
	mysql_select_db($config['dbname']);   
	return true;
} 

function mysql_session_close() {  
	return true;
} 

function mysql_session_read ($SessionID) {        

    $SessionID = addslashes($SessionID); 
    
	 $session_data = mysql_query("SELECT data FROM tsessions_php                   
		WHERE id_session = '$SessionID'") or die(db_error_message());
	
	if (mysql_numrows($session_data) == 1) {             
		return mysql_result($session_data, 0);         
	} else {             
		return false;         
	}     
} 

function mysql_session_write ($SessionID, $val) {     

    $SessionID = addslashes($SessionID);         
    $val = addslashes($val); 

    $SessionExists = mysql_result(mysql_query("SELECT COUNT(*) FROM tsessions_php
										WHERE id_session = '$SessionID'"), 0); 

    if ($SessionExists == 0) {             
		$retval = mysql_query("INSERT INTO tsessions_php   
							(id_session, last_active, Data) 
							VALUES ('$SessionID', UNIX_TIMESTAMP(NOW()), '$val')") 
					or die(db_error_message());         
	} else {          
		$retval = mysql_query("UPDATE tsessions_php SET data = '$val', last_active = UNIX_TIMESTAMP(NOW()) 
					WHERE id_session = '$SessionID'") or die(db_error_message());             
		if (mysql_affected_rows() == 0) {
			error_log("unable to update session data for session $SessionID");             
		}
	} 

	return $retval;     
} 

function mysql_session_destroy ($SessionID) {        


    $SessionID = addslashes($SessionID); 

    $retval = mysql_query("DELETE FROM tsessions_php 
				WHERE id_session = '$SessionID'") or die(db_error_message());
	return $retval;
} 

function mysql_session_gc ($maxlifetime = 300) {
	        
	$CutoffTime = time() - $maxlifetime;         
	$retval = mysql_query("DELETE FROM tsessions_php 
			WHERE last_active < $CutoffTime") or die(db_error_message());         
	return $retval;     
} 

session_set_save_handler ('mysql_session_open', 'mysql_session_close', 'mysql_session_read', 'mysql_session_write', 'mysql_session_destroy', 'mysql_session_gc'); 

?>

