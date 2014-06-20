{include file='js/tutorial.js.tpl'}
<div style="position: fixed;padding: 0px;line-height:130%; display: none; z-index:40; border:2px solid; top:0px;left:0px;" id="box_tut">
	<div class="tableHead" style="width:100%;padding:3px;">
		<span id="box_tut_heading">Tutorial</span>
		<span style="float:right;margin-right:3px;">
			<a href="javascript:hideTutorial();">x</a>
		</span>
	</div>
	<div class="tableInner1" id="box_tut_text" style="width:100%; height:100%;padding:3px;">
	</div>
</div>
<div class="tableInner1" style="position:fixed; top:0;left:0; width:0;height:0;z-index:20; opacity: 0.4;" id="back_blur">
</div>  
<div id="quest_box" style="position: absolute; padding: 0px; display: none; z-index:35; border:1px solid;border-color:black;top:0;left:0;">
	<div class="tableHead" style="width:100%;padding:3px;">
		<span id="quest_box_heading">Tutorial</span>
		<span style="float:right;margin-right:3px;">
			<a href="javascript:$('#quest_box').hide('fast');">x</a>
		</span>
	</div>
	<div class="tableInner1" id="quest_box_text" style="width:100%; height:100%;padding:3px;"></div>
</div>
