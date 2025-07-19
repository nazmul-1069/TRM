<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use App\TrainingHistory;
use App\User;
use App\Training;
use App\TrainingUser;
use App\TrainingType;
use App\TrainingMode;
use App\TrainingTarget;
use App\Status;
use App\TrainingAudience;
use Excel;

class TrainingHistoryController extends Controller
{
    public function index()
    {
        return view('training-histories.index');
    }

    public function indexData(Datatables $datatables)
    {
        $query = TrainingHistory::with('training')
        ->with('audience')
        ->with('user')
        ->with('status')
        ->select('training_histories.*');
        if(auth()->user()->hasRole('trainer')){
            $query = $query->where('user_id', auth()->user()->id);
        }
        $datatables = $datatables->eloquent($query)
          ->addColumn('user_name', function ($training_history) {
              return $training_history->user->name;
          })
          ->addColumn('status', function ($training_history) {
              return $training_history->status->name;
          })
          ->addColumn('audience', function ($training_history) {
            if($training_history->audience)
            {
              return $training_history->audience->name;  
            }
            return '';                 
          })
          ->addColumn('training_title', function ($training_history) {
              return $training_history->training->title;
          })
          ->addColumn('action', function ($training_history) {
              return view('training-histories.actions', ['training_history' => $training_history]);
          });
        return $datatables->make(true);
    }


    public function create(Request $request)
    {

        if(auth()->user()->hasRole('admin')){
            $users = User::where('is_active', true)
            ->where('company_id', auth()->user()->company->id)
            ->wherehas('roles', function($query){
                $query->where('name', 'trainer');
            })
            ->pluck('username', 'id');
        }

        if(auth()->user()->hasRole('trainer'))
        {           
            $id = $request->training_user_id;
            $user_id = TrainingUser::where('id',$id)->pluck('user_id');           
        }

        $audiences = TrainingAudience::orderBy('name')->pluck('name','id');
        
        $training_types = TrainingType::orderBy('name')->pluck('name','id');

        $training_modes = TrainingMode::orderBy('name')->pluck('name', 'id');
        //dd($training_modes);
        $training_user = TrainingUser::find($request->training_user_id);
        return view('training-histories.create', compact('users', 'training_modes', 'training_user','audiences','training_types'));
    }

    public function store(Request $request)
    {
        if(auth()->user()->hasRole('trainer')) {
            $this->validate($request, [
                'training_user_id' => 'required',
                'training_mode_id' => 'required',
                'started_at' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        if(!empty($request->started_at)
                        && !empty($request->ended_at)
                        && !empty($request->training_user_id)){
                            $started_at = Carbon::parse($request->started_at)->addSecond();
                            $ended_at = Carbon::parse($request->ended_at)->subSecond();
                            if(!$started_at->gt($ended_at)){
                                $training_user = TrainingUser::find($request->training_user_id);
                                $training_histories = TrainingHistory::where('user_id', $training_user->user_id)
                                ->whereBetween('started_at', [$started_at, $ended_at])
                                ->orWhereBetween('ended_at', [$started_at, $ended_at])
                                ->orWhere(function($query) use($started_at, $ended_at){
                                    $query->where('started_at', '<=', $started_at)
                                    ->where('ended_at', '>=', $started_at);
                                })
                                ->orWhere(function($query) use($started_at, $ended_at){
                                    $query->where('started_at', '<=', $ended_at)
                                    ->where('ended_at', '>=', $ended_at);
                                })
                                ->count();
                                if($training_histories){
                                    return $fail("The user have entries during this time period");
                                }
                            }
                        }
                    }
                ],
                'ended_at' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        if(!empty($request->started_at) && !empty($request->ended_at)){
                            $started_at = Carbon::parse($request->started_at);
                            $ended_at = Carbon::parse($request->ended_at);
                            if($started_at->gte($ended_at)){
                                return $fail("End date should be greater than start date");
                            }
                        }
                    }
                ],
                'user_duration' => 'required',
                'no_of_trainees' => 'required'
            ], [
                'training_id.required' => 'Please select a training',
                'training_mode_id.required' => 'Please select a training Mode',
                'started_at.required' => 'From Date is required',
                'ended_at.required' => 'To Date is required',
                'user_duration.required' => 'Duration is required',
                'no_of_trainees.required' => 'Number of trainees required'
            ]);
        }
        // Approved duration is equal to user duration initially
        $request->merge(['approved_duration' => $request->user_duration]);
        $training_user = TrainingUser::find($request->training_user_id);
        $training_history = new TrainingHistory();
        $training_history->fill($request->all());
        if(auth()->user()->hasRole('admin')){
            $training_history->user()->associate($request->user_id);
        }
        if(auth()->user()->hasRole('trainer')){
            $training_history->user()->associate(auth()->user()->id);
        }
        $training_history->training_user()->associate($request->training_user_id);
        $training_history->training()->associate($training_user->training_id);
        $training_history->training_type()->associate($request->training_type_id);
        $training_history->training_mode()->associate($request->training_mode_id);
        $training_history->status()->associate(5);
        
        $training_history->save();

        $training_target = TrainingTarget::where('user_id', $training_history->user_id)
        ->where('started_at', '<=', $training_history->started_at)
        ->where('ended_at', '>=', $training_history->started_at)
        ->first();
        if($training_target){
            $training_target->achieved_hour = $training_target->achieved_hour 
            + $training_history->approved_duration;
            $training_target->save();
        }
    
        return ["success" => "Training history created Successfully"];
    }

    public function edit($id)
    {
        if(auth()->user()->hasRole('admin')){
            $users = User::where('is_active', true)
            ->where('company_id', auth()->user()->company->id)
            ->wherehas('roles', function($query){
                $query->where('name', 'trainer');
            })
            ->pluck('username', 'id');            
        }

        if(auth()->user()->hasRole('trainer')){
            $trainings = Training::whereHas('training_users', function($query){
                $query->where('user_id', auth()->user()->id);
            })
            ->pluck('title', 'id');
        }

        $audiences = TrainingAudience::orderBy('name')->pluck('name','id');

        $training_types = TrainingType::orderBy('name')->pluck('name','id');

        $training_modes = TrainingMode::orderBy('name')->pluck('name', 'id');

        $training_history = TrainingHistory::find($id);

        if(auth()->user()->hasRole('admin'))
        {
            $statuses = Status::where('whose','training_history')->pluck('display_name','id');
        }
                
        return view('training-histories.edit', compact('training_history', 'users', 'training_modes', 'trainings','statuses','audiences','training_types'));
    }

    public function update(Request $request, $id)
    {

        //dd($request->status_id);
        if(auth()->user()->hasRole('trainer')) {
            $this->validate($request, [
                'training_mode_id' => 'required',
                'started_at' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request, $id) {
                        if(!empty($request->started_at)
                        && !empty($request->ended_at)){
                            $started_at = Carbon::parse($request->started_at)->addSecond();
                            $ended_at = Carbon::parse($request->ended_at)->subSecond();

                            if(!$started_at->gt($ended_at)){
                                $training_history = TrainingHistory::find($id);
                                $training_histories = TrainingHistory::where('user_id', $training_history->user_id)
                                ->where('id', '!=', $id)
                                ->where(function($query) use ($started_at, $ended_at){

                                $query->whereBetween('started_at', [$started_at, $ended_at])
                                ->orWhereBetween('ended_at', [$started_at, $ended_at])
                                ->orWhere(function($query) use($started_at, $ended_at){
                                    $query->where('started_at', '<=', $started_at)
                                    ->where('ended_at', '>=', $started_at);
                                });
                                })                                     
                                ->count();
                                if($training_histories){
                                    return $fail("You have entries during this time period");
                                }
                            }
                        }
                    }
                ],
                'ended_at' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        if(!empty($request->started_at) && !empty($request->ended_at)){
                            $started_at = Carbon::parse($request->started_at);
                            $ended_at = Carbon::parse($request->ended_at);
                            if($started_at->gte($ended_at)){
                                return $fail("End date should be greater than start date");
                            }
                        }
                    }
                ],
                'user_duration' => 'required'
            ], [
                'training_id.required' => 'Please select a training',
                'training_mode_id.required' => 'Please select a training Mode',
                'started_at.required' => 'From Date is required',
                'ended_at.required' => 'To Date is required',
                'user_duration.required' => 'Duration is required'
            ]);
        }
            
        $training_history = TrainingHistory::find($id);

        $training_target = TrainingTarget::where('user_id', $training_history->user_id)
        ->where('started_at', '<=', $training_history->started_at)
        ->where('ended_at', '>=', $training_history->started_at)
        ->first();

        if($training_target){
            $training_target->achieved_hour = $training_target->achieved_hour 
            - $training_history->approved_duration 
            + $request->approved_duration;
          $training_target->save();   
        }

        

           // Approved duration is equal to user duration initially
        $training_history->approved_duration = $request->user_duration;
        
        

        $training_history->fill($request->except('user_duration'));
     
        if(auth()->user()->hasRole('admin')){

            $training_history->user()->associate($request->user_id);
        }
        if(auth()->user()->hasRole('trainer')){
            $training_history->user_duration = $request->user_duration;
            $training_history->user()->associate(auth()->user()->id);
        }
        $training_history->training_mode()->associate($request->training_mode_id);
        $training_history->training_type()->associate($request->training_type_id);
        if(auth()->user()->hasRole('trainer'))
        {
           $training_history->status()->associate(5); 
        }elseif (auth()->user()->hasRole('admin')) {
            $training_history->status()->associate($request->status_id);
        }    
        

        $training_history->save();

        return ["success" => "Training history updated Successfully"];
    }

    public function delete($id)
    {
        $training_history = TrainingHistory::find($id);
        return view('training-histories.delete', ['training_history' => $training_history]);
    }
    public function destroy($id)
    {
        $user = TrainingHistory::find($id);
        $user->delete();
        return ["success" => "TrainingHistory Deleted Successfully"];
    }

    

}
