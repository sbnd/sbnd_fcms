<div class="filtertab filteron"><span><!-- lingual(filter) --></span></div>
<div class="filter toggle_containerfilter">
	<input type="submit" value="<!-- lingual(filter_btn) -->" class="button filterbutton" />
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