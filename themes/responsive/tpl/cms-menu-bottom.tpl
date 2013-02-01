<ul class="nav nav-pills">
	<!-- foreach(${nodes},note) -->
		<li <!-- if(${note.current}) -->class="active"<!-- end -->><a href="${note.href}" ${note.target}>${note.title}</a></li>
	<!-- end -->
</ul>