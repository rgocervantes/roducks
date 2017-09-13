$rdks.http.postJson('<?php echo \rdks\core\framework\URL::goToAdmin(); ?>/_service/foo/cross', {id: 7}, function(res){
	console.log(res);
});