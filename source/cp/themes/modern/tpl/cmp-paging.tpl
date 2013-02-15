<div class="paging">
   	<div class="pagingleft">
   		<!-- if(${paging_bar.max_pages}) -->
    	<div class="itemcount">${paging_bar.show_from_items} <!-- lingual(of) --> ${paging_bar.show_to_items} / ${paging_bar.show_all_items} <!-- lingual(items) --></div>
		<!-- end -->
		<div class="showpages"><!-- lingual(show) -->: 
			<!-- foreach(${paging_bar.max_page_rows},k,item) -->
				<!-- if(${k}) --> |<!-- end --><!-- if(${item.current}) -->${item.label}<!-- else --><a href="${item.link}">${item.label}</a><!-- end --> 
			<!-- end -->
		</div>
	</div>
	<!-- if(${paging_bar.max_pages}) -->
  	<div class="pagingright">      
    <!-- if(!${paging_bar.prev_link}) -->
        <a class="prev"><span class="inactive">&nbsp;</span></a>
    <!-- else -->
        <a class="prev" href="${paging_bar.prev_link}"><span class="active">&nbsp;</span></a>
    <!-- end -->    
    <!-- foreach(${paging_bar.pages},page) -->
    	<!-- if(${page.current}) -->
    	<label><!-- lingual(page) --></label><div class="current">${page.number}</div>   
        <label><!-- lingual(of) --><a href="${paging_bar.max_link}"> ${paging_bar.max_pages}</a></label>
    	<!-- end -->
    <!-- end -->     			     
    <!-- if(!${paging_bar.next_link}) -->
		<a class="next"><span class="inactive">&nbsp;</span></a>
    <!-- else -->
		<a class="next" href="${paging_bar.next_link}"><span class="active">&nbsp;</span></a>
    <!-- end -->  	
	</div>
	<!-- end -->
</div>