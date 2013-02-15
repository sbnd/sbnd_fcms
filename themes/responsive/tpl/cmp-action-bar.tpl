<!-- foreach(${actions},$action) -->
	<!-- if(${action.disable}) -->
	<a class="${action.key} btn_disabled" href="#" onclick="return false"><span>${action.text}</span></a>
	<!-- else -->
	<button name="${cmd}<!-- if(${is_ie7}) -->${action.key}<!-- end -->" value="${action.key}" <!-- if(${action.rule_type}) -->onclick="<!-- if(${action.rule_type} == 'rule') -->${action.rule_text}<!-- else -->return FormActionManager.go(this, '${action.rule_type}', '${action.rule_text}')<!-- end -->"<!-- end --> lang="${action.key}" type="submit" class="cmd_btn ${action.key}<!-- if(${action.key} == 'save') --> btn-primary<!-- end -->"><span>${action.text}</span></button>
	<!-- end -->
<!-- end -->