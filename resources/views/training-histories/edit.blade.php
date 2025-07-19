
{!! Form::model($training_history, ['id'=>'model-form','method' => 'PATCH','route' => ['training-histories.update', $training_history->id]]) !!}

<input type="hidden" name="user_id" value="{{$training_history->user_id}}">
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">×</span>
  </button>
  <h4 class="modal-title">Edit Training History</h4>
</div>
<div class="modal-body">
  @include('training-histories.fields')
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
  <button type="submit" class="btn btn-primary">Update</button>
</div>
{!! Form::close() !!}
