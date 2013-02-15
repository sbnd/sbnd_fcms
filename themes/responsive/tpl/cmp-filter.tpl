<div class="filtertab"><span><!-- lingual(filter) --></span></div>
<div class="filter toggle_containerfilter">
	<div class="row">
	<!-- foreach(${fields},field) -->
		<div class="col span3">
			<label>${field.label}</label>
				${field.ctrl}
		</div>
	<!-- end -->
	</div>
	<input type="submit" value="<!-- lingual(filter_btn) -->" class="btn btn-primary" />
</div>