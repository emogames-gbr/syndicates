	<h3>Login</h3>
	<form action="?{$HTTP_QUERY}" data-ajax="false" method="login">
		<input type="hidden" name="action" value="login" />
		
		<label for="input_name" class="ui-hidden-accessible">Name:</label>
		<input class="absolute" type="text" name="user" id="input_name" placeholder="Name" />
		
		<label for="input_password" class="ui-hidden-accessible">Passwort:</label>
		<input class="absolute" type="password" name="password" id="input_password" placeholder="Passwort" />
		
		<label for="input_savelogin">Login speichern</label>
		<input type="checkbox" name="savelogin" id="input_savelogin" class="custom" value="on" />
		<button type="submit" data-inline="true">Login</button>
	</form>