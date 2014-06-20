{strip}
	</div>
	<footer data-role="footer">
		<p>&copy; 2011 - Emogames Productions</p>
	</footer>
</section>

{literal}
<script type="text/javascript">
	$('[data-role=page]').live('pageshow', function (event, ui) {
		try {
			var pageTracker = _gat._getTracker("UA-745697-5");
			pageTracker._setCustomVar(1, "Mobile Version", "Ingame", 1);
			pageTracker._trackPageview();
		} catch(err) { }
	});
</script>
{/literal}
</body>
</html>{/strip}