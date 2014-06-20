<?
##########################################################
#
#			 Mod Quiz - By Nicolas Breitwieser
#			Started: Friday, 04. February 2005
#			requires xtendsql + db conection
##########################################################

##########################################################
#
# class quiz
#
# Methods:
#

class quiz {

	##########################################################
	#			Constructor
	##########################################################

	function quiz($emogames_user_id, $username) {
		global $SCRIPT_NAME;
		$this->time = time();
		$this->user = assoc("select * from quiz_users where emogames_user_id = '$emogames_user_id'");
		if (!$this->user) {
			$this->user = $this->create_user($emogames_user_id, $username);
		}

		$this->self = array_pop(explode("/",$SCRIPT_NAME));
		$this->defined_quiz_action = array(
			'new_quiz' => 'neues_quizz',
			'edit_quiz' => 'quiz_bearbeiten'
		);
		echo "Ütest: ü<br>";
		$this->kategorien = assocs("select * from quiz_kategorien order by name asc", "id");

	}

	##########################################################
	##########################################################
	#			PUBLIC FUNCTIONS
	##########################################################
	##########################################################

	function init($tableoutline = 'tableOutline', $tablehead = 'tableHead', $tablehead2 = 'tableHead2', $tableinner = 'tableInner1', $tableinner2 = 'tableInner2', $siteground = 'siteGround', $linkauftableinner = 'linkAuftableInner', $linkaufsitebg = 'linkAufsiteBg', $highlightauftableinner = 'highlightAuftableInner', $highlightaufsitebg = 'highlightAufSiteBg') {
		$this->tableoutline = $tableoutline;
		$this->tablehead = $tablehead;
		$this->tablehead2 = $tablehead2;
		$this->tableinner = $tableinner;
		$this->tableinner2 = $tableinner2;
		$this->siteground = $siteground;
		$this->linkauftableinner = $linkauftableinner;
		$this->linkaufsitebg = $linkaufsitebg;
		$this->highlightauftableinner = $highlightauftableinner;
		$this->highlightaufsitebg = $highlightaufsitebg;

		$this->ausgabe_start = "<table width=95% class=".$this->siteground." cellpadding=0 cellspacing=0><tr><td>";
		$this->ausgabe_end = "</td></tr></table>";

		$this->quiz_action = $_POST['quiz_action'];
		if (!$this->quiz_action) $this->quiz_action = $_GET['quiz_action'];
		$this->quiz_sub_action = $_POST['quiz_sub_action'];
		if (!$this->quiz_sub_action) $this->quiz_sub_action = $_GET['quiz_sub_action'];

		if (!$this->quiz_action) return $this->ausgabe_start.$this->AUSGABE_initial().$this->ausgabe_end;
		elseif ($this->quiz_action) return $this->ausgabe_start.$this->{$this->defined_quiz_action[$this->quiz_action]}().$this->ausgabe_end;
	}

	function AUSGABE_initial() {
		return "<a href=".$this->self."?quiz_action=new_quiz class=".$this->linkaufsitebg.">Neues Quiz erstellen</a>
					<br>
				<a href=".$this->self."?quiz_action=edit_quiz class=".$this->linkaufsitebg.">Quiz bearbeiten</a>
				";

	}

	function neues_quizz() {
		// Checken ob gültige Kategorie ausgewählt ist
		$kategorie = $_POST['kategorie'];
		if (!$kategorie) $kategorie = $_GET['kategorie'];
		if (!$this->kategorien[$kategorie]) { // Kategorie ungültig
			foreach ($this->kategorien as $vl) {
				$kategorien_output[] = "<a href=".$this->self."?quiz_action=new_quiz&kategorie=$vl[id] class=".$this->linkauftableinner.">$vl[name] ($vl[quiz_count])</a>";
			}
			return "Hallo ".$this->user[username].",<br><br>Bitte wähle zunächst eine Kategorie, die zu Deinem neuen Quiz am besten passt!<br>Die Zahl in Klammern gibt an, wieviele Quiz in dieser Kategorie bereits vorhanden sind.<br><br>".join(", ", $kategorien_output);
		}
		// Kategorie gültig, weiter gehts
		else {
			$quiz_name = $this->slashing($_POST['quiz_name']);
			$quiz_beschreibung = $this->slashing($_POST['quiz_beschreibung']);
			$quiz_fragen_count = floor($_POST['quiz_fragen_count']);
			$quiz_antworten_count = floor($_POST['quiz_antworten_count']);
			$quiz_timelimit = floor($_POST['quiz_timelimit']);
			$quiz_show_fragen_per_page_count = floor($_POST['quiz_show_fragen_per_page_count']);
			if ($this->quiz_sub_action == "step2") { // Grundangaben wurden gemacht, jetzt auf Richtigkeit prüfen
				if (strlen($quiz_name) < 5 or strlen($quiz_name) >= 30) { $barrier = 1; f("Der Quiz-Name muss zwischen 5 und 30 Zeichen lang sein."); }
				if (strlen($quiz_beschreibung) < 10 or strlen($quiz_beschreibung) >= 15000) { $barrier = 1; f("Die Quiz-Beschreibung muss zwischen 10 und 15.000 Zeichen lang sein."); }
				if ($quiz_fragen_count < 1 or $quiz_fragen_count > 50) { $barrier = 1; f("Das Quiz muss mindestens eine Frage und darf höchstens 50 Fragen haben."); }
				if ($quiz_antworten_count < 2 or $quiz_antworten_count > 20) { $barrier = 1; f("Jede Frage muss mindestens zwei und darf höchstens 20 verschiedene Antwortmöglichkeiten haben."); }
				if ($quiz_timelimit && $quiz_timelimit < $quiz_fragen_count * 20) { $barrier = 1; f("Das Timelimit muss mind. 20 Sekunden pro Frage lang sein!"); }
				if ($quiz_show_fragen_per_page_count > $quiz_fragen_count or !$quiz_show_fragen_per_page_count) { $quiz_show_fragen_per_page_count = $quiz_fragen_count; }
				if (!$barrier) { // Und weiter im Text, alles klar soweit
					// CHECKEN, ob nicht vor kurzem schon ein Quiz angelegt wurde und einfach nur reloaded wurde;
					$last_quiz_createtime = single("select time_created from quiz_quizzs where author_user_id = '".$this->user[id]."' and kategorie_id=$kategorie order by time_created desc limit 1");
					if ($last_quiz_createtime && $this->time - $last_quiz_createtime <= 60) { $barrier = 1; f("Du hast vor weniger als 60 Sekunden bereits ein Quiz angelegt. Abbruch!"); }
					if (!$barrier) {
						// QUIZ ANLEGNE
						select("insert into quiz_quizzs (kategorie_id, name, description, author_user_id, author_username, fragen_count, show_fragen_per_page_count, antworten_count, timelimit, time_created)
								VALUES
								($kategorie, '$quiz_name', '$quiz_beschreibung', '".$this->user[id]."', '".$this->user[username]."', $quiz_fragen_count, $quiz_show_fragen_per_page_count, ".($quiz_fragen_count*$quiz_antworten_count).", $quiz_timelimit, ".$this->time.")");
						$quiz_id = single("select id from quiz_quizzs where author_user_id = '".$this->user[id]."' and kategorie_id=$kategorie order by time_created desc limit 1");
						// FRAGEN ANLEGEN
						for ($i = 1; $i <= $quiz_fragen_count; $i++) {
							select("insert into quiz_quizzs_fragen (kategorie_id, quiz_id, position, antworten_count, type) VALUES ($kategorie, $quiz_id, $i, $quiz_antworten_count, 'mc1')");
						}
						// ANTWORTEN ANLEGEN
						$fragen_ids = singles("select id from quiz_quizzs_fragen where quiz_id = $quiz_id order by position asc");
						foreach ($fragen_ids as $vl) {
							for ($i = 1; $i <= $quiz_antworten_count; $i++) {
								select("insert into quiz_quizzs_antworten (kategorie_id, quiz_id, frage_id, position) VALUES ($kategorie, $quiz_id, $vl, $i)");
							}
						}
						s("Dein Quiz wurde erfolgreich angelegt.<br>Du kannst jetzt damit beginnen es zu bearbeiten und insbesondere die Fragen und deren Antworten festzulegen.");
						return "Um dein Quiz zu bearbeiten, klicke bitte hier.";
					}
				} else $this->quiz_sub_action = '';
			}
			if (!$this->quiz_sub_action) { // NOCH NICHTS AUSGEWÄHLT: Grundlegende Sachen wie Name, Beschreibung, Anzahl Fragen festlegen
				return "
					Folgende Angaben sind unbedingt notwendig, um ein neues Quiz erstellen zu können, bitte fülle sie gewissenhaft aus.<br>Je gründlicher und sorgfältiger du bist, desto erfolgreicher wird dein Quiz sein und eine desto bessere Bewertung wird es bekommen und damit auch öfter besucht werden.<br><br><br>
					".$this->form_start("quiz_action=".$this->quiz_action, "quiz_sub_action=step2", "kategorie=$kategorie")."
					".$this->start_table($this->tableoutline, '100%', 'center', 0, 0, 0)."<tr><td>
					".$this->start_table("-",'100%', 'center', 8, 1, 0)."
						<tr class=".$this->tablehead."><td width=30%>Gewählte Kategorie:</td><td colspan=2><b>".$this->kategorien[$kategorie][name]."</b></td></tr>
						<tr class=".$this->tablehead2."><td>Quiz-Name:</td><td colspan=2><input type=text name=quiz_name value=\"".$quiz_name."\" size=30 maxlength=30></td></tr>
						<tr class=".$this->tableinner."><td valign=top>Quiz-Beschreibung:<br><br>BBCode aktiviert <a href=\"javascript:info('hilfe','bbcode')\" class=linkAuftableInner><img src=\"images/_help.gif\" border=0 valign=\"absmiddle\"></a></td><td colspan=2 valign=top><textarea name=quiz_beschreibung cols=40 rows=10>".$quiz_beschreibung."</textarea></td></tr>
						<tr class=".$this->tableinner2."><td>Wieviele Fragen hat dein Quiz?</td><td valign=top><input type=text value=\"".($quiz_fragen_count ? $quiz_fragen_count : "")."\" name=quiz_fragen_count></td><td>(Mindestens eine, höchstens 50 Fragen)</td></tr>
						<tr class=".$this->tableinner."><td valign=top>Wieviele Antwortmöglichkeiten soll es pro Frage geben?<br>(Lässt sich später noch ändern)</td><td valign=top><input type=text value=\"".($quiz_antworten_count ? $quiz_antworten_count : "")."\" name=quiz_antworten_count></td><td>(Je genauer du hier den Schnitt angibst, desto weniger musst du später Antwortmöglichkeiten einzeln dazu-, bzw. wegklicken.<br>Mindestens zwei, höchstens 20)</td></tr>
						<tr class=".$this->tableinner2."><td valign=top>Möchtest du ein Zeitlimit setzen (<b>optional</b>)?<br>(Angabe in <b>Sekunden</b>)</td><td valign=top><input type=text name=quiz_timelimit value=\"".($quiz_timelimit ? $quiz_timelimit : "")."\"></td><td>(Das Zeitlimit ist optional. Es erhöht den Schwierigkeitsgrad, da z.B. das Googlen nach Antworten nicht mehr so einfach möglich ist.<br>Pro Frage mind. 20 Sekunden.)</td></tr>
						<tr class=".$this->tableinner."><td valign=top>Wieviele Fragen sollen jeweils gleichzeitig auf einer Seite angezeigt werden?</td><td valign=top><input type=text name=quiz_show_fragen_per_page_count value=\"".($quiz_show_fragen_per_page_count ? $quiz_show_fragen_per_page_count : "")."\"></td><td>(Diese Option gibt dir die Möglichkeit zu bestimmen, ob Frage nach Frage beantwortet werden soll (Wert gleich 1) oder ob z.B. bei 16 Fragen jeweils 25% aller Fragen pro Seite angezeigt werden sollen (Wert dann gleich 4).<br>Sollen alle Fragen auf einer Seite angezeigt werden, lass dieses Feld frei, trage eine 0 oder die gleiche Anzahl Fragen ein, die du stellen möchtest.)</td></tr>
					".$this->end_table()."</td></tr>".$this->end_table()."
					".$this->form_end("weiter");

			}
		}
		return "";
	}

	function quiz_bearbeiten() {
			if (!$this->quiz_sub_action) {
				$quizzs_own = assocs("select * from quiz_quizzs where author_user_id='".$this->user[id]."' order by time_created desc");
				$anz = count($quizzs_own);
				for ($i = 0; $i < $anz; $i++) {
					$temp_lines[] = "<tr class=".$this->tableinner."><td>".$quizzs_own[$i][name]."</td><td>".$this->kategorien[$quizzs_own[$i][kategorie_id]][name]."</td><td>".$quizzs_own[$i][fragen_count]."</td><td>".$quizzs_own[$i][antworten_count]."</td><td>".date("H:i:s, d.M", $quizzs_own[$i][time_created])."</td>
					<td bgcolor=".($quizzs_own[$i][visible] ? "green":"red")."><a href=".$this->self."?quiz_action=edit_quiz&quiz_sub_action=edit&quiz_id=".$quizzs_own[$i][id]." class=".$this->linkauftableinner.">Bearbeiten</a>,<br>
					<a href=".$this->self."?quiz_action=edit_quiz&quiz_sub_action=delete&quiz_id=".$quizzs_own[$i][id]." class=".$this->linkauftableinner.">Löschen</a>,<br>
					<a href=".$this->self."?quiz_action=edit_quiz&quiz_sub_action=openclose&quiz_id=".$quizzs_own[$i][id]." class=".$this->linkauftableinner.">".($quizzs_own[$i][visible] ? "deaktivieren":"freischalten")."</a></td></tr>";
				}
				return $this->start_table($this->tableoutline, '100%', 'center', 0, 0, 0)."<tr><td>
					".$this->start_table("-",'100%', 'center', 2, 1, 0)."
					<tr class=".$this->tablehead."><td>Name</td><td>Kategorie</td><td>#Fragen</td><td>#Antworten</td><td>erstellt am</td><td>OPTIONEN</td></tr>
					".join("", $temp_lines)."
					".$this->end_table().$this->end_table();

			}
	}



	##########################################################
	#			PRIVATE FUNCTIONS
	##########################################################
	function create_user($emogames_user_id, $username) {
		select("insert into quiz_users (emogames_user_id, username) values ('$emogames_user_id', '$username')");
		return assoc("select * from quiz_users where emogames_user_id = '$emogames_user_id'");
	}

	function form_start() {
		for ($i = 0; $i < func_num_args(); $i++) {
			$temp = func_get_arg($i);
			$things[] = explode("=", $temp);
		}
		foreach ($things as $vl) {
			$hidden[] = "<input type=hidden name=\"$vl[0]\" value=\"$vl[1]\">";
		}
		return "<form action=".$this->self." method=post>".join("", $hidden);
	}

	function form_end($submit = '') {
		return ($submit ? "<br><center><input type=submit value=\"".$submit."\"></center>":"")."</form>";
	}

	function start_table($class = '', $width = '100%', $align='center', $cellpadding = 4, $cellspacing = 0, $border = 0) {
		if (!$class && $class != "-") $class = $this->siteground;
		return "<table align=$align".($class != "-" ? " class=$class":"")." border=$border width=$width cellpadding=$cellpadding cellspacing=$cellspacing>";
	}

	function end_table() {
		return "</table>";
	}

	function slashing($string) {
		if (get_magic_quotes_gpc()) {
			return $string;
		} else return addslashes($string);
	}
}

?>
