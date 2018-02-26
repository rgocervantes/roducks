$(".help").each(function(){
	var id = $(this).attr('id');
	var text = $(this).attr('data-text');
	$rdks("#"+id).tooltip(text);
});