<div class="form-search">
	<div class="input-append">
		${fields.text.ctrl}
		<!-- foreach(${buttons_bar.actions},action) -->
			<button type="submit" class="cmd_btn btn ${action.key}<!-- if(${action.key} == 'save') --> btn-primary<!-- end -->" onclick="this.form.submit(); return false;">${action.text}</button>
		<!-- end -->
	</div>
</div>