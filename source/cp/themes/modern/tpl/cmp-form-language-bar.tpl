<!-- if(${current}) -->
<script type="text/javascript">$(document).ready(function (){
	var form = Svincs.LangManager({
		current: "${current}",
		target : $('#lang_sys').get(0)
	}),
	list = $('.tabs a').click(function (){
		list.removeClass('current');
		$(this).addClass('current');

		form.langchange(this.lang);
		return false;
	});
});</script>
<input type="hidden" id="lang_sys"/>
<div class="tabs">
	<ul>
	<!-- foreach(${linguals},lang) -->
		<li><a href="#"<!-- if(${lang.key} == ${current}) --> class="current"<!-- end --> lang="${lang.key}"><!-- image(${lang.flag},width=18|height=13) --><span>${lang.text}</span></a></li>
	<!-- end -->
	</ul>
</div>
<!-- end -->