
	var $finances = new $rdks.plugin();

	(function($){
		
		function _getValues(){

			var year = $rdks.form.getFieldByName("year").value,
				month = $rdks.form.getFieldByName("month").value;

				year = year.split("|")[0];
				month = month.split("|")[0];	

			return {year: year, month: month};
		}

		function _go(params){
			$rdks.http.redirect($rdks.http.setUrl("<?php echo $tpl['urlShopping']; ?>", params));
		}

		function _redirect(params){
			params.cardId = $rdks.http.getUrlParam('cardId');
			_go(params);
		}

		function _apply(){

			var params = _getValues();

			_redirect(params);
		}

		function _prev(){

			var params = _getValues();

			if(params.month == 1){
				params.year--;
				params.month = 12;
			} else {
				params.month--;
			}

			_redirect(params);
		}

		function _next(){

			var params = _getValues();

			if(params.month == 12){
				params.year++;
				params.month = 1;
			} else {
				params.month++;
			}

			_redirect(params);
		}

		$finances.filters = {
			apply: function(){
				_apply();
			},
			prev: function(){
				_prev();
			},
			next: function(){
				_next();
			},
			go: function(params){
				_go(params);
			}
		};

		$finances.getTotal = function(){
			var total = $("#sum").val();
				total = total.toString();
				total = $rdks.tool.addCommas(total);
			$("#total").text(total);
		}

	})(jQuery);

	function cbFinancesFilters(valid, data){
		$finances.filters.apply();
	}

	function cbRemove(btn, params){
		dataGrid.remove(btn, params, true);
	}

	function cbFinancesPaid(btn, response){
		if(response.success){
			$(btn)
			.removeClass("btn-default")
			.addClass("btn-success")
			.removeAttr('rel');

			$(btn)
			.find("span:first")
			.removeClass("glyphicon-exclamation-sign")
			.addClass("glyphicon-ok");

		}
	}

	function cbFinancesVisibilityDone(response){
		if(response.success){
			$rdks($rdks.cache.get("visibility")).removeRow();
		}
	}

	function cbFinancesVisibility(btn, params){
		$rdks.cache.set("visibility", btn);
		dataGrid.visibility(btn, params);
	}

	function cbFinancesCards(text, value, data){
		$finances.filters.go({cardId: value});
	}

	function cbFinancesMonth(text, value, data){
		$finances.filters.apply();
	}
