<!DOCTYPE html>
<html>
	<head>
		${META}
		<link href="${THEME}css/style.css" rel="stylesheet" type="text/css" />
		<!-- SCRIPTS -->
		${JS}
		<script type="text/javascript" src="${THEME}js/jquery.tooltip.pack.js"></script>
			<script type="text/javascript" src="${THEME}js/jquery.cookie.js"></script>
			
			<script type="text/javascript">$(document).ready(function(){
					$('.okmsg .closemsg, .infomsg .closemsg, .errormsg .closemsg').click(function(){
						$(this).parent().fadeOut('slow');
					});
			});
		</script>	
	</head>
	
	<body>
	<div class="top">&nbsp;</div>
	<div class="wrapper login">
		<div class="logincnt">
	        <div class="logo">
	            <img src="${THEME}img/logo.png" alt="" />
	        </div>
	        ${CONTENT}
	        <div class="clr"></div>
	    </div>
	    <div class="forget">
	    	<a href="${forgoten_pass_link}"><!-- if(${login_mode}) --><!-- lingual(forgot_pass_label) --><!-- else --><!-- lingual(back_to_login) --><!-- end --></a>
	    </div>
	    <div class="footer">&nbsp;</div>
	</div>
	<div class="popupmsg">
		<!-- if(${MESSAGES.message}) -->	
			<div class="okmsg">
				<!-- foreach(${MESSAGES.message},massage) --><p>${massage.message}</p><!-- end -->
				<span class="closemsg">&nbsp;</span>
			</div>
		<!-- end -->
		<!-- if(${MESSAGES.warning}) -->
			<div class="infomsg">
				<!-- foreach(${MESSAGES.warning},massage) --><p>${massage.message}</p><!-- end -->
				<span class="closemsg">&nbsp;</span>
			</div>
		<!-- end -->
		<!-- if(${MESSAGES.fatal}) -->
			<div class="errormsg">
				<!-- foreach(${MESSAGES.fatal},massage) --><p>${massage.message}</p><!-- end -->
				<span class="closemsg">&nbsp;</span>
			</div>
		<!-- end -->
	</div>
</body>
</html>