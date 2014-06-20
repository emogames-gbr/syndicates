<?php


require_once("../includes.php");


connectdb();
if (!$argv[1]) exit("\n\nKeine Datenbank übergeben! Abbruch\n\n");
$db = $argv[1];
mysql_select_db($db);
$time = time();


	$feature_data = assocs("select feature_id, emogames_user_id, time_bis from users_features");
	$feature_koins_data = assocs("select feature_id, user_id, time_bis from users_koins_features where time_bis > $time");
	
	if ($feature_data or $feature_koins_data) {
		if ($feature_data) {
			foreach ($feature_data as $vl) {
				if (!$there[$vl[emogames_user_id]]) $emogames_ids[] = $vl[emogames_user_id];
				$features_by_emogames_id[$vl[emogames_user_id]][$vl[feature_id]] = 1;
			}
		}
		if ($feature_koins_data) {
			$user_ids = array();
			foreach ($feature_koins_data as $vl) {
				if (!in_array($vl['user_id'], $user_ids)) $user_ids[] = $vl['user_id'];
			}
			$emogames_ids_by_user_id = assocs("select emogames_user_id, id from users where id in (".join(",", $user_ids).")", "id");
			foreach ($feature_koins_data as $vl) {
				if ($emogames_ids_by_user_id[$vl['user_id']]['emogames_user_id']) {
					$features_by_emogames_id[$emogames_ids_by_user_id[$vl['user_id']]['emogames_user_id']][$vl['feature_id']] = 1;
					if (!in_array($emogames_ids_by_user_id[$vl['user_id']]['emogames_user_id'], $emogames_ids))
						$emogames_ids[] = $emogames_ids_by_user_id[$vl['user_id']]['emogames_user_id'];
						echo $vl['user_id'].": ".$features_by_emogames_id[$emogames_ids_by_user_id[$vl['user_id']]['emogames_user_id']][$vl['feature_id']]."\n";
				}
			}
			
		}
	
	
		$konzernid_by_emogames_id = assocs("select konzernid, emogames_user_id from users where emogames_user_id in (".join(",", $emogames_ids).")", "emogames_user_id");
	
		foreach ($konzernid_by_emogames_id as $vl) {
			$emogames_id_by_konzernid[$vl[konzernid]] = $vl[emogames_user_id];
		}
	
	
		$features_feature_table = assocs("select konzernid, feature_id from features");
	
		if ($features_feature_table) {
			foreach ($features_feature_table as $vl) {
	
				//echo "konzid: $vl[konzernid]; emo_id: ".$emogames_id_by_konzernid[$vl[konzernid]]."; feature_id: $vl[feature_id]; da: ".$features_by_emogames_id[$emogames_id_by_konzernid[$vl[konzernid]]][$vl[feature_id]]."\n";
	
				if (!$features_by_emogames_id[$emogames_id_by_konzernid[$vl[konzernid]]][$vl[feature_id]]) {
					$delete_features_by_feature_id[$vl[feature_id]][] = $vl[konzernid];
				}
				$features_by_konzernid[$vl[konzernid]][$vl[feature_id]] = 1;
			}
		} else { $features_feature_table = array(); }
	
	
		foreach ($feature_data as $vl) {
			if ($konzernid_by_emogames_id[$vl[emogames_user_id]][konzernid] && !$features_by_konzernid[$konzernid_by_emogames_id[$vl[emogames_user_id]][konzernid]][$vl[feature_id]]) { 
				$insert_features_by_feature_id[$vl[feature_id]][] = "(".$vl[feature_id].", ".$konzernid_by_emogames_id[$vl[emogames_user_id]][konzernid].", ".$vl['time_bis'].")";
			}
		}
		foreach ($feature_koins_data as $vl) {
			echo "da\n";
			if ($emogames_ids_by_user_id[$vl['user_id']]['emogames_user_id']) {
				if ($konzernid_by_emogames_id[$emogames_ids_by_user_id[$vl['user_id']]['emogames_user_id']][konzernid] && !$features_by_konzernid[$konzernid_by_emogames_id[$emogames_ids_by_user_id[$vl['user_id']]['emogames_user_id']][konzernid]][$vl[feature_id]]) {
					$insert_features_by_feature_id[$vl[feature_id]][] = "(".$vl[feature_id].", ".$konzernid_by_emogames_id[$emogames_ids_by_user_id[$vl['user_id']]['emogames_user_id']][konzernid].", ".$vl['time_bis'].")";
				}
			}
		}
	
		if ($delete_features_by_feature_id) {
			foreach ($delete_features_by_feature_id as $ky => $vl) {
				select("delete from features where feature_id = $ky and konzernid in (".join(",", $vl).")");
			}
		}
		if ($insert_features_by_feature_id) {
			foreach ($insert_features_by_feature_id as $ky => $vl) {
				select("insert into features (feature_id, konzernid, time_bis) VALUES ".join(",", $vl));
			}
		}
	
	}
	else { select("delete from features"); }



?>
