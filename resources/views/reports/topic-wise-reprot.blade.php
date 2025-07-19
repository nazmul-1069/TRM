@extends('layouts.dashboard')
@section('page-title', 'Training Histories')

@section('content')

<div class="content-header clearfix">
  <h2 class="pull-left">
    List of Training Topics Info
  </h2> 
</div>

<div class="content">
  <div class="alert alert-success" id="success-msg" style="display:none"></div>
  <div class="alert alert-error" id="error-msg" style="display:none"></div>
  <div class="panel panel-default">    
    <div class="panel-body">
       <div class="bg-gray color-palette" style="padding: 10px; margin-bottom: 10px">
        <div class="form-inline">
          <div class="form-group">
            <label for="daterange">Date Range </label>
            <div id="reportrange" class="form-control">
              <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
              <span></span> <b class="caret"></b>
            </div>
          </div>
          <div class="form-group" style="margin-left: 20px" id="filter-box">
            <label for="username">Topic </label>
          </div>
        </div>
      </div>
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
    var startDate = moment().subtract(29, 'days');
    var endDate = moment();
    var usergroup = '';
    var table = $('#data-table').DataTable({
      initComplete: function () {
        this.api().columns().every( function () {
          var column = this;
          if(column[0] == 1){
            var select = $('<select class="form-control"><option value=""></option></select>')
            .appendTo($("#filter-box"))
            .on('change', function () {
              var val = $.fn.dataTable.util.escapeRegex(
                $(this).val()
                );
              usergroup = val;
              column
              .search( val ? val : '')
              .draw();

            } )

            $.ajax({
              url: "{{route('trainings.titles') }}",
              type: 'GET',
              success: function(data){
                
                for(var key in data){
                  var d = data[key];
                  console.log(d);  
                  select.append( '<option value="'+d+'">'+d+'</option>' )  
                }
              }
            })     
            }
          })
      },
      
      dom: 'Bfrtip',
      buttons: [
        { extend: "excel", text: "Download Excel", title: 'Top Contents' },
        { extend: "csv", text: "Download CSV", title: 'Top Contents' }
      ], 


      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ route('reports.topic-wise-report-data') }}",
        data: function(d){
          d.start_date = startDate ? startDate.format('YYYY-MM-DD HH:mm:ss'): '';
          d.end_date = endDate ? endDate.format('YYYY-MM-DD HH:mm:ss') : '';
        }
      },
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
            if(val == 'approved') {
              return "<a href=''><small class='label pull-right bg-green'>Approved</small></a>";
            }
            else if(val == 'pending') {
              return "<a href=''><small class='label pull-right bg-yellow'>Pending</small></a>";
            }
            else if(val == 'rejected') {
              return "<a href=''><small class='label pull-right bg-red'>Rejected</small></a>";
            }
          }
        },     
      ]
    })

    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
      $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $('#reportrange').daterangepicker({
      startDate: start,
      endDate: end,
      ranges: {
       'Today': [moment(), moment()],
       'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
       'Last 7 Days': [moment().subtract(6, 'days'), moment()],
       'Last 30 Days': [moment().subtract(29, 'days'), moment()],
       'This Month': [moment().startOf('month'), moment().endOf('month')],
       'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
     },
     locale: { cancelLabel: 'Clear' }
    }, cb);  
    cb(start, end);
    $("#reportrange").on('apply.daterangepicker', function(e, picker){
      console.log('clicked')
      startDate = picker.startDate;
      endDate = picker.endDate;
      table.draw(false);
    })
    $('#reportrange').on('cancel.daterangepicker', function(ev, picker) {
      //do something, like clearing an input
      startDate = undefined;
      endDate = undefined;
      $('#reportrange span').html('');
      table.draw(false);
    });     
})
</script>
@endpush
