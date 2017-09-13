var finances = {};

finances.getMonth = function(m){
	$rdks.http.redirect("/finances?month="+m);
}