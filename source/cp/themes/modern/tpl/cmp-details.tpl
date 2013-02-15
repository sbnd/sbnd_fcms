<div class="frm">
	<!-- template(cmp-form-language-bar.tpl) -->
	<table width="100%" border="1" cellspacing="0" cellpadding="0" class="frmcnt">
		<!-- foreach(${fields},k,field) -->
		<tr>
			<td width="100%">
				<div class="frmwrap">
					${k}: ${field}
				</div>
			</td>
		</tr>
		<!-- end -->
		<tr>
			<td>
				<!-- template(cmp-form-action-bar.tpl,${buttons_bar}) -->
			</td>
		</tr>		
	</table>
</div>