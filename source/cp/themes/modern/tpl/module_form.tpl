<style type="text/css">
	.check_support select{
		width: 100%;
	}
	.check_support input{
		width: 20px;	
	}
	.check_support table td{
		padding: 0 !important;
		margin: 0 !important;	
	}
</style>
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
    <table border="0" cellspacing="0" cellpadding="0" class="frmcnt">
		<tr>
		<td><label><span>${fields.name.perm}</span>${fields.name.label}</label></td>
 			<td>
				<div class="frmwrap">
                    ${fields.name.ctrl}
                    <!-- if(${fields.name.message}) -->
					<span class="tooltipicon" title="${fields.name.message}">&nbsp;</span>
					<!-- end -->
				</div>
			</td>
		</tr>
		<tr>
			<td><label><span>${fields.class.perm}</span>${fields.class.label}</label></td>
			<td>
				<div class="frmwrap">
					${fields.class.ctrl}
					<!-- if(${fields.class.message}) -->
					<span class="tooltipicon" title="${fields.class.message}">&nbsp;</span>
					<!-- end -->
				</div>
			</td>
		</tr>
		<tr>
			<td><label><span>${fields.public_name.perm}</span>${fields.public_name.label}</label></td>
			<td>
				<div class="frmwrap">
					${fields.public_name.ctrl}
					<!-- if(${fields.public_name.message}) -->
					<span class="tooltipicon" title="${fields.public_name.message}">&nbsp;</span>
					<!-- end -->
				</div>
			</td>
		</tr>
		<tr>
			<td><label><span>${fields.admin_group.perm}</span>${fields.admin_group.label}</label></td>
			<td class="check_support">
				<div class="frmwrap">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td>${fields.admin_support.ctrl}</td>
							<td width="100%">${fields.admin_group.ctrl}</td>
						</tr>
					</table>
					 
					<!-- if(${fields.admin_group.message}) -->
					<span class="tooltipicon" title="${fields.admin_group.message}">&nbsp;</span>
					<!-- end -->
				</div>
			</td>
		</tr>
		<tr>
			<td><label><span>${fields._parent_self.perm}</span>${fields._parent_self.label}</label></td>
			<td>
				<div class="frmwrap">
					${fields._parent_self.ctrl}
	 				<!-- if(${fields._parent_self.message}) -->
					<span class="tooltipicon" title="${fields._parent_self.message}">&nbsp;</span>
					<!-- end -->
				</div>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<!-- template(cmp-form-action-bar.tpl,${buttons_bar}) -->
			</td>
		</tr>
	</table>
</div>