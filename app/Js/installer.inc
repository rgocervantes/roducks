
function cbFormSuccessInstaller(response){
  if(response.success){
    $rdks.modal.loader(false);
    $("#finish").show();
    $("#stepper").hide();
  } else {
    $rdks.alert.notice({
      title: "There was an error",
      result: false,
      message: "Fix form data and try again.",
      elastic: true
    });
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

    if (
      fields['database[name]'].value != "" &&
      fields['database[port]'].value != "" &&
      fields['database[user]'].value != ""
    ) {
      stepper(3);
    } else {
      stepper(2);
    }

  }
}

function next(){
  $rdks.form.name('foovar').submit();
}

function stepper(n){
  $rdks.pipe('stepper').set('step_'+n);
  $('#myTabs li:eq('+n+') a').tab('show');
}
