	<h4>Zum Spiel</h4>
	<form action="php/login.php" data-ajax="false" method="post" name="zum_game">
		<input type="hidden" name="action" value="login" />
			<div class="text_captcha">
				<!--<img src="images/startseite/capag2.gif" />-->
			</div><br />
		<label for="input_codeinput" class="ui-hidden-accessible">Logincode:</label>
		<input class="absolute" type="text" name="codeinput" id="input_codeinput" placeholder="Logincode" />
		<button type="submit" data-inline="true">Zum Spiel</button>
		<a href="?action=logout" data-role="button" data-theme="b" data-inline="true">Logout</a>
	</form>
