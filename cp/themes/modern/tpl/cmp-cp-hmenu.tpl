<ul>
	<!-- foreach(${nodes},node) -->
		<li><a  <!-- if(${node.current}) -->class="current-topmenu"<!-- end --> href="${node.href}" title="${node.tooltip}">${node.title}</a>
			${node.childs}
		</li>
	<!-- end -->
</ul>