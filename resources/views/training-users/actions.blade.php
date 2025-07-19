@if(auth()->user()->hasRole('trainer') and $training_user->status->name != 'close')
<a href="{{route('training-histories.create', ['training_user_id'=>$training_user->id])}}" class="btn btn-primary edit-button">
    <i class="fa fa-plus"></i>
</a>
@endif

@if(auth()->user()->hasRole('admin') || $training_user->status->name != 'close') 
<a href="{{route('training-users.edit',['id'=>$training_user->id])}}" class="btn btn-success edit-button">
    <i class="fa fa-edit"></i>
</a>
@endif

@role('admin')
<a href="{{ route('training-users.delete', ['id' => $training_user->id]) }}" class="btn btn-danger action-button  delete-button">
    <i class="fa fa-times"></i>
</a>
@endrole
