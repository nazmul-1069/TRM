{!! Form::model($training_audience,['id'=>'model-form','method' => 'PATCH','route' => ['training-audiences.update', $training_audience->id]]) !!}
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">×</span>
  </button>
  <h4 class="modal-title">Edit Training Audiences</h4>
</div>
<div class="modal-body">
  @include('training-audiences.fields')
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
  <button type="submit" class="btn btn-primary">Update</button>
</div>
{!! Form::close() !!}
