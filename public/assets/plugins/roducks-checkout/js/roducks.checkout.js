/*!
 *	RoducksJS Checkout
 *
 *
 *	@copyright: Roducks
 *	@author: Rod
 *	@version: 1.0
 */
$rdks.checkout = new $rdks.plugin();

(function($, $checkout){

	var _step = 1,
		_current = 1;

	function _getId(id){

		var n = id.replace(/#step-(\d+)$/,'$1');
			n = parseInt(n);

		return n;
	}

	function _selected(id){

		var n = _getId(id);

		if(_step >= n) {
			_current = n;
			$(".rdks-checkout-step").hide();
			$("ul#rdks-checkout-tab-steps li > a").each(function(){
				$(this).removeClass("current");
			});
			$("a[href='"+id+"']").addClass("active current");
			$(id).show();

			$checkout._settings.onSelect(_current);
			$rdks.scroll.go("#top");
		}
	}

	function _fillBilling(_this){
		var name = _this.name,
			value = _this.value,
			field = name.replace(/^shipping\[([a-zA-Z_]+)\]$/, '$1');
		
		document.forms["rdks-checkout"].elements['billing['+field+']'].value = value;
	}

	function _fillBillingData(){

		$(".shipping").each(function(){
			_fillBilling(this);
		});

		$(".filter-shipping").each(function(){
			var field = this.name.replace(/^[a-z]+(\_.+)$/, '$1');
			$checkout.setBilling(field);
		});		
	}

	function _cleanBillingData(){

		$(".billing").each(function(){
			this.value = "";
		});

		$(".filter-billing").each(function(){
			this.value = "";
			var name = this.name,
				label = $(this).attr('data-label');
			$("#"+name+"-aux-name").text(label);
		});		
	}

	$checkout.init = function(obj){

		var settings = {
			totalSteps: 1,
			startOnStep: 1,
			onSelect: function(step){},
			onSubmit: function(){}
		}, 
		shipping = document.forms["rdks-checkout"].elements['shipping_option'],
		payment = document.forms["rdks-checkout"].elements['payment_option'],
		shippingChecked = false,
		paymentChecked = false,
		id, 
		_class;

		this._init(settings, obj);

		if(this._settings.startOnStep > 1){

			for(var i = 1; i <= this._settings.startOnStep; i++){
				id = "#step-"+i;
				
				if(i == this._settings.startOnStep){
					_class = "active current";
					$(id).show();
				} else {
					_class = "active";
				}

				$("a[href='"+id+"']").addClass(_class);
			}

			_step = this._settings.startOnStep;
			_current = this._settings.startOnStep;

			$checkout._settings.onSelect(_step);

		} else {
			_selected('#step-1');
		}

		$("#same-address").click(function(){
			var id = $(this).val();

			if($(this).is(":checked")){
				_fillBillingData();
				$(id).hide();
			} else {
				_cleanBillingData();
				$(id).show();
			}
		});

		$("ul#rdks-checkout-tab-steps li > a").click(function(e){
			e.preventDefault();

			var id = $(this).attr('href'),
				n = _getId(id);

			_selected(id);
		});

		for (var i = 0; i < shipping.length; i++) {
			if(shipping[i].checked){
				$(shipping[i]).trigger('click');
				shippingChecked = true;
				break;
			}
		}

		if(!shippingChecked){
			$(shipping[0]).trigger('click');
		}

		for (var j = 0; j < payment.length; j++) {
			if(payment[j].checked){
				$(payment[j]).trigger('click');
				paymentChecked = true;
				break;
			}
		}	

		if(!paymentChecked){
			$(payment[0]).trigger('click');
		}			

	}

	$checkout.back = function(id){
		_selected(id);
	}

	$checkout.next = function(id){

		var n = _getId(id);

		if(_step < this._settings.totalSteps && _current == _step){
			_step++;
		}

		if(_step == n || _step == this._settings.totalSteps){
			_selected(id);
		}

	}

	$checkout.fillBilling = function(_this){
		if($("#same-address").is(":checked")){
			_fillBilling(_this);
		}	
	}

	$checkout.setBilling = function(fieldName){

		var fieldObj1 = document.forms["rdks-checkout"].elements['shipping'+fieldName],
			fieldObj2 = document.forms["rdks-checkout"].elements['billing'+fieldName];
		
		fieldObj2.value = fieldObj1.value;
	}

	$checkout.blur = function(_this){

		if(!$("#same-address").is(":checked")){
			return;
		}

		var fieldName,
			name = _this.name,
			rule = /^shipping([a-zA-Z_]+)$/;

		if(rule.test(name)){
			fieldName = name.replace(rule, '$1');
			this.setBilling(fieldName);
		}

		$rdks.form.blur(_this);
	}

	$checkout.cleanBilling = function(fieldName){
		document.forms["rdks-checkout"].elements['billing'+fieldName].value = "";
	}

	$checkout.submit = function(btn){
		$("#rdks-checkout-submit").trigger('click');
		if(btn != null)
			this._settings.onSubmit.call(this, btn);
	}

	$checkout.info = function(containerId, boxId, display){
		if(display){
			var info = $(boxId).html();
			$(containerId).html(info).show();
		} else {
			$(containerId).html('').hide();
		}
	}

	$checkout.shipping = function(_this, containerId, boxId, display){
		this.info(containerId, boxId, display);

		var option = _this.value;

		$rdks.http.postJson('/_json/cart/shipping', {option: option}, function(response){
			$rdks.cart.getTotals();
		}, function(){
			$rdks.modal.loader(true);
		});
	}

	$checkout.payment = function(_this, containerId, boxId, display){
		this.info(containerId, boxId, display);

		var option = _this.value;

		$rdks.http.postJson('/_json/cart/payment', {option: option}, function(response){
			$rdks.modal.loader(false);
		}, function(){
			$rdks.modal.loader(true);
		});
	}

	$checkout.coupon = function(btn, _apply){

		var coupon = $("#coupon").val(),
			apply = (_apply) ? 1 : 0;

		if($rdks.st.isEmpty(coupon)){
			return;
		} else {
			$(btn).prop("disabled", true);
		}

		$rdks.http.action('/_json/cart/coupon', {coupon: coupon, apply: apply }, function(response){
			if(response.success){
				$rdks.cart.getTotals();

				if(!_apply){
					$("#coupon").val('');
					$("#coupon-remove").hide();
					$("#coupon-icon").removeClass('glyphicon-ok glyphicon-tag').addClass('glyphicon-tag');
				} else {
					$("#coupon-remove").show();
					$("#coupon-icon").removeClass('glyphicon-ok glyphicon-tag').addClass('glyphicon-ok');
				}
			}
			$(btn).prop("disabled", false);
		});
	}

})(jQuery, $rdks.checkout);
