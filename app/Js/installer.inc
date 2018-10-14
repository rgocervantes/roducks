
function cbFormSuccessInstaller(response){
  if(response.success){
    $rdks.modal.loader(false);
    $("#finish").show();
    $("#stepper").hide();
  }
}

function cbFormSubmitInstaller(valid, data){
  var fields = $rdks.form.name('foovar').getFields();
  if(
    fields['site[title]'].value != "" &&
    fields['site[email_from]'].value != "" &&
    fields['site[email_to]'].value != "" &&
    fields['site[timezone]'].value != "" &&
    fields['default_language'].value != ""
  ){
    stepper(2);
  }
}

function next(){
  $rdks.form.name('foovar').submit();
}

function stepper(n){
  $rdks.pipe('stepper').set('step_'+n);
  $('#myTabs li:eq('+n+') a').tab('show');
}
