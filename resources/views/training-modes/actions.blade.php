<a href="{{route('training-modes.edit',['id'=>$training_mode->id])}}" class="btn btn-success action-button edit-button">
    <i class="fa fa-edit"></i>
</a>
<a href="{{ route('training-modes.delete', ['id' => $training_mode->id]) }}" class="btn btn-danger action-button delete-button">
    <i class="fa fa-times"></i>
</a>
