  $rdks.pipe('stepper').init();

  $('#myTabs a').click(function (e) {
    e.preventDefault();
    var index = $(this).attr('data-index');
        index = parseInt(index);

    if($rdks.pipe('stepper').get().indexOf('step_'+index) != -1 || index == 0){
      $(this).tab('show');
    }

  });
