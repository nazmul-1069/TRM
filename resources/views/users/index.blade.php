@extends('layouts.dashboard')
@section('page-title', 'Users')

@section('content')

<div class="content-header clearfix">
  <h2 class="pull-left">
    List of Users
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
    ajax: "{{route('users.data')}}",
    columns: [
      {title: 'Login ID', data: 'username'},
      {title: 'Name', data: 'name'},
      {title: 'Email', data: 'email'},
      {title: 'Group', data: 'role', name: "roles.display_name"},
      {title: 'Registered At', data: 'registered_at', name:'users.created_at'},
      {title: 'Actions', data: 'action', orderable: false, searchable: false}
    ]
  })
})
</script>
@endpush
