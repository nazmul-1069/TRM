@role('admin')
<a href="{{ route('training-users.create', ['training_id' => $training->id]) }}" class="btn btn-primary action-button assign-button">
    <i class="fa fa-angle-double-right"></i>
</a>
<a href="{{route('trainings.edit',['id'=>$training->id])}}" class="btn btn-success action-button edit-button">
    <i class="fa fa-edit"></i>
</a>
<a href="{{ route('trainings.delete', ['id' => $training->id]) }}"class="btn btn-danger action-button  delete-button">
    <i class="fa fa-times"></i>
</a>
@endrole
@role('trainer')
@endrole
