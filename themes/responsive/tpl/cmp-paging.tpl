<div class="pagination">
	<!-- if(${paging_bar.max_pages}) -->
		<ul>
		
			<!-- # PREV LINK # -->
			<li>
				<!-- if(!${paging_bar.prev_link}) -->
					<a href="#" class="disabled">&laquo;</a>
				<!-- else -->
					<a href="${paging_bar.prev_link}">&laquo;</a>
				<!-- end -->
			</li>	

			<!-- # PAGE NUMBER # -->
			<!-- foreach(${paging_bar.pages},page) -->
				<!-- if(${page.current}) -->
				<li><a href="#">${page.number}&nbsp;/&nbsp;${paging_bar.max_pages}</a></li>
				<!-- end -->
			<!-- end -->
			
			<!-- # NEXT LINK # -->
			<li>
				<!-- if(!${paging_bar.next_link}) -->
					<a href="#" class="disabled">&raquo;</a>
				<!-- else -->
					<a href="${paging_bar.next_link}">&raquo;</a>
				<!-- end --> 
			</li>
		</ul>
	<!-- end -->
</div>