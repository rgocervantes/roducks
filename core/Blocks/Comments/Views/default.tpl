<h1>Comments:</h1>

{{% @each $comment in $comments %}}
  <div class="comment-box">
    <div class="comment-inner">
      <img src="{{% $comment[picture] %}}" width="45" height="45">
      <div class="">
        <span><b>{{% $comment[name] %}}</b></span>
      </div>
      <div class="">
        <span>{{% $comment[date] %}}</span>
      </div>
      <div class="clearfix"></div>
      <p>{{% $comment[post] %}}</p>
    </div>
  </div>
{{% @endeach %}}

<div rdks-each="comments" class="rdks-template comment-box">
  <div class="comment-inner">
    <img rdks-src="{{picture}}" width="45" height="45">
    <span>{{name}}</span>
    <span>{{date}}</span>
    <p>{{comment}}</p>
  </div>
</div>

<form id="form-comments" name="form_comments" method="post"
rel="rdks-form"
data-notification="false"
data-callback-success="cbFormSuccessComments"
data-callback-loading="cbFormLoading"
data-callback-error="cbFormError"
data-alert-warning="{{% _text('FORM_WARNING') %}}"
data-alert-error="{{% _text('FORM_ERROR') %}}"
data-alert-failed="{{% _text('FORM_FAILED') %}}"
data-reset="true"
data-ajax="true"
data-json="true"
data-focus="true"
action="/_service/comments/add/{{% $id %}}">

    <input type="hidden" name="form-key" value="{{% @form:key %}}" />

    <div class="form-group has-feedback">
      <label class="control-label" for="price"><span class="rdks-ui-color-red">*</span> {{% __('comments') %}}</label>
      <textarea name="comment" data-required="true" class="form-control" rows="8" cols="80"></textarea>

      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
      <span class="help-block">No HTML markup is allowed</span>
    </div>

    <button type="submit" name="send" class="btn btn-lg btn-info"><span class="glyphicon glyphicon-send" aria-hidden="true"></span> Send</button>

</form>
