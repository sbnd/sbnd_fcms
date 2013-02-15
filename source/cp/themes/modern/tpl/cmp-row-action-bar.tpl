<div><!-- for($i=0,${actions}[$i],$i++) --><!-- if(${i}) --> | <!-- end -->
	<!-- if(${actions[$i].disable}) -->
	<span class="disabled">${actions[$i].text}</span>
	<!-- else -->
	<a href="${actions[$i].link}" <!-- if(${actions[$i].rule_type}) -->onclick="<!-- if(${actions[$i].rule_type} == 'rule') -->${actions[$i].rule_text}<!-- else -->return FormActionManager.go(this, '${actions[$i].rule_type}', '${actions[$i].rule_text}')<!-- end -->"<!-- end --> class="action_row_${row_number}" lang="${actions[$i].key}">${actions[$i].text}</a>
	<!-- end -->
<!-- end -->
</div>