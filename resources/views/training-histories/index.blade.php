@extends('layouts.dashboard')
@section('page-title', 'Training Histories')

@section('content')

<div class="content-header clearfix">
  <h2 class="pull-left">
    List of Training Histories
  </h2>  
</div>
<div class="content">
  <div class="alert alert-success" id="success-msg" style="display:none"></div>
  <div class="alert alert-error" id="error-msg" style="display:none"></div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="table-responsive">
        <table id="data-table" class="table table-bordered"></table>
      </div>
    </div>
  </div>
</div>
<div class="modal" id="modal-form">
  <div class="modal-dialog">
    <div class="modal-content"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#data-table').DataTable({
    serverSide: true,
    processing: true,
    ajax: "{{ route('training-histories.data') }}",
    columns: [
      @role('admin')
      {title: 'User', data: 'user_name',name: 'user_name'},
      @endrole
      {title: 'Training', data: 'training_title', name: 'training.title' },
      {title: 'From', data: 'started_at', name: 'started_at' },
      {title: 'To', data: 'ended_at', name: 'ended_at' },
      {title: 'No of Trainees', data: 'no_of_trainees', name: 'no_of_trainees' },
      {title: 'Duration', data: 'approved_duration', name: 'approved_duration' },
      {title: 'Location', data: 'location', name: 'location' },
      {title: 'Audience', data: 'audience', name: 'audience.name' },

      {title: 'Status', data: 'status', name: 'status.name', 
        render: function (val) 
                {
                    if(val == 'approved')
                    {
                    return "<a href=''><small class='label pull-right bg-green'>Approved</small></a>";
                    }
                    else if(val == 'pending')
                    {
                    return "<a href=''><small class='label pull-right bg-yellow'>Pending</small></a>";
                    }
                    else if(val == 'rejected')
                    {
                    return "<a href=''><small class='label pull-right bg-red'>Rejected</small></a>";
                    }
                }
      },

      {title: 'Actions', data: 'action', orderable: false, searchable: false, className: 'action-column'}
    ]
  })
})
</script>
@endpush
