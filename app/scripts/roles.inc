function cbRolesAutocomplete(text, value){
	$rdks.http.redirect('<?php echo $redirectAutocomplete; ?>'+value);
}