	$rdks.http.getJson('/_json/posts/_latest', {code: "<?php echo \Roducks\framework\Form::getKey(); ?>"}, function(response){
		$rdks.modal.loader(false);

		$("#latest-post").text(response.data.post.title);
		$("#latest-post").attr('href', response.data.post.link);
	}, function(){
		$rdks.modal.loader(true);
	}, function(){
		$rdks.modal.loader(false);
	});