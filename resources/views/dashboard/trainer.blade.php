@extends('layouts/dashboard')
@section('page-title', 'Dashboard')
@section('content')
<div class="container-fluid">
  <div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
      <div class="card">
        <div class="header">
          <div class="row">
            <div class="col-md-6">
              <h2>Trainer Dashboard</h2><br>
              {{ auth()->user()->name }}
            </div>
            
            <div class="col-md-6">
              <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>User Name</th>
                      <th>Achieved Hours</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($top_achievements as $target)
                    <tr>
                      <td>{{$target->user->name}}</td>
                      <td>{{$target->user->username}}</td>
                      <td>{{ $target->achieved_hour/($target->target_hour * 100)}} %</td>
                    </tr>
                    @endforeach                    
                  </tbody>
                </table>
            </div>

          </div>
          
        </div>
        <div class="body">
          <div class="row">
            <div class="col-md-12">            
              @foreach($targets as $target)
              <div>
                {{$target->started_at->format('Y-m-d') .' / '.$target->ended_at->format('Y-m-d') }}                  
              </div>
              <div class="row">
              <div class="col-md-10">
              <div class="progress">
          
                <div class="progress-bar progress-bar-success" role="progressbar" style="width:{{$target->percentage}}%">
                   {{$target->achieved_hour}}
                </div>

                <div class="progress-bar progress-bar-danger" style="width:{{100 - $target->percentage}}%">
                  {{$target->target_hour - $target->achieved_hour}}
                </div>
                
                                 
              </div>
            </div>
            <div class="col-md-2">
              <span>{{$target->target_hour}} Hours</span>
            </div>
          </div>

             @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
