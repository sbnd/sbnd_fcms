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
<div class="frm">
	<!-- template(cmp-form-language-bar.tpl) -->
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="frmcnt">
		<!-- foreach(${fields},field) -->
		<tr>
			<td><label><!-- if(${field.perm}) --><span>${field.perm}</span><!-- end -->${field.label}</label></td>
			<td width="100%">
				<div class="frmwrap">
					${field.ctrl}
					<!-- if(${field.message}) -->
					<span class="tooltipicon" title="${field.message}">&nbsp;</span>
					<!-- end -->
				</div>
			</td>
		</tr>
		<!-- end -->
		<tr>
			<td>&nbsp;</td>
			<td>
				<!-- template(cmp-form-action-bar.tpl,${buttons_bar}) -->
			</td>
		</tr>		
	</table>
	<script type="text/javascript" mypar="1">
		// this is match test
	</script>
</div>