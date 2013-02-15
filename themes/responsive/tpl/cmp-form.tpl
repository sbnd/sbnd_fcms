<!-- template(cmp-form-language-bar.tpl) -->
<form class="form-horizontal">
	<!-- foreach(${fields},field) -->
	<div class="control-group">
		<label class="control-label" for="inputEmail"><!-- if(${field.perm}) --><span>${field.perm}</span><!-- end -->${field.label}</label>
		<div class="controls">
			${field.ctrl}
			<!-- if(${field.label} == 'Password') --><span class="help-block"><!-- lingual(password_rules) --></span><!-- end -->
			<!-- if(${field.message}) -->
				<div class="alert alert-block">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					${field.message}
				</div>
			<!-- end -->
		</div>
	</div>
	<!-- end -->
	<div class="control-group">
		<div class="controls">
			<!-- template(cmp-form-action-bar.tpl,${buttons_bar}) -->
		</div>
	</div>
</form>