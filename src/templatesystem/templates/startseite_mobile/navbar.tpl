<ul>
	<li><a href="?action=home" data-icon="home" {if !preg_match('/news/', $ACTION) && $ACTION != 'sonstiges' && $ACTION != 'stats' && $ACTION != 'impressum'}class="ui-btn-active"{/if}>Home</a></li>
	<li><a href="?action=news" data-icon="info" {if preg_match('/news/', $ACTION)}class="ui-btn-active"{/if}>News</a></li>
	<li><a href="?action=stats" data-icon="star" {if $ACTION == 'stats'}class="ui-btn-active"{/if}>Aktuelles</a></li>
	<li><a href="?action=sonstiges" data-icon="grid" {if $ACTION == 'sonstiges' || $ACTION == 'impressum'}class="ui-btn-active"{/if}>Sonstiges</a></li>
</ul>