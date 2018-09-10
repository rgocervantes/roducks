<div class="container">
  {{% @template('go-back') %}}
  {{% @template('title') %}}
  <span>{{% date|strtotime|$created_at|d/m/Y,* %}}</span>
  <p>{{% $description %}}</p>
</div>
