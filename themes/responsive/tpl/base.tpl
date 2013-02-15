<!DOCTYPE html>
<html>
	<head>
	    ${META}
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
		<!-- # LOGIN HEADER BOX # -->
		${LOGIN_BOX}
		<!-- if(${is_logged}) -->
		<div class="container login">
			<div class="form-inline">
				<div class="boxbrdr">
					<div class="pull-right">
						<!-- if(${profile_page}) -->
						<a href="${profile_page}">${user_data.name}</a>
						<!-- else -->
						${user_data.name},
						<!-- end -->
						&nbsp;<a class="langLink" href="${logout_link}"><!-- lingual(logout) --></a>
					</div>
				</div>
			</div>
		</div>
		<!-- end -->
		
		<!-- # LOGO, LANG BOX AND SEARCH # -->
		<div class="container header">
			<a href="${VIRTUAL}" class="brand pull-left"><img src="${THEME}img/logo.png" alt=""/></a>
			<div class="pull-right"><!-- component(language-bar) --></div>
			<div class="pull-right"><!-- component(search-bar,cmd=) --></div>
		</div>
		
		<!-- # MAIN NAVIGATION # -->
		<div class="navbar navbar-inverse navbar-static-top topnav">
			<div class="navbar-inner">
				<div class="container">
					<a class="btn btn-navbar" data-target=".nav-collapse" data-toggle="collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<div class="nav-collapse">
						<!-- navigation(top,cms-menu-top.tpl) -->
					</div>
				</div>
			</div>
		</div>
		
		<!-- # MAIN CONTENT # -->
		<div class="container">
			<div class="boxbrdr">
				<!-- if(${BREADCRUMBS}) --><!-- template(cms-breadcrumbs.tpl) --><!-- end -->
				
				<h1>${PAGE_DATA.title}</h1>
				<div class="page_body">${PAGE_DATA.body}</div>
				
				${CONTENT}
			</div>
		</div>
		
		<!-- # FOOTER # -->
		<div class="container">
		    <div class="footer">
		    	<!-- navigation(bottom,cms-menu-bottom.tpl) -->
		    </div>
		</div>
		
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