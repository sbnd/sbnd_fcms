<script type="text/javascript">
$(document).ready(function(){
	//form tooltips
	$('.tooltipicon').tooltip({
		fade: 250,
		delay: 0,
		left: 0
	});	
});
</script>
<label>${fields.email.label}</label>
	<div class="frmwrap">
		${fields.email.ctrl}
		<!-- if(${fields.email.message}) -->
		<span class="tooltipicon" title="${fields.email.message}">&nbsp;</span>
		<!-- end -->
	</div>
<!-- if(${fields.code}) -->
<label>${fields.code.label}</label>
	<div class="frmwrap">
		${fields.code.ctrl}
		<!-- if(${fields.code.message}) -->
		<span class="tooltipicon" title="${fields.code.message}">&nbsp;</span>
		<!-- end -->
	</div>
<!-- end -->	
<!-- if(${fields.pass}) -->
<label>${fields.pass.label}</label>
	<div class="frmwrap">
		${fields.pass.ctrl}
		<!-- if(${fields.pass.message}) -->
		<span class="logintooltip tooltipicon" title="${fields.pass.message}">&nbsp;</span>
		<!-- end -->
	</div>
<!-- end -->
<!-- if(${fields.confirm_pass}) -->
<label>${fields.confirm_pass.label}</label>
	<div class="frmwrap">
		${fields.confirm_pass.ctrl}
		<!-- if(${fields.confirm_pass.message}) -->
		<span class="tooltipicon" title="${fields.confirm_pass.message}">&nbsp;</span>
		<!-- end -->
	</div>
<!-- end -->

<div class="clr"></div>
<!-- if(${fields.rememberme}) -->
<div class="check">
	${fields.rememberme.ctrl}
	<span>${fields.rememberme.label}</span>
</div>
<!-- end -->

<!-- template(cmp-form-action-bar.tpl,${buttons_bar}) -->