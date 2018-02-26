
	// Checkout
	$rdks.checkout.init({
		totalSteps: 3,
		startOnStep: <?php echo $startOnStep; ?>,
		onSelect: function(step){

			if(step == 3){
				$("#checkout").prop("checked", true);
				$("#rdks-checkout").attr('data-ajax', "false");
				$("#rdks-checkout").attr('data-json', "false");
			} else {
				$("#checkout").prop("checked", false);
				$("#rdks-checkout").attr('data-ajax', "true");
				$("#rdks-checkout").attr('data-json', "true");
			}
			
			$rdks.st.footer();
		},
		onSubmit: function(btn){}
	});

	$rdks.st.footer();

<?php if(empty($shipping['states'])): ?>
	$("#shipping_state-aux-name").parent().find('span.glyphicon').hide();
	$("#shipping_sate-aux-name").hide();
<?php endif; ?>

<?php if(empty($shipping['cities'])): ?>
	$("#shipping_city-aux-name").parent().find('span.glyphicon').hide();
	$("#shipping_city-aux-name").hide();
<?php endif; ?>

<?php if(empty($billing['states'])): ?>
	$("#billing_state-aux-name").parent().find('span.glyphicon').hide();
	$("#billing_sate-aux-name").hide();
<?php endif; ?>

<?php if(empty($billing['cities'])): ?>
	$("#billing_city-aux-name").parent().find('span.glyphicon').hide();
	$("#billing_city-aux-name").hide();
<?php endif; ?>

<?php if($startOnStep > 1): ?>
	$rdks.cache.set('shipping_country_lock', true);
	$rdks.cache.set('shipping_state_lock', true);
	$rdks.cache.set('billing_country_lock', true);
	$rdks.cache.set('billing_state_lock', true);
<?php endif; ?>