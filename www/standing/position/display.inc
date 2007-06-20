<?
/******************************************************************************
File    :   display.inc

Author  :   AK

Purpose :   Display the team standings.
           
******************************************************************************/

$action=@$_GET['action'];

$title = "Position Count";

switch($action)
{
    case "display":     $title.=" - Round $round";
                        break;
    default:
                        $title.=" - Round $round";
                        $action="display";
                        break;
}

include("header.inc");

echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

if ($action == "display")
{
	$warning="null";
	$query="SELECT team.team_id AS team_id, univ.univ_code AS univ_code, team.team_code AS team_code ";
	$query.="FROM team AS team, university AS univ ";
	$query.="WHERE team.univ_id=univ.univ_id ";
	$query.="ORDER BY univ.univ_code, team.team_code ";

	$result = mysql_query($query);
	$team_count=mysql_num_rows($result);
	
	//echo "query => $query <br/>";
	//echo "$team_count <br/>";
			
	// Create array with all the team ids
	$index=0;
	while ($row=mysql_fetch_assoc($result))
	{
	    $team_array[$index] = array("index" => $index++,
	                        "teamid" => $row['team_id'],
	                        "teamname" => $row['univ_code'].' '.$row['team_code'],
	                        "og" => 0,
	                        "oo" => 0,
	                        "cg" => 0,
	                        "co" => 0);    
	}
	
	// Run through the array and add the points
	function addPoints($team_array, $round) {
		$result = array();
		foreach($team_array as $team) {
			$team_id = $team["teamid"];
			$og = 0;
			$oo = 0;
			$cg = 0;
			$co = 0;
			for ($x = 1; $x <= $round; $x++)
			{
				// Check for OG
				$score_query = "SELECT og FROM draw_round_$x WHERE og = '$team_id' ";
				$score_result = mysql_query($score_query);
				$score_count = mysql_num_rows($score_result);
				if ($score_count > 0)
					$og++;
		
				// Check for OO
				$score_query = "SELECT oo FROM draw_round_$x WHERE oo = '$team_id' ";
				$score_result = mysql_query($score_query);
				$score_count = mysql_num_rows($score_result);
				if ($score_count > 0)
					$oo++;
		
				// Check for CG
				$score_query = "SELECT cg FROM draw_round_$x WHERE cg = '$team_id' ";
				$score_result = mysql_query($score_query);
				$score_count = mysql_num_rows($score_result);
				if ($score_count > 0)
					$cg++;
				
				// Check for CO
				$score_query = "SELECT co FROM draw_round_$x WHERE co = '$team_id' ";
				$score_result = mysql_query($score_query);
				$score_count = mysql_num_rows($score_result);
				if ($score_count > 0)
					$co++;
	
			}
			$team["og"] = $og;
			$team["oo"] = $oo;
			$team["cg"] = $cg;
			$team["co"] = $co;
			$result[] = $team;
		}
		return $result;
	}
	$team_array = addPoints($team_array, $round);
	
	// Displaying the standings
	echo "<table>\n";
	echo "<tr><th>Team Name</th><th>OG</th><th>OO</th><th>CG</th><th>CO</th></tr>\n";
	foreach ($team_array as $team) {
	    echo "<tr>\n";
	    	echo "<td>".$team["teamname"]."</td>\n";
	    	echo "<td>".$team["og"]."</td>\n";
	    	echo "<td>".$team["oo"]."</td>\n";
	    	echo "<td>".$team["cg"]."</td>\n";
	    	echo "<td>".$team["co"]."</td>\n";
	    echo "</tr>\n";
	}
	echo "</table>\n";
}	
	
?>
</div>
</body>
</html>