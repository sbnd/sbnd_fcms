<ul>
	<!-- foreach(${nodes},node) -->
		<li id="${node.uid}"><a  <!-- if(${node.current}) -->class="jstree-clicked"<!-- end --> href="${node.href}" title="${node.tooltip}">${node.title}</a>
			${node.childs}
		</li>
	<!-- end -->
</ul>