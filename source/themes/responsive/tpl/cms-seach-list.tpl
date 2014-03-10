<br/><br/>
<!-- if(${search_results}) -->
	<h3><!-- lingual(search_results_number) --> <span class="badge badge-info">${count_results}</span></h3>
	<!-- foreach(${search_results},row) -->
			<!-- if(${row.target_name} == 'pages') --><!-- end -->

			<div class="well">
			<!-- if(${row.title}) -->
				<a href="${row.href}" >${row.title}</a>
			<!-- elseif(${row.name}) -->
				<a href="${row.href}" >${row.name}</a>
			<!-- elseif(${row.public_name}) -->
				<a href="${row.href}" >${row.public_name}</a>
			<!-- end -->
				
			<!-- if(${row.body}) -->
				<p>${row.body}</p>
			<!-- elseif(${row.short_desc}) -->
				<p>${row.short_desc}</p>
			<!-- elseif(${row.desc}) -->
				<p>${row.desc}</p>
			<!-- end -->
			</div>
			
	<!-- end -->
	
	<!-- # PAGINATION # -->
	<!-- if(${paging_bar}) -->
		<!-- template(cmp-paging.tpl,${paging_bar}) -->
	<!-- end -->
	
<!-- else -->
<h3><!-- lingual(search_results_empty) --></h3>
<!-- end -->