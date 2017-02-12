<?php

$path =  realpath(dirname (__FILE__)).'/inc/properties';
$properties_filepath = $path."/". $_SERVER["HTTP_HOST"].'/properties.ini';
$prop = parse_ini_file($properties_filepath);

backup_tables($prop['db_hostname'],$prop['db_username'],$prop['db_password'],$prop['db_name'], '*');

/* backup the db OR just a table */
function backup_tables($host, $user, $pass, $name, $tables = '*') {
	
	$link = mysql_connect($host,$user,$pass);
	mysql_select_db($name,$link);
	$return = '';

	//get all of the tables
	if($tables == '*') {
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result)) {
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}
	
	//cycle through
	foreach($tables as $table) {
		$result = mysql_query('SELECT * FROM '.$table);
		$num_fields = mysql_num_fields($result);
		
		$return.= 'DROP TABLE IF EXISTS '.$table.';';
		$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = mysql_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j < $num_fields; $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = ereg_replace("\n","\\n",$row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j < ($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}
	
	//save file
	$filename = 'dumps/db-backup-'.time().'.sql';
    $handle = fopen($filename,'w+');
	fwrite($handle,$return);
	fclose($handle);
    echo "Fichier : <a href='".$filename."'>". basename($filename)."</a>";
}