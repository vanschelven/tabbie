<?
/******************************************************************************
File    :   showround.inc

Author  :   AK

Purpose :   Display the speaker standings at the end of a selected round
           
******************************************************************************/

$action=@$_GET['action'];
$roundno=$round;

$list=trim(@$_POST['list']);
if (!$list)
{	$list=trim(@$_GET['list']); //list : all, esl, break, eslbreak
	if (!$list) $list="all"; //set to all if empty
}

$validate=1;

if ($roundno > $numresults)
{   
    $msg[]="Invalid round selected.";
    $msg[]="<BR>";
    $validate=0;
}

$title = "Speaker Standings";

switch($list)
{
    case "all"   :      break;
    case "esl"   :	$title.= " (ESL)";
    			break;
    default      :	$list = "all";
    			break;
}
                
switch($action)
{
    case "showround":   $title.=" - Round $roundno";
                        break;
    default:
                        $title.=" - Round $roundno";
                        $action="showround";
                        break;
}

include("header.inc");

echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

if ($validate == 0)
{	for($x=0;$x<count($msg);$x++)
	echo "<p class=\"err\">".$msg[$x]."</p>\n";

}
else
{	if ($action == "showround")
	{
		$warning="null";
		$query = "SELECT speaker.speaker_id, speaker.team_id, speaker_name FROM speaker AS speaker";
	
		if ($list=="esl")
			$query.=", team AS team WHERE speaker.team_id = team.team_id and team.esl = 'Y' ";
	
		$result = mysql_query($query);
		$speaker_count=mysql_num_rows($result);
		
		//echo "query => $query <BR>";
		//echo "$speaker_count <BR>";
		
		// Create array with all the team ids
		$index=0;
		while ($row=mysql_fetch_assoc($result))
		{
		    $speaker_array[$index] = array("index" => $index++,
		                        "speakerid" => $row['speaker_id'],
		                        "speakername" => $row['speaker_name'],
		                        "teamid" => $row['team_id'],
		                        "teamname" => ' ',
		                        "points" => 0);    
		}
		
		
		// Fill up all the team names
		$index=0;
		while ($index<count($speaker_array))
		{
		    $teamid = $speaker_array[$index]["teamid"];
		    $name_query = "SELECT univ.univ_code AS univ_code, team.team_code AS team_code ";
		    $name_query .= "FROM university AS univ, team AS team ";
		    $name_query .= "WHERE team.team_id=$teamid AND team.univ_id = univ.univ_id ";
		    $name_result = mysql_query($name_query);
		    $name_row = mysql_fetch_assoc($name_result);
		    $teamname = $name_row['univ_code'].' '.$name_row['team_code'];
		    $speaker_array[$index++]["teamname"] = $teamname;
		}
		
		
		// Run through the array and add the points
		foreach($speaker_array as $cc) 
		{
			$index = $cc["index"];
			$speaker_id = $cc["speakerid"];
			$points = 0;
			for ($x=1;$x<=$roundno;$x++)
			{
				$score_query = "SELECT points FROM speaker_round_$x ";
				$score_query .= "WHERE speaker_id = '$speaker_id' ";
				$score_result = mysql_query($score_query);
				$score_row = mysql_fetch_assoc($score_result);
				      $points += $score_row['points'];
			}
			$speaker_array[$index]["points"] = $points;
		}
		
		
		// Sorting the array
		function cmp ($a, $b) {
		    return ($a["points"] > $b["points"]) ? -1 : 1;
		}
		usort($speaker_array, "cmp");
		
		
		// Displaying the standings
		echo "<table>\n";
		echo "<tr><th>Position</th><th>Speaker Name</th><th>Team Name</th><th>Points</th></tr>\n";
		for ($x=0;$x<count($speaker_array);$x++)
		{
		    echo "<tr>\n";
	    		echo "<td>".($x+1)."</td>\n";
		    	echo "<td>".$speaker_array[$x]["speakername"]."</td>\n";
		    	echo "<td>".$speaker_array[$x]["teamname"]."</td>\n";
		    	echo "<td>".$speaker_array[$x]["points"]."</td>\n";
		    echo "</tr>\n";
		    
		}
		echo "</table>\n";
		
		/*
		//Code for testing the present array content
		foreach($speaker_array as $cc) {
			echo "$cc -> ";
			foreach($cc as $k => $dd) {
				print ". . .$k => $dd";
			}
			print "<BR>";
		}
		*/
		
		//echo "DONE PROCESSING! ";
	}
}

?>
</div>
</body>
</html>