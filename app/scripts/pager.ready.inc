	pageControl.init({ 	
		instance: "pageControl",
		labels: {
			first: "&laquo; First",
			last: "Last &raquo;",
			back: "&larr; Back",
			next: "Next &rarr;"
		},
		//style: 'btn-success',						
		links: 10, // number of links to be shown (5|10)
		total: <?php echo $totalPages; ?>, //total of pages
		onSelect: function(page){
			$rdks.modal.loader(true);
			$rdks.http.redirect("<?php echo $pageRedirect; ?>"+page);
		} 
	});
