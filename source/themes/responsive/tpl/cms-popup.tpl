<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	    ${META} 
	    
	    <link href="${THEME}css/styles.css" rel="stylesheet" type="text/css" />
		<link href="${THEME}css/bootstrap.css" rel="stylesheet" type="text/css">
		<link href="${THEME}css/bootstrap-responsive.css" rel="stylesheet" type="text/css">
		
		<!-- IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		
		<!-- Fav and touch icons -->
		<link rel="shortcut icon" href="${THEME}ico/favicon.ico">
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="${THEME}ico/apple-144.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="${THEME}ico/apple-114.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="${THEME}ico/apple-72.png">
		<link rel="apple-touch-icon-precomposed" href="${THEME}ico/apple-57.png">
		
		<!-- Javascripts -->
		<script src="${THEME}js/bootstrap.js"></script>
		
		<!--[if lte IE 6]><script type="text/javascript" src="${VIRTUAL}js/supersleight.plugin.js"></script><![endif]-->
		
		<link href="${THEME}css/jquery.lightbox-0.5.css" rel="stylesheet" type="text/css"/>
		<script src="${THEME}js/jquery.lightbox-0.5.pack.js" type="text/javascript"></script>
	
	</head>
	<body>
		<!-- if(${MESSAGES}) -->
			<ul>
				<!-- foreach(${MESSAGES},message) -->
					<li class="well">${message.message}</li>
				<!-- end -->
			</ul>
		<!-- end -->
		<div class="page_body">${PAGE_DATA.body}</div>
		${CONTENT}
	</body>
</html>