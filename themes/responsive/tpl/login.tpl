<div class="container login">
	<div class="form-inline">
		<div class="boxbrdr">
			${fields.email.ctrl}
			${fields.pass.ctrl}
			<label class="checkbox">
				${fields.remember.ctrl}${fields.remember.label}
			</label>
			
			<div class="pull-right">
			
				<!-- template(cmp-form-action-bar.tpl,${buttons_bar}) -->
			
				<!-- if(${forgotten_page}) -->
				<a href="${forgotten_page}"><!-- lingual(forgoten) --></a>
				<!-- end -->
				
				<!-- if(${forgotten_page} && ${register_page}) -->
				<span>&nbsp;|&nbsp;</span>
				<!-- end -->
				
				<!-- if(${register_page}) -->
				<a href="${register_page}"><!-- lingual(registration) --></a>
				<!-- end -->
				
			</div>
			
			<!-- # INFO, WARNING AND ERROR MESSAGES # -->
			
			<!-- Email error message -->
			<!-- if(${fields.email.message}) -->
				<div class="alert alert-block">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					${fields.email.message}
				</div>
			<!-- end -->
			
			<!-- Password error message -->
			<!-- if(${fields.pass.message}) -->
				<div class="alert alert-block">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					${fields.pass.message}
				</div>
			<!-- end -->
			
			<!-- Remember me checkbox message -->
			<!-- if(${fields.remember.message}) -->
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					${fields.remember.message}
				</div>
			<!-- end -->
		</div>
	</div>
</div>