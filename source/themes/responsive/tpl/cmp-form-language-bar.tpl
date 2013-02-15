<!-- if(${current}) -->
<script type="text/javascript">$(document).ready(function (){
	var form = Svincs.LangManager({
		current: "${active}",
		target : $('#lang_sys').get(0)
	}),
	list = $('.nav-tabs li').click(function (){
		list.removeClass('active');
		$(this).addClass('active');

		form.langchange(this.lang);
		return false;
	});
});
</script>
<input type="hidden" id="lang_sys"/>
<ul class="nav nav-tabs">
	<!-- foreach(${linguals},lang) -->
		<li<!-- if(${lang.key} == ${current}) --> class="active"<!-- end --> lang="${lang.key}"><a href="#"><!-- image(${lang.flag},width=18|height=13) --> ${lang.text}</a></li>
	<!-- end -->
</ul>
<!-- end -->