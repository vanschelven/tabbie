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
require_once("includes/backend.php");
require_once("draw/adjudicator/simulated_annealing_config.php");
require_once("includes/adjudicator.php");

$roundno=(isset($_GET['roundno']) ? $_GET['roundno'] : 0);
$slide=@$_GET['slide'];
$norefresh=@$_GET['norefresh'];
$query="SELECT COUNT(*) AS count FROM draws WHERE round_no=?";
$result=qp($query, array($roundno));
$row=$result->FetchRow();
$maxrooms=$row["count"];
$refreshspeed=$scoring_factors["draw_table_speed"];

$intslide=intval($slide);
if($intslide > $maxrooms) $slide="premotion";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<?php
	if((0 < $intslide) && ($intslide <= $maxrooms) && (!$norefresh)) 
	{
		$newslide=$slide+1;
		echo("<meta http-equiv='refresh' content='".$refreshspeed.";draw_table_display.php?roundno=$roundno&slide=$newslide'/>");
	}
	?>
	<link rel="stylesheet" href="view/table/css/tablepageold.css" type="text/css" charset="utf-8"/>
    <title>Draw : Round <?= $roundno ?></title>    
</head>

<body>
	<div class="canvas">
		<div class="header">
			Draw for Round <?= $roundno ?>
		</div>
		<?php
		if($slide == "0") {
			echo("<div class='notice'><a href='draw_table_display.php?roundno=".$roundno."&slide=1'>Draw Round</a></div>");
		} elseif($slide=="premotion") {
			echo("<div class='notice'><a href='draw_table_display.php?roundno=".$roundno."&slide=motion'>Motion</a><br /><a href='draw_table_display.php?roundno=".$roundno."&slide=1' style='font-size: 0.4em'>Display draw again</a></div>");
		} elseif($slide=="motion") {
			echo("<div class='notice'>" . get_motion_for_round($roundno) . "</div>");
		} else {
			?><div class='container'><?php
			$query = "SELECT * FROM draws WHERE round_no=? ORDER BY venue_id ASC";
			$db_result=qp($query, array($roundno));
			for($i=1;$i<$intslide;$i++) {
				$row=$db_result->FetchRow();
			}
			$row = $db_result->FetchRow();
			echo("<div class='venue'>".venue_name($row["venue_id"])."</div>");
			echo("<div class='prop'>Proposition</div>");
			echo("<div class='opp'>Opposition</div>");
			echo("<div class='og'> ".team_code_long_table($row["og"])."</div>");
			echo("<div class='oo'>".team_code_long_table($row["oo"])."</div>");
			echo("<br />");
			echo("<div class='cg'>".team_code_long_table($row["cg"])."</div>");
			echo("<div class='co'>".team_code_long_table($row["co"])."</div>");	
		
			$debate_id = $row['debate_id'];
			echo("<div class='chair'>".get_chair($roundno, $debate_id)."</div>");
			$p = get_panel($roundno, $debate_id);
			if ($p) {
				echo ("<div class='panelist'>");
				$first=1;
				foreach ($p as $pan) {
					if ($first==1) {
						echo $pan;
					}else {
						echo ", " . $pan;
					}
					$first=0;
				}
				echo ("</div>");
			}
			$t = get_trainees($roundno, $debate_id);
			if($t){
				echo ("<div class='trainee'>");
				$first=1;
				foreach ($t as $trainee) {
					if ($first==1) {
						echo $trainee. " (t)";
					}else {
						echo ", " .$trainee. " (t)";
					}
					$first=0;
				}
				echo ("</div>");
			}
		}

?>                        
    </div></div><div class="push"></div>
	<div class="footer"><!-- BEGIN class footer-->
		Created with Tabbie, see <a href="http://smoothtournament.com">http://smoothtournament.com</a> and <a href="http://tabbie.wikidot.com">http://tabbie.wikidot.com</a>.
	</div><!-- END class footer-->
</body>
</html>
