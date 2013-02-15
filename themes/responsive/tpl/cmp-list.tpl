<script type="text/javascript">
$(document).ready(function (){
	var fbody = $(".toggle_containerfilter").hide(),
		tab2 = $(".filtertab").click(function(){
			$(this).toggleClass("active").next().slideToggle("fast");
			return false; 
		}).get(1);
	if(tab2) $(tab2).hide();
	
<!-- if(${use_order}) -->
	var runTRee = <!-- if(${parent_self}) -->true<!-- else -->false<!-- end -->,
		spl = "${use_order}".split("?"), cmp = '', get = {}, p;
		 
	if(spl[1]){
		var spl2 = spl[1].split('&');
		for(p in spl2){
			var ex = spl2[p].split("=");

			if(ex[0] != '${prefix}id' && ex[0] != '${prefix}column' && ex[0] != '${prefix}dir'){
				get[ex[0]] = ex[1];
			}
		}
	}
	$('table.list_grid tr.row').draggable({
		helper: 'clone',
		axis: 'y',
		handle: '.dragable',
		opacity: 0.5
	});
	$((runTRee ? 'table.list_grid tr.row,': '')+'table.list_grid tr.space_row').droppable({
		hoverClass: "list_row_light",
		drop: function(e, ui){
			var target, next, move = ui.draggable.attr('lang');
			if(this.className.indexOf('space_row') != -1){
				target = 0;
				next = $(this).next().attr('lang');
				if(next == move){
					next = $(this).next().next().next().attr('lang');
				}		
			}else{
				target = $(this).attr('lang');
				next = 0;
			}

			var request = ''; for(p in get){
				if(request) request += '&'; request += p+'='+get[p];
			}
			location.href = spl[0]+"?"+request+"&${prefix}cmd=order_up&${prefix}id[]="+target+"&${prefix}id[]="+move+"&${prefix}id[]="+next;
		}
	});
<!-- end -->
});
</script>

<div class="list">
	<table class="list_grid table table-hover" border="0" cellspacing="1" cellpadding="0">
		<tr>
			<!-- if(${use_order}) --><th class="resetorder"><a href="${use_order}">&nbsp;</a></th><!-- end -->
			<th class="selectable">
				<!-- if(${use_checkbox}) --><input type="checkbox" onclick="managerList.changeAll(this);" value="${cmd}"/><!-- end -->
			</th>
			<!-- foreach(${headers},head) -->
			<th ${head.attr}>${head.label}<!-- if(${head.selected}) --><span class="sort sort<!-- if(${head.isdown}) -->down<!-- else -->up<!-- end -->">&nbsp;</span><!-- end --></th>
			<!-- end -->
		</tr>	
		<tr lang="0" class="space">
			<!-- if(${use_order}) --><td></td><!-- end -->
			<td></td>
			<td colspan="${column_length}"></td>
		</tr>
		<!-- foreach(${rows},row) -->
			<!-- if($i=0) --> <!-- end --><!-- for($p=0; $p < ${row.row_level}; $p++) --><!-- if($i+=32) --> <!-- end --><!-- end -->
		
		<tr lang="${row.id}" class="<!-- if(!${row.row_level}) --> info<!-- end --><!-- if(${row.even_class}) --> even<!-- else --> odd<!-- end -->">
			<!-- if(${use_order}) --><td class="dragable"><span>&nbsp;</span></td><!-- end -->
			<td class="selectable">
				<!-- if(${use_checkbox}) --><input type="checkbox" value="${row.id}" name="${cmd}[]" id="${cmd}${row.row_number}" lang="${row.row_level}" onclick="managerList.treeChecker(this,event)" ${row.action_bar.function} /><!-- end -->
			</td>
			
			<!-- iforeach(${row.columns},ii,column) -->
			<td <!-- if(!${ii}) -->class="title" style="padding-left: ${i}px;"<!-- end --> ${column.attr}>
				<!-- if(!${ii}) -->
					<span>${column.label}</span>
					<!-- template(cmp-row-action-bar.tpl,${row.action_bar}) -->
				<!-- else -->
					${column.label}
				<!-- end -->
			</td>
			<!-- end -->
		</tr>
		<tr lang="${row.id}" class="space">
			<!-- if(${use_order}) --><td></td><!-- end -->
			<td></td>
			
			<td colspan="${column_length}"></td>
		</tr>
		<!-- end -->
	</table>
</div>

<div class="tools">
	<!-- template(cmp-paging.tpl) -->
	<div class="actions">
	<!-- template(cmp-action-bar.tpl,${action_bar}) -->
    </div>
</div>