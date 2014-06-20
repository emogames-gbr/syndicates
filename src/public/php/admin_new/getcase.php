<?

include("inc/general.php");

$case_id = int($case_id);
$type = int($type);

if (isSet($_GET['type'])) {
	$case = assoc("select * from admin_case where type = $type and status = 0 order by starttime asc limit 1");
	$case_id = $case['id'];
}
if ($case_id) {
	$casedata = assoc("select * from admin_case where id = $case_id");
	if ($casedata['status'] == 0) {
		select("update admin_case set processor_id = $id, status = 1 where id = $case_id");
		select("insert into admin_case_messages (case_id, type, message_text, time) values ($case_id, 4, '<b>".$user['username']."</b> übernimmt den Case', $time)");
		header("Location: view_case.php?case_id=$case_id");
	}
	else header("Location: main.php");
}
if (!$case_id) { header("Location: main.php"); exit(); }


?>

