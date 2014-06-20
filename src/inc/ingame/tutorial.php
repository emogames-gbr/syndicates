<?php
//by dragon 12 tutorial include file

$menu_box = array('show' => false);
$replace_pattern = array('/{{amount}}/', '/{{reward}}/');
$replace_text = array( $current_tut['amount'], $current_tut['reward']);

if(!$current_tut['menu_item'] == null && (strpos($_SERVER['PHP_SELF'], $current_tut['menu_item'].'.php', 0) === false)) {
	$menu_box['show'] = true;
	$menu_box['item'] = $current_tut['menu_item'];
	$menu_box['text'] = $current_tut['link_description'];
} else {//on target site
	if(isset($_GET['tid']) && isset($_GET['tid']) && $_GET['success'] == 'true' && $_GET['tid'] == $current_tut['id']) {
		select('INSERT INTO user_finished_tutorial (konzern_id, tutorial_id, confirmed) VALUES ('.$id.', '.$current_tut['id'].', '.$current_tut['def_confirm'].')');
	} else if(single('SELECT count(*) FROM user_finished_tutorial WHERE konzern_id = '.$id.' and tutorial_id = '.$current_tut['id']) == 1) {
		if($current_tut['task_type']=='config' || !empty($_GET) || !empty($_POST)) {
			select('UPDATE user_finished_tutorial SET confirmed = 1 WHERE konzern_id = '.$id.' and tutorial_id = '.$current_tut['id']);
			$current_tut = getCurrentTutorial($status['id']);
			$menu_box['show'] = true;
			$menu_box['item'] = $current_tut['menu_item'];
			$menu_box['text'] = $current_tut['link_description'];
		} else {
			select('DELETE FROM user_finished_tutorial WHERE konzern_id = '.$id.' and tutorial_id = '.$current_tut['id'].' LIMIT 1');
		}
	} else {
		$task = array('id' => $current_tut['id'], 'type' => $current_tut['task_type'], 'text' => preg_replace($replace_pattern, $replace_text, $current_tut['second_description']),
						'success_text' => preg_replace($replace_pattern, $replace_text, $current_tut['success_description']), 'item'=>$current_tut['second_item'],
						'failure_text' => preg_replace($replace_pattern, $replace_text, $current_tut['failure_description']), 'amount' => $current_tut['amount'],
						'allow_more' => $current_tut['allow_more'], 'heading' => $current_tut['second_heading']);
		if ($task['type']=='ressi_leiste') {
			$task['amount'] = $status['money'];
		}
		$tpl->assign('TASK', $task);
	}
}
$tpl->assign('WIDTH', $current_tut['width']);
$tpl->assign('MENU_BOX', $menu_box);
$tpl->display('tutorial.tpl');
?>