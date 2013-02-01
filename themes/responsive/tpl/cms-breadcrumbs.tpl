<ul class="breadcrumb">
<!-- foreach(${BREADCRUMBS},page) -->
	<!-- if(!${page.rem}) -->
		<!-- if(!${page.current}) -->
			<li><a href="${page.href}" ${page.target}>${page.title}</a>&nbsp;<span class="divider">/</span>&nbsp;</li>
		<!-- else -->
			<li class="active">${page.title}</li>
		<!-- end -->
	<!-- end -->
<!-- end -->
</ul>