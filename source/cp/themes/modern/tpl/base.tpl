<!DOCTYPE html>
<html>
<head>
	${META}
	<link href="${THEME}css/style.css" rel="stylesheet" type="text/css" />
	<link href="${THEME}css/jstree.css" rel="stylesheet" type="text/css" />
	<!-- SCRIPTS -->
	${JS}
	<script type="text/javascript" src="${THEME}js/expand.js"></script>
		<script type="text/javascript" src="${THEME}js/hoverIntent.js"></script>
		<script type="text/javascript" src="${THEME}js/superfish.js"></script>
		<script type="text/javascript" src="${THEME}js/jquery.cookie.js"></script>
		<script type="text/javascript" src="${THEME}js/jquery.hotkeys.js"></script>
		<script type="text/javascript" src="${THEME}js/jquery.jstree.js"></script>
		<script type="text/javascript" src="${THEME}js/jquery.tooltip.pack.js"></script>
		<script type="text/javascript" src="${THEME}js/treeChecker.js"></script>
		<script type="text/javascript" src="${THEME}js/form-action-manager.js"></script>
		
		<script type="text/javascript">$(document).ready(function(){
			$('.okmsg .closemsg, .infomsg .closemsg, .errormsg .closemsg').click(function(){
				$(this).parent().fadeOut('slow');
			});		
			/**
			 * @events 
			 *	tabopened
			 *	tabclosed
			 */
			$(".trigger").click(function(){
				var menu = $('.supportmenucnt'), 
					wripper = $('.wrapper'), 
					isClosed = !this.isClosed,
					self = $(this);
				
				$(this).animate({
					left: isClosed ? 1 : 160
				},{
					duration: 500,
					step: function (now){//debugger;
						menu.width(now);
						wripper.css('margin-left', now);
					},
					complete: function (){
						self.trigger(isClosed ? 'tabclosed' : 'tabopened');
					}
				});
				this.isClosed = isClosed;
				return false;
			}).bind('tabclosed', function (){
				$.cookie("hmenu", 1);
			}).bind('tabopened', function (){
				$.cookie("hmenu", null);
			});
			if($.cookie("hmenu")){
				$('.supportmenucnt').width(1);
				$('.wrapper').css('margin-left', '1px');
				$(".trigger").css('left', '1px').get(0).isClosed = true;
			}
			//dropdown menu trigger
			$('.topnav ul').superfish({
				autoArrows: false
			});
			//fix for lingual menu
			 if(window.location.href.indexOf("lingual") > -1) {
				 $('a[href$="index.php?cmp=languages"]').addClass('current-topmenu');
				 $('ul.sf-js-enabled li a').first().addClass('current-topmenu');
			    }
			// jstree menu 
			$(".supportmenu .tree").jstree({
				plugins: ["themes", "html_data", "ui", "crrm", "hotkeys", "cookies"]
			}).bind("reselect.jstree", function () { 
				$(this).bind("select_node.jstree", function (e, data) {
					document.location = data.rslt.obj.children("a").attr("href"); 
				}); 
			});
		});
	</script>
</head>
<body>
<div style="min-width: 1024px;">
	<div style="width: 1024px; height: 1px;"></div>
	<div class="top">&nbsp;</div>
	<div class="supportmenu">
	    <div class="trigger"><span><a href="#"><img src="${THEME}/img/viewicon.png" alt="" /></a></span></div>
	    <div class="supportmenucnt">
	    	<h2><span><!-- lingual(vertical_menu) --></span></h2>
	        <div class="tree">
	            <!-- menu(${menu},cmp-cp-vmenu.tpl) -->
	        </div>
	    </div>
	</div>
	<div class="wrapper">
		<div class="header">
			<div class="status_bar">
			    <!-- lingual(welcome) -->, 
			    	<a href="${user_data.profile_link}">${user_data.name}</a> | 
			    	<a href="${logout_link}" id="logout"><!-- lingual(logout) --></a>
			</div>
			<div class="logo">
				<a href="${VIRTUAL}cp/"><img src="${THEME}/img/logo.png" alt="" /></a>
			</div>
			<div class="topnav">
	            <!-- menu(${menu},cmp-cp-hmenu.tpl) -->
	            <div class="topnavcrnr topnavnw">&nbsp;</div>
	            <div class="topnavcrnr topnavne">&nbsp;</div>
	            <div class="topnavcrnr topnavse">&nbsp;</div>
	            <div class="topnavcrnr topnavsw">&nbsp;</div>
			</div>
			<div class="clr"></div>
			<div class="breadcrumbs"><!-- lingual(you_are_here) -->: ${BREADCRUMBS}</div>
		</div>
		<div class="main">
			${CONTENT}
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
</div>
</body>
</html>