<!DOCTYPE html>
<html>
	<head>
	    ${META}
	    
	    <link href="${THEME}css/styles.css" rel="stylesheet" type="text/css" />
		<link href="${THEME}css/bootstrap.css" rel="stylesheet" type="text/css">
		<link href="${THEME}css/bootstrap-responsive.css" rel="stylesheet" type="text/css">
		
		<!-- Fav and touch icons -->
		<link rel="shortcut icon" href="${THEME}ico/favicon.ico">
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="${THEME}ico/apple-144.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="${THEME}ico/apple-114.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="${THEME}ico/apple-72.png">
		<link rel="apple-touch-icon-precomposed" href="${THEME}ico/apple-57.png">
		
		<!-- Javascripts -->
		${JS}
		<!-- IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->		
		<script src="${THEME}js/bootstrap.js"></script>
		<script type="text/javascript" src="${THEME}js/form-action-manager.js"></script>
		
		<!--[if lte IE 6]><script type="text/javascript" src="${THEME}js/supersleight.plugin.js"></script><![endif]-->
	</head>
	
	<body>
		<!-- # LOGO AND LANG BOX # -->
		<div class="container header">
			<a href="${VIRTUAL}" class="pull-left"><img src="${THEME}img/logo.png" alt=""/></a>
			<a href="#" class="lang pull-right"><!-- component(language-bar) --></a>
		</div>
		
		<!-- # LOGIN FORM # -->
		${CONTENT}
		
		<!-- # WARNING MESSAGES # -->
		<!-- if(${MESSAGES.message} || ${MESSAGES.warning} || ${MESSAGES.fatal}) -->
			<div class="popupmsg">
				<!-- if(${MESSAGES.message}) -->	
					<div class="alert alert-success">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<!-- foreach(${MESSAGES.message},massage) --><p>${massage.message}</p><!-- end -->
					</div>
				<!-- end -->
				<!-- if(${MESSAGES.warning}) -->
					<div class="alert alert-info">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<!-- foreach(${MESSAGES.warning},massage) --><p>${massage.message}</p><!-- end -->
					</div>
				<!-- end -->
				<!-- if(${MESSAGES.fatal}) -->
					<div class="alert alert-error">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<!-- foreach(${MESSAGES.fatal},massage) --><p>${massage.message}</p><!-- end -->
					</div>
				<!-- end -->
			</div>
		<!-- end -->
	</body>
</html>