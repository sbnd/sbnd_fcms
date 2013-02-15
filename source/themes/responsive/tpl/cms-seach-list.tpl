<br/><br/>
<!-- if(${search_results}) -->
	<h3><!-- lingual(search_results_number) --> <span class="badge badge-info">${count_results}</span></h3>
	<!-- foreach(${search_results},row) -->
		
			<!-- if(${row.target_name} == 'pages') -->
				<div class="well">
					<a href="${row.href}" >${row.title}</a>
					<p>${row.body}</p>
				</div>
				
			<!-- elseif(${row.target_name} == 'evgeny_test_1') -->
				<div class="well">
					<a href="${row.href}" >${row.name}</a>
				</div>
				
			<!-- elseif(${row.target_name} == 'product_catalog') -->
				<div class="well">
					<a href="${row.href}" >${row.name}</a>
					<p>${row.body}</p>
				</div>
					
			<!-- elseif(${row.target_name} == 'catalog_news') -->
				<div class="well">
					<a href="${row.href}" >${row.name}</a>
					<p>${row.body}</p>
				</div>
					
			<!-- elseif(${row.target_name} == 'b_simple_news') -->
				<div class="well">
					<a href="${row.href}" >${row.name}</a>
					<p>${row.body}</p>
				</div>
						
			<!-- elseif(${row.target_name} == 'FAQ') -->
				<div class="well">
					<a href="${row.href}" >${row.name}</a>
					<p>${row.body}</p>
				</div>
			<!-- end -->
			
	<!-- end -->
	
	<!-- # PAGINATION # -->
	<!-- if(${paging_bar}) -->
		<!-- template(cmp-paging.tpl,${paging_bar}) -->
	<!-- end -->
	
<!-- else -->
<h3><!-- lingual(search_results_empty) --></h3>
<!-- end -->