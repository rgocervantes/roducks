$rdks.http.postJson('<?php echo \Roducks\Framework\URL::getAdminURL('/_service/foo/cross'); ?>', {id: 7}, function(res){
	console.log(res);
});