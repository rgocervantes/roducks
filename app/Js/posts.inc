	function cbSelectCategory(text, value, data){
		$rdks.http.redirect('<?php echo $tpl['categoryRedirect']; ?>'+value);
	}