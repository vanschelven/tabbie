<?php /* begin license *
 *
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 *
 *     This file is part of Tabbie
 *
 *     Tabbie is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 *
 *     Tabbie is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with Tabbie; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * end license */

$ntu_controller = "import";
$moduletype="";

require("ntu_bridge.php");
require("view/header.php");
require("view/mainmenu.php");
require("includes/adjudicator.php");
require("includes/dbimport.php");
require_once("includes/dbconnection.php");
?>
<h2>Import Backup</h2>
<p>
Use this page to import SQL files. SQL files can be created by clicking on "backup".<br/>
<b>Warning: this will erase all current data from your Tabbie installation!</b>

<form enctype="multipart/form-data" action="import.php" method="POST">
<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
<label for="uploadedfile">Choose a file to upload:</label> <input name="uploadedfile" type="file" /><br /><br />
<label for="drop_create">Drop and recreate DB?</label> <input name="drop_create" type="checkbox" value="drop" /><br /><br />
<input type="submit" value="Start Import" />
</form>

</p>
<?php
if ( array_key_exists("uploadedfile", @$_FILES)) {
	$problem = false;
	echo <<<WARN
<div id='import-warning' style='display: block; width:100%, padding-top: 15px; padding-bottom: 15px; background: #ffcc00; text-align: center; color: #000000; font-size: +1em;'><strong>CAUTION:&nbsp;</strong> Wait for import to complete before navigating away from this page</div>
WARN;

	if (isset($_POST['drop_create']) && $_POST['drop_create']=="drop") {
		if (!$DBConn->Execute("DROP DATABASE $database_name")) {
			$problem = true;
			print $DBConn->ErrorMsg() . "<br>";
		}
		if (!$DBConn->Execute("CREATE DATABASE $database_name")) {
		$problem = true;
		print $DBConn->ErrorMsg() . "<br>";
		}
		$DBConn->Execute("USE $database_name");
	}

	echo ('<p style="font-family: courier; font-size: 10px;">');
	$contents = file_get_contents($_FILES['uploadedfile']['tmp_name']);
	// Dumps could be generated by us, but also may be taken straight from the server
	// If it's a windows system, carriage returns are \r\n;, if unix, \n; Accommodate.
	$lines = explode(strpos($contents,";\r\n") ? ";\r\n" : ";\n", $contents);
	foreach ($lines as $line) {
		$problem = !execute_query_print_result($line) || $problem; // Return false on error, so negate
	}
	echo('</p>');

	//Upgrade files with 'conflicts' in adjudicator table]
	$query="SHOW COLUMNS FROM adjudicator";
	$result=$DBConn->Execute($query);
	while($row=$result->FetchRow()){
		if($row['Field']=='conflicts'){
			// Does this do what we want? Does it not recreate strikes in many cases?!
			$query=" CREATE TABLE strikes (strike_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, adjud_id INT NOT NULL , team_id INT NULL , univ_id INT NULL , INDEX ( adjud_id ))";
			$r=$DBConn->Execute($query);
			if (!$r) {
				print $DBConn->ErrorMsg();
			}
			$query="SELECT adjud_id, univ_id, conflicts FROM adjudicator";
			$strikeresult=$DBConn->Execute($query);
			while($adjudicator=$strikeresult->FetchRow()){
			    $conflicts = preg_split("/, /", $adjudicator['conflicts'], -1, PREG_SPLIT_NO_EMPTY);
			    foreach ($conflicts as $conflict) {
					$parts = preg_split("/\./", $conflict);
					if (count($parts) == 1) {
					    add_strike_judge_univ($adjudicator['adjud_id'],__get_university_id_by_code($conflict));
					} elseif (count($parts == 2)) {
					    add_strike_judge_team($adjudicator['adjud_id'],__get_team_id_by_codes($parts[0], $parts[1]));
					}
				}
			}
			add_strike_judge_univ($adjudicator['adjud_id'],$adjudicator['univ_id']); //Strike from own institution.
			$query="ALTER TABLE adjudicator DROP COLUMN conflicts";
			$r=$DBConn->Execute($query);
			if (!$r) {
				$problem = true;
				print $DBConn->ErrorMsg();
			}
			print "<p>Conflicts converted to new format.</p>";
		}
	}
	include('draw/adjudicator/simulated_annealing_config.php'); ## Converts settings table
	//Upgrade MyISAM dbs to InnoDB
	$query="SELECT table_name,engine FROM INFORMATION_SCHEMA.TABLES WHERE table_schema='$database_name';";
	$result=$DBConn->Execute($query);
	while($row=$result->FetchRow()){
		if ($row["engine"] == "MyISAM"){
			$query = "ALTER TABLE ".$row["table_name"]." ENGINE=InnoDB;";
			$DBConn->Execute($query);
			print "<p>Converting ".$row["table_name"]." to InnoDB. ".$DBConn->ErrorMsg()."</p>";
		}
	}

	if (! $problem ) {
		echo<<<EOHTML
		<p><b>Imported File Succesfully</b></p>
		<script type='text/javascript'>
		$('#import-warning').css("background", "#00dd00");
		$('#import-warning').html("<strong>Success!&nbsp;</strong> Data successfully imported!");
		</script>
EOHTML;
	} else {
		echo<<<EOHTML
		<p><b>Imported Encountered Problems</b></p>
		<script type='text/javascript'>
		$('#import-warning').css("background", "#dd0000");
		$('#import-warning').css("color", "#ffffff");
		$('#import-warning').html("<strong>Error:&nbsp;</strong> Problem found while importing data, see bold statements below for details");
		</script>
EOHTML;
	}
}
require('view/footer.php');
?>
