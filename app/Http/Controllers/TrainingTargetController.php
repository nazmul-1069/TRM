<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\TrainingTarget;
use App\User;
use Carbon\Carbon;

class TrainingTargetController extends Controller
{
    public function index()
    {
        return view('training-targets.index');
    }

    public function indexData(Datatables $datatables)
    {
        $query = TrainingTarget::with('user')->select('training_targets.*');
        return $datatables->eloquent($query)
          ->addColumn('user_name', function ($training_target) {
              return $training_target->user->name;
          })
          ->addColumn('action', function ($training_target) {
              return view('training-targets.actions', ['training_target' => $training_target]);
          })
          ->make(true);
    }

    public function status()
    {
        if(auth()->user()->hasRole('trainer'))
        {
            $targets = TrainingTarget::where('user_id','=',auth()->user()->id)->get();
        }else{
             $targets = TrainingTarget::get();     
        }
        
        return view('training-targets.status',compact('targets'));
    }

    public function create(Request $request)
    {
        $users = User::where('is_active', true)
        ->where('company_id', auth()->user()->company->id)
        ->wherehas('roles', function($query){
            $query->where('name', 'trainer');
        })
        ->pluck('username', 'id');
        return view('training-targets.create', compact('users'));
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'started_at' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if(!empty($request->started_at) && !empty($request->ended_at)){
                        $started_at = Carbon::parse($request->started_at);
                        $ended_at = Carbon::parse($request->ended_at);
                        if(!$started_at->gt($ended_at)){
                            $current_targets_count = TrainingTarget::where('user_id', $request->user_id)
                            ->whereBetween('started_at', [$request->started_at, $request->ended_at])
                            ->orWhereBetween('ended_at', [$request->started_at, $request->ended_at])
                            ->count();
                            if($current_targets_count){
                                return $fail("The target period overlaps with previously set targets");
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
                        if($started_at->gt($ended_at)){
                            return $fail("End date should be greater than start date");
                        }
                    }
                }
            ],
            'target_hour' => 'required'
        ], [
            'user_id.required' => 'User is required',
            'started_at.required' => 'From Date is required',
            'ended_at.required' => 'To Date is required',
            'target_hour.required' => 'Target hour is required'
        ]);
        $training_target = new TrainingTarget();
        $training_target->fill($request->all());
        $training_target->user()->associate($request->user_id);
        $training_target->save();
        return ["success" => "Training target created Successfully"];
    }

    public function edit($id)
    {
        $users = User::where('is_active', true)
        ->where('company_id', auth()->user()->company->id)
        ->wherehas('roles', function($query){
            $query->where('name', 'trainer');
        })
        ->pluck('username', 'id');
        $training_target = TrainingTarget::find($id);
        return view('training-targets.edit', ['training_target' => $training_target, 'users' => $users]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'started_at' => 'required',
            'ended_at' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if(!empty($request->started_at) && !empty($request->ended_at)){
                        $started_at = Carbon::parse($request->started_at);
                        $ended_at = Carbon::parse($request->ended_at);
                        if($started_at->gt($ended_at)){
                            return $fail("End date should be greater than start date");
                        }
                    }
                }
            ],
            'target_hour' => 'required'
        ], [
            'user_id.required' => 'User is required',
            'started_at.required' => 'From Date is required',
            'ended_at.required' => 'To Date is required',
            'target_hour.required' => 'Target hour is required'
        ]);
        $training_target = TrainingTarget::find($id);
        $training_target->fill($request->all());
        $training_target->user()->associate($request->user_id);
        $training_target->save();
        return ["success" => "Training target updated Successfully"];
    }

    public function delete($id)
    {
        $training_target = TrainingTarget::find($id);
        return view('training-targets.delete', ['training_target' => $training_target]);
    }
    public function destroy($id)
    {
        $user = TrainingTarget::find($id);
        $user->delete();
        return ["success" => "TrainingTarget Deleted Successfully"];
    }
}
