/*!
 *	RoducksJS Cart
 *
 *
 *	@copyright: Roducks
 *	@author: Rod
 *	@version: 1.0
 */
$rdks.cart = new $rdks.plugin();

(function($, $cart){

	function removeRow(id){
		$rdks(id, true).removeRow();
	}

	function setQty(id, _this, qty, cache){
		
		if(qty > 0){
			$(_this).attr('data-cache', qty);
			$rdks.http.postJson('/_json/cart/update', {id: id, qty: qty}, function(response){

				if(response.success){
					$rdks.modal.loader(false);
					$cart.onRemoveItem(response);
				} else {
					$(_this).val(cache);
					$(_this).attr('data-cache', cache);

					$rdks.alert.notice({
						result: response.success,
						icon: "warning",
						message: response.message
					});			
				}

			}, function(){
				$rdks.modal.loader(true);
			});
		} else {
			$(_this).val(cache);
		}	

	}
	
	function incQty(id, inc){

		var input = $("#"+id+"_qty"),
			cache = input.attr('data-cache'),
			qty = input.val();
			qty = parseInt(qty);
			qty += inc;

		if(qty <= 0){
			return;
		}	

		input.val(qty);
		setQty(id, input, qty, cache);
	}

	$cart.getCounter = function(){

		$rdks.http.getHtml('/_block/cart/counter', null, function(block){
			$("#rdks-cart-counter").html(block);
			$rdks.modal.loader(false);
			$rdks.st.footer();
		});

	}

	$cart.getSubtotal = function(id, subtotal){
		$("#"+id+"_subtotal").html(subtotal);
	}

	$cart.getTotals = function(){

		$rdks.http.getHtml('/_block/cart/totals', null, function(block){
			
			$(".rdks-cart-refresh").each(function(){
				$(this).remove();
			});

			$("#rdks-cart-grid").append(block);
			$cart.getCounter();

		}, function(){
			$rdks.modal.loader(true);
		});

	}

	$cart.removeItem = function(btn, params){

		dataGrid.remove(btn, params);

		$("."+params.index).each(function(){
			var id = $(this).attr('data-id');
			removeRow(id);
		});
	}

	$cart.onRemoveItem = function(response){

		if(response.data.refresh){
			$cart.getSubtotal(response.data.id, response.data.subtotal);
		}

		if(!response.data.hasItems){
			$("#btn-checkout").remove();
		}

		this.getTotals();
	}

	$cart.qty = {

		update: function(_this, id){
			var qty = $(_this).val(),
				cache = $(_this).attr('data-cache');

				qty = parseInt(qty);

			if($.trim(qty) == ""){
				$(_this).val(cache);
				qty = cache;
			}

			$("#btn-checkout").prop("disabled", false);

			if(cache == qty){
				return;
			}

			setQty(id, _this, qty, cache);
		},
		focus: function(_this){
			$rdks(_this).selectionRange(0);
			$("#btn-checkout").prop("disabled", true);
		},
		sub: function(id){
			incQty(id, -1);
		},
		add: function(id){
			incQty(id, 1);
		}

	};

})(jQuery, $rdks.cart);

function rdksCartRemoveItem(btn, params){
	$rdks.cart.removeItem(btn, params);
}

function rdksCartOnRemoveItem(response){
	$rdks.cart.onRemoveItem(response);
}
