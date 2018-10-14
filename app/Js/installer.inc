
function cbFormSuccessInstaller(response){
  if(response.success){
    $("#finish").show();
    $("#stepper").hide();
  }
}

function stepper(n){
  $rdks.pipe('stepper').set('step_'+n);
  $('#myTabs li:eq('+n+') a').tab('show');
}
