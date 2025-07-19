@extends('layouts.dashboard')
@section('page-title', 'Assign Training')
@section('content')

@if ($message = Session::get('success'))
<div class="alert alert-success">
    <p>{{ $message }}</p>
</div>
@endif
<div class="content-header clearfix">
  <h2 class="pull-left">
    Edit Assigned Training
  </h2> 
</div>
{!! Form::model($training_user, ['id'=>'model-form','method' => 'PATCh','route' => ['training-users.update', $training_user->id]]) !!}
<div class="content">
  <div class="alert alert-success" id="success-msg" style="display:none"></div>
  <div class="alert alert-error" id="error-msg" style="display:none"></div>  
  <div class="panel panel-default">
    <div class="box box-info">
        <div class="box-body">                                  
             @role('admin')
    <div class="row clearfix">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Start Date</label>
                <div class="form-line">
                    {!! Form::text('started_at', null, array('id' => 'started_at', 'placeholder' => 'Start Date','class' => 'form-control datetimepicker')) !!}
                </div>
                <span class="help-block"></span>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">End Date</label>
                <div class="form-line">
                    {!! Form::text('ended_at', null, array('id' => 'ended_at', 'placeholder' => 'End Date','class' => 'form-control datetimepicker')) !!}
                </div>
                <span class="help-block"></span>
            </div>
        </div>
    </div>

    <div class="row clearfix">
         <div class="col-sm-6">
            <div class="form-group">
                <label for="">User</label>
                {!! Form::select('user_id[]', $users, null, array('id' => 'user_id', 'class' => 'form-control select2','multiple' => "multiple")) !!}
                <span class="help-block"></span>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group required">
                <label class="control-label">Status</label>
                {!! Form::select('status_id', $statuses, null, array('id' => 'status_id', 'class' => 'form-control select2')) !!}
                <span class="help-block"></span>
            </div>
        </div>
    </div>
    @endrole
    @role('trainer')
    <div class="row clearfix">
        <div class="col-sm-6">
            <div class="form-group required">
                <label class="control-label">Status</label>
                {!! Form::select('status_id', $statuses, null, array('id' => 'status_id', 'class' => 'form-control select2')) !!}
                <span class="help-block"></span>
            </div>
        </div>
    </div>
    @endrole
    </div>
        <div class="box-footer">
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary pull-right">Update</button>
        </div>
    </div>
  </div>   
</div>
{{ Form::close() }}
@endsection