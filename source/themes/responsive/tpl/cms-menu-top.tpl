<ul class="<!-- if(!${level}) -->nav<!-- else -->dropdown-menu<!-- end -->">
	<!-- foreach(${nodes},note) -->
		<li class="<!-- if(${note.childs}) -->dropdown<!-- if(${level}) -->-submenu<!-- end --><!-- end --><!-- if(${note.current}) --> active<!-- end -->">
			<a href="${note.href}" ${note.target}>
				${note.title}
			</a>
			<!-- if(${note.childs}) --><!-- if(!${level}) --><b class="caret dropdown-toggle" data-toggle="dropdown"><span></span></b><!-- end --><!-- end -->
			${note.childs}
		</li> 
	<!-- end -->	
</ul>