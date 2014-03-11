<!-- 
	TEMPLATE FOR SHOW VISUAL CATALOG'S ELEMENTS (ARTICLE, ITEMS, ... )	
 -->
 
 <!-- 
 	ELEMENT'S DETAILS
  -->
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td valign="top"><a href="${VIRTUAL}${data.file}"><!-- image(${data.file},width=300|height=300|alt=${data.title}|name=${data.title}) --></a></td>
		<td valign="top" width="100%">
			<h2>${data.title}</h2>
			<div class="cat_descr">${data.desc}</div>
		</td>
	</tr>
</table>	

<!-- 
	IF ELEMENTS IS TREE TYPE SHOW LIST OF SUB ELEMENTS
 -->
<!-- foreach(${data._subs_},k,sub,c) -->
<!-- if(${c}) -->&nbsp;|&nbsp;<!-- end -->
<a href="${sub.href}">${sub.title}</a>
<!-- end -->

<!-- 
	LIST CHILD COMPONENTS
 -->
	
<!-- foreach(${data._childs_},name,child) -->
	<!-- if(${child}) -->
	<div class="cat_item">
		<ul>
			<!-- foreach(${child}, item) -->
			
				<!-- 
					IF YOU NEED SHOW IT IN DIFFERENT SECTION LIKE CHECK COMPONENT NAME
				 -->

				<!-- if(${name} == 'catalog-article-gallery') -->
					<a href="${VIRTUAL}${item.file}">${item.title}</a><br/>
				<!-- ifelse(${name} == ' --- add you reg component name --- ') -->
				
				<!-- else -->
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td valign="top"><!-- image(${item.file},width=100|height=100|alt=${item.title}|name=${item.title}) --></td>
						<td valign="top" width="100%">
							<h2><a href="${item.href}">${item.title}</a></h2>
							<div class="cat_descr">${item.short_desc}</div>
						</td>
					</tr>
				</table>
				<!-- end -->
				
			<!-- end -->
		</ul>
		<div class="clr"></div>
	</div>
	<!-- end -->
<!-- end -->