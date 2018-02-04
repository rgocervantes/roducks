<?php
use Roducks\framework\Language;
?>

function cbFormSuccessCheckout(response){
	$rdks.modal.loader(false);
	$rdks.checkout.next('#step-3');
}

function cbFilterChoice(id, text, value, data){
	$rdks.cache.unset(id+'_lock');
	$rdks.form.filter.choice(id, text, value, data);
}

function cbAddressFilter(list, id, text, value, data){

	$rdks.http.getJson('/_json/cart/get-'+list, {id: value}, function(response){

		if(response.success){

			var li = $rdks.html.li(
						$rdks.html.a("javascript:;", "<?php echo TEXT_NO_RESULTS; ?>")
					,{"style":"display: none;"});

			$.each(response.data[list], function(k, v){
				li += $rdks.html.li(
					$rdks.html.a("javascript:;",
						$rdks.html.span(v.name,{"class":"speller"})
					,{"data-text":v.name,"onclick":"cbFilterChoice('"+data.field+id+"','"+v.name+"',"+v.id+",{&quot;field&quot;:&quot;"+data.field+"&quot;});"})
				,{"class":"active","style":"display: list-item;"});
			});

			$("input[name="+data.field+id+"]").parent().find('div.rdks-ui-pos-rel').show();
			$("input[name="+data.field+id+"]").parent().find('span.help-block').text('');
			$("input[name="+data.field+id+"]").attr('type', 'hidden').val('');

			$("#fr-"+data.field+id+"-aux").find('ul').html(li);
			$("#"+data.field+id+"-aux-name").text("<?php echo Language::translate("Select a option","Selecciona una opción"); ?>");
			$("#"+data.field+id+"-aux-name").show().removeAttr("disabled");
			$("#"+data.field+id+"-aux-name").parent().find('span.glyphicon').show();

		} else {
			$("input[name="+data.field+id+"]").parent().find('div.rdks-ui-pos-rel').hide();
			$("input[name="+data.field+id+"]").attr('type', 'text').val('');
			$("#"+data.field+id+"-aux-name").hide();
			$("#"+data.field+id+"-aux-name").parent().find('span.glyphicon').hide();
		}

		$rdks.modal.loader(false);

	}, function(){
		$rdks.modal.loader(true);
	});

}

function cbCountry(text, value, data){

	if($rdks.cache.isset(data.field+'_country_lock')){
		$rdks.cache.unset(data.field+'_country_lock');
		return;
	}

	cbAddressFilter('states', '_state', text, value, data);
	if(data.field == 'shipping' && $(this).is(":checked")){
		$rdks.checkout.setBilling('_country');
	}
}

function cbState(text, value, data){

	if($rdks.cache.isset(data.field+'_state_lock')){
		$rdks.cache.unset(data.field+'_state_lock');
		return;
	}

	cbAddressFilter('cities', '_city', text, value, data);
	if(data.field == 'shipping' && $(this).is(":checked")){
		$rdks.checkout.setBilling('_state');
	}	
}

function cbCity(text, value, data){
	if(data.field == 'shipping' && $(this).is(":checked")){
		$rdks.checkout.setBilling('_city');
	}	
}

function cbPostalCode(_this){
	
	var code = _this.value;
	
	$rdks.http.action('/_json/cart/post-code', {code: code}, function(response){
		$("#lock").val('ok').focus().blur();
		$rdks.checkout.submit(null);
	});

}