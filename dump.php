<?php
/**
* @project		Dinero
* @author		Olivier Gaillard
* @version		1.0 du 11/12/2014
* @desc			Dump de la base de données
*/

$properties_filepath = realpath(dirname (__FILE__)).'/inc/properties/'.$_SERVER["HTTP_HOST"].'/properties.ini';
if (!is_file($properties_filepath)) die("Unbale to find :  ".$properties_filepath);
$prop = parse_ini_file($properties_filepath);

backup_tables($prop['db_hostname'],$prop['db_username'],$prop['db_password'],$prop['db_name'], '*');

/* backup the db OR just a table */
function backup_tables($host, $user, $pass, $name, $tables = '*') {
	
	$link = mysqli_connect($host,$user,$pass, $name);
	$return = '';

	//get all of the tables
	if($tables == '*') {
		$tables = array();
		$result = mysqli_query($link, 'SHOW TABLES');
		while($row = mysqli_fetch_row($result)) {
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}
	
	//cycle through
	foreach($tables as $table) {
		$result = mysqli_query($link, 'SELECT * FROM '.$table);
		$num_fields = mysqli_num_fields($result);
		
		$return.= 'DROP TABLE IF EXISTS '.$table.';';
		$row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = mysqli_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j < $num_fields; $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = str_replace("\n","\\n",$row[$j]);
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