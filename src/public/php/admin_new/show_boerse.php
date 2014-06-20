<?
include("inc/general.php");

$players = assocs("select id,syndicate, rid from status","id");

/*
INSERT INTO `admin_pages` ( `id` , `kategorie` , `showposition` , `name` , `dateiname` , `visible` , `privilege_level` )
VALUES (
'20', 'Checkstuff', '10', 'Börsenübersicht', 'show_boerse.php', '1', '1'
);
*/
$time = time();

function mytimes($time) {
	$back = date("d.m.y - H:i:s",$time);
	return $back;
}

if(!$_POST['days']){
	$_POST['days'] = 2;
}
if(!$_POST['site'] || $_POST['rid'] != $_POST['last_rid']){
	$_POST['site'] = 1;
}
$_POST['site'] -= 1;

if($_POST['rid']){
	$logs = assocs("SELECT * FROM aktien_logs WHERE rid = ".$_POST['rid']." AND time >= ".($time - 60*60*24*$_POST['days'])." ORDER BY time DESC LIMIT ".($_POST['site'] * 500).", 500");
	$all = assocs("SELECT * FROM aktien_logs WHERE rid = ".$_POST['rid']." AND time >= ".($time - 60*60*24*$_POST['days'])." ORDER BY time DESC");
}
else{
	$logs = assocs("SELECT * FROM aktien_logs WHERE time >= ".($time - 60*60*24*$_POST['days'])." ORDER BY time DESC LIMIT ".($_POST['site'] * 500).", 500");
	$all = assocs("SELECT * FROM aktien_logs WHERE time >= ".($time - 60*60*24*$_POST['days'])." ORDER BY time DESC");
}

$i = $_POST['site'] * 500 + 1;
$table = '';
foreach($logs as $tag => $val){
	$table .= '
		<tr>
			<td align="right">'.$i.'</td>
			<td align="right">'.$val['rid'].'</td>
			<td><a class="ver11s" href="traceuser.php?action=traceuser&ia=trace&konid='.$players[$val['offer_id']]['id'].'">'.$players[$val['offer_id']]['syndicate'].' (#'.$players[$val['offer_id']]['rid'].')</a></td>
			<td><a class="ver11s" href="traceuser.php?action=traceuser&ia=trace&konid='.$players[$val['need_id']]['id'].'">'.$players[$val['need_id']]['syndicate'].' (#'.$players[$val['need_id']]['rid'].')</a></td>
			<td align="right">'.pointit($val['menge']).'</td>
			<td align="right">'.pointit($val['menge'] * $val['preis']).'</td>
			<td align="right">'.pointit($val['preis']).'</td>
			<td align="right">'.mytimes($val['time']).'</td>
		</tr>';
	$i++;
}

$sites = ceil(count($all) / 500);
$select = '';
for($i=1; $i<=$sites; $i++){
	$sel = '';
	if(($_POST['site'] + 1) == $i){
		$sel = 'selected="selected"';
	}
	$select .= '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
}






?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Syndicates - B&ouml;rsenlogs</title>
<LINK REL="stylesheet" HREF="style.css" TYPE="text/css">
</head>

<body>
<div align="center">
	<form method="post" style="width:300px;">
		<fieldset>
			<legend>B&ouml;rsenlogs anzeigen</legend>
			<table>
				<tr>
					<td>Syndikat:</td>
					<td><input type="text" name="rid" size="10" value="<?=$_POST['rid']?>" /><input type="hidden" name="last_rid" value="<?=$_POST['rid']?>"</td>
				</tr>
				<tr>
					<td>Die letzten x Tage:</td>
					<td><input type="text" name="days" size="10" value="<?=$_POST['days']?>" /></td>
				</tr>
				<tr>
					<td>Seite:</td>
					<td><select name="site">
								<?=$select?>
							</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" value="Anzeigen" /></td>
				</tr>
			</table>
		</fieldset>
	</form>
	<br />
	<br />
	<table cellspacing="3px" cellpadding="3px" border="1px">
		<tr>
			<td align="right">#</td>
			<td align="right">Syn</td>
			<td>Anbieter</td>
			<td>K&auml;ufer</td>
			<td align="right">Menge</td>
			<td align="right">Preis</td>
			<td align="right">Kurs</td>
			<td align="right">Zeit</td>
		</tr>
		<?=$table?>
	</table>
</div>
</body>
</html>
