<!-- 
	WHEN IS USED SAME ELEMENT (CATEGORY, ITEM, ARTICLE, ... )
-->
<!-- if(${data._current_}) -->
	<div class="cat_item">
		<h2>${data.title}</h2>
		<div class="cat_descr">
			<!--
				<!-- image(${data.image},width=80|height=80|alt=${data.title}|name=${data.title}) -->
			-->
			${data.short_desc}
		</div>
		<br/><br/>
		<!-- foreach(${data._subs_},k,sub,c) -->
		<!-- if(${c}) -->&nbsp;|&nbsp;<!-- end -->
		<a href="${sub.href}">
			<!--
				<!-- image(${sub.image},width=30|height=30|alt=${data.title}|name=${data.title}) -->
			-->
			${sub.title}
		</a>
		<!-- end -->
	</div>
		
	<!-- foreach(${data._childs_},name,child) -->
		<!-- if(${child}) -->
		<div class="cat_item">
			<ul>
				<!-- foreach(${child}, item) -->
				<table width="100%" cellpadding="0" cellspacing="3">
					<tr>
									
					<!-- 
						LIST CHILD COMPONENTS AND IF YOU NEED SHOW IT IN DIFFERENT SECTION
						LIKE CHECK COMPONENT NAME
					 -->
				
					<!-- if(${name} == 'catalog-documents') -->
						<td valign="top">&nbsp;</td>
						<td valign="top" width="100%"><a href="${VIRTUAL}${item.file}">${item.title}</a></td>
					<!-- ifelse(${name} == ' --- add you reg component name --- ') -->
					
					<!-- else -->
						<td valign="top"><!-- image(${item.file},width=100|height=100|alt=${item.title}|name=${item.title}) --></td>
						<td valign="top" width="100%">
							<h2><a href="${item.href}">${item.title}</a></h2>
							<div class="cat_descr">${item.short_desc}</div>
						</td>
					<!-- end -->
					</tr>
				</table>
				<!-- end -->
			</ul>
			<div class="clr"></div>
		</div>
		<!-- end -->
	<!-- end -->
	
<!-- 
	FIRST CATALOG'SPAGE (IF IS NOT USE ELEMENT YET)  
-->
<!-- else -->
	
	<!-- foreach(${data},cat) -->
	<div class="clr"></div>
	<div class="cat_item">
	
		<h2><a href="${cat.href}">${cat.title}</a></h2>
		<div class="cat_descr">
			<!--
				<!-- image(${cat.image},width=80|height=80|alt=${cat.title}|name=${cat.title}) -->
			-->
			${cat.short_desc}
		</div>
		
		<br/><br/>
		
		<!-- if(${cat._subs_}) -->	
			<!-- foreach(${cat._subs_},sub) -->
			<a href="${sub.href}">
				<!-- 
					<!-- image(${sub.image},width=30|height=30|alt=${sub.title}|name=${sub.title}) -->
				-->
				${sub.title}</a>&nbsp;|&nbsp;
			<!-- end -->
			
			<br/><br/>
		<!-- end -->
		
		<!-- foreach(${cat._childs_},name,child) -->
			<!-- if(${child}) -->
			<ul style="border: 1px solid #C0C0C0;float:left">
				<li><h3>${name}</h3></li>
				<!-- foreach(${child}, item) -->
				
					<!-- 
						LIST CHILD COMPONENTS AND IF YOU NEED SHOW IT IN DIFFERENT SECTION
						LIKE CHECK COMPONENT NAME
					 -->
				
					<!-- if(${name} == 'catalog-documents') -->
						<li><a href="${VIRTUAL}${item.file}">${item.title}</a></li>
					<!-- ifelse(${name} == ' --- add you reg component name --- ') -->
					
					<!-- else -->
						<li class="cat_note_s"><a href="${item.href}" title="${item.title}"><!-- image(${item.file},width=30|height=30|alt=${item.title}|name=${item.title}) --></a></li>
					<!-- end -->
				<!-- end -->
			</ul>
			<!-- end -->
		<!-- end -->
	</div>
	<!-- end -->
	
<!-- end -->

<!-- if(${paging_bar}) -->
	<div class="clr"></div>
	<!-- template('cmp-paging.tpl',${paging_bar}) -->
<!-- end -->
<div class="clr"></div>