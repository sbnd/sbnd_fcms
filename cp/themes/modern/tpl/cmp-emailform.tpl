<script type="text/javascript">
function ajaxRunner(caller){
	if (caller.value){
		Svincs.Ajax.Remote(caller, '${VIRTUAL}', {	
			id : caller.value
		}, function (data){ 
		   var js = JSON.parse(data);
			$('#message').get(0).htmlEditor.loadHTML(js.content);
			$('#subject').val(js.title);
		},{
			service: 'template_loader',
			loader: '#test'
		});

	}
}
</script>

<style>
.send_to_all_box label {
	text-align:left;
}

</style>
<h1>${PAGE_DATA.title}</h1>   <div id="test"></div>
<div class="page_body">${PAGE_DATA.body}</div>
<div class="frm">
<!-- template(cmp-form-language-bar.tpl) -->
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="frmcnt">
	<tr >
		<td>
		<label><span>${fields.send_to_all.perm}</span>${fields.send_to_all.label}</label>
		</td>
		<td>
			<div class="frmwrap">
	         ${fields.send_to_all.ctrl}
	        <!-- if(${fields.send_to_all.message}) -->
			<span class="tooltipicon" title="${fields.send_to_all.message}">&nbsp;</span>
			<!-- end -->
						<div class="send_to_all_box">	
				<label><span>${fields.user_groups.perm}</span>${fields.user_groups.label}</label>
						<div class="frmwrap">
		                    ${fields.user_groups.ctrl}
		                    <!-- if(${fields.user_groups.message}) -->
							<span class="tooltipicon" title="${fields.user_groups.message}">&nbsp;</span>
							<!-- end -->
						</div>
			
				<label><span>${fields.search_users.perm}</span>${fields.search_users.label}</label>
						<div class="frmwrap">
		                    ${fields.search_users.ctrl}
		                    <!-- if(${fields.search_users.message}) -->
							<span class="tooltipicon" title="${fields.search_users.message}">&nbsp;</span>
							<!-- end -->
						</div>
						
				<label><span>${fields.select_users.perm}</span>${fields.select_users.label}</label>
						<div class="frmwrap">
		                    ${fields.select_users.ctrl}
		                    <!-- if(${fields.select_users.message}) -->
							<span class="tooltipicon" title="${fields.select_users.message}">&nbsp;</span>
							<!-- end -->
						</div>
		</div>

			
			</div>
		</td>
	</tr>
	<tr>
		<td>		
		<label><span>${fields.email_template.perm}</span>${fields.email_template.label}</label>
		</td>
		<td>
			<div class="frmwrap">
             ${fields.email_template.ctrl}
              <!-- if(${fields.email_template.message}) -->
			<span class="tooltipicon" title="${fields.email_template.message}">&nbsp;</span>
			<!-- end -->
			</div>
		</td>
	</tr>
	<tr>
		<td>
		<label><span>${fields.subject.perm}</span>${fields.subject.label}</label>
		</td>
		<td>
			<div class="frmwrap">
              ${fields.subject.ctrl}
              <!-- if(${fields.subject.message}) -->
			<span class="tooltipicon" title="${fields.subject.message}">&nbsp;</span>
			<!-- end -->
			</div>
		</td>
	</tr>
	<tr>
		<td>
		<label><span>${fields.message.perm}</span>${fields.message.label}</label>
		</td>
		<td>
			<div class="frmwrap">
             ${fields.message.ctrl}
             <!-- if(${fields.message.message}) -->
			<span class="tooltipicon" title="${fields.message.message}">&nbsp;</span>
			<!-- end -->
			</div>
		</td>
	</tr>
	<tr>
		<td>
		<label><span>${fields.file.perm}</span>${fields.file.label}</label>
		</td>
		<td>
			<div class="frmwrap">
               ${fields.file.ctrl}
                <!-- if(${fields.file.message}) -->
				<span class="tooltipicon" title="${fields.file.message}">&nbsp;</span>
				<!-- end -->
			</div>
		</td>
	</tr>
	<tr>
		<td></td>
		<td><!-- template(cmp-form-action-bar.tpl,${buttons_bar}) --></td>
	</tr>
</table>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$('#message').get(0).htmlEditor.loadHTML('Choose E-mail Template or create one ');
	$('.send_to_all_box').hide();
		$('input[name=send_to_all]:radio').click(function() {

			if ($('input[name=send_to_all]:radio:checked').val() == 0) {
				$('.send_to_all_box').hide('slow');
				
			}else{
				$('.send_to_all_box').show('slow');
				}	
	});
	$('.tooltipicon').tooltip({
			fade: 250,
			delay: 0,
			left: 0
	});
	if ( $("input[name=send_to_all]:checked").val() == 1){
		$('.send_to_all_box').show();
	}	
		
});


</script>