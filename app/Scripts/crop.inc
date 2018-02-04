
function cropInit(){

	$('#x').val(0);
	$('#y').val(0);
	$('#w').val(0);
	$('#h').val(0);

	$('#crop').Jcrop({
		aspectRatio: 1, //set this for square, comment this if you want an irregular figure.
		onSelect: function(c){
			
			$('#x').val(c.x);
			$('#y').val(c.y);
			
			var w = $(".jcrop-tracker").width();
			var h = $(".jcrop-tracker").height();
			
			$('#w').val(w);
			$('#h').val(h);

		}
	});	

}

function cropComplete() {
	$rdks.modal.loader(false);

	$(".jcrop-holder").before('<div id="crop"></div>').remove();
	$("#btn-upload-box").removeClass('display-none');
	$("#btn-crop").addClass('display-none');
}

function cbCropSuccess(response) {
	
	if(response.success) {
		var img_full = response.data.dir + response.data.img_full,
			img_cropped = response.data.dir + response.data.img_cropped;
		$("#cropped").html( $rdks.html.a('javascript:;', $rdks.html.img( img_cropped ) , {"onclick":"$rdks.alert.image({&quot;src&quot;: &quot;"+img_full+"&quot;});"}) );

		$rdks.http.postJson('<?php echo $tpl['urlJsonPicture']; ?>', {picture: response.data.name}, function(response){
			cropComplete();
		});

	} else {
		$rdks.alert.notice({
			result: response.success,
			message: response.message,
			elastic: true,
			buttons: {
				large: true
			}
		});
	}

}

function cbCropError() {

	cropComplete();

	$rdks.alert.notice({
		result: false,
		message: 'Something went wrong!',
		elastic: true,
		buttons: {
			large: true
		}
	});
}

var uploader = new $rdks.uploader();
			