<ul class="breadcrumb">
<!-- foreach(${cbreadcrumbs},row) -->
	<!-- if(!${row.is_last}) -->
		<li><a href="${row.link}">${row.title}</a>&nbsp;<span class="divider">/</span>&nbsp;</li>
	<!-- else -->
		<li class="active">${row.title}</li>
	<!-- end -->
<!-- end -->
</ul>