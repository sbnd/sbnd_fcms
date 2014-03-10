<div class="filtertab filteron"><span><!-- lingual(filter) --></span></div>
<div class="filter toggle_containerfilter">
	<!-- foreach(${buttons_bar.actions},action) -->
		<button name="${buttons_bar.cmd}<!-- if(${buttons_bar.is_ie7}) -->${action.key}<!-- end -->" value="${action.key}" class="button filterbutton" type="submit"<!-- if(${action.disable}) --> disabled="disabled"<!-- end -->>${action.text}</button>
	<!-- end -->
	
	<div class="cols">
	<!-- foreach(${fields},field) -->
		<div class="col">
			<label>${field.label}</label>
				${field.ctrl}
		</div>
	<!-- end -->
		<div class="clr"></div>
	</div>
	<div class="clr"></div>
</div>