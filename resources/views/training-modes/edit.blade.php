{!! Form::model($training_mode,['id'=>'model-form','method' => 'PATCH','route' => ['training-modes.update', $training_mode->id]]) !!}
<div class="modal-header">
  <button mode="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">×</span>
  </button>
  <h4 class="modal-title">Edit Training Mode</h4>
</div>
<div class="modal-body">
  @include('training-modes.fields')
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
  <button type="submit" class="btn btn-primary">Update</button>
</div>
{!! Form::close() !!}
