	        <tr>
	        </tr>
	        	<td class="ver12w" width="730" colspan=3 valign="top">
	        		<br><br><br>
	        		<center>
	        			<b>
	        				{if $case1 || $case3}
	        					Ihr Konzern befindet sich momentan im Urlaubsmodus und kann erst in
	        					<font class=highlightAufSiteBg>
	        						{if $days > 0}{$days} Tagen, {/if}
	        						{$hours} Stunden, 
	        						{$minutes} Minuten,
	        					</font><br>
	        					bzw. am <font class=highlightAufSiteBg>{$activationtime} Uhr</font> wieder aktiviert werden!
	        					{if $case3}
	        						<br>
	        						<br>Nicht schummeln ;)!
	        					{/if}
					    	{/if}
	    					{if $case2}
	        					Ihr Konzern befindet sich momentan im Urlaubsmodus!<br>
	        					<br>
	        					Bitte <a href=vacation.php?inneraction=1 class=highlightAufSiteBg>hier klicken</a>
	        					um den Urlaubsmodus zu beenden
	        				{/if}			
	        			</b>
	        		</center>
	        		<br>
	        		<br>
	        		<center>
	        			<a href=logout.php class=linkAufSitebg>{$yellowdot} Logout</a>
	        		</center>
	        	</td>
	        </tr>