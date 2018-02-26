function cbRolesAutocomplete(text, value){
	$rdks.http.redirect('<?php echo $tpl['autocomplete']['redirect']; ?>'+value);
}