<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use App\TrainingUser;
use App\Training;
use App\User;
use App\Status;
use DB;
class TrainingUserController extends Controller
{
    public function index()
    {
        return view('training-users.index');
    }

    public function indexData(Datatables $datatables){
        $request = $datatables->getRequest();
        
        $query = TrainingUser::with('user')
        ->with('training')
        ->with('status')
        
        ->whereHas('user', function($query){
            $query->where('company_id', auth()->user()->company_id);
        })       
        ->select('training_user.*');
       
          
        if($request->start_date){
          $query = $query->whereBetween('training_user.updated_at', [
              Carbon::parse($request->start_date)->startOfDay()->toDateTimeString(),
              Carbon::parse($request->end_date)->endOfDay()->toDateTimeString(),
          ]);
        }
        if(auth()->user()->hasRole('trainer')){
            $query = $query->where('user_id', auth()->user()->id);
        }
        $datatable =  $datatables->eloquent($query)
        ->addColumn('action', function ($training_user) {
              return view('training-users.actions', ['training_user' => $training_user]);
          })
        ->addColumn('training_title', function ($training_user) {
              return $training_user->training->title;
          })
        ->addColumn('username', function ($training_user) {
              return $training_user->user->username;
          })

        ->addColumn('status', function ($training_user) {
              return $training_user->status->name;
          })

        ->rawColumns(['name', 'action'])
        ->make(true);
        return $datatable;
    }

    public function indexPerUser(Request $request)
    {
        return view('training-users.index-per-user');
    }

    public function indexDataPerUser(Datatables $datatables)
    {
        $request = $datatables->getRequest();
        $query = TrainingUser::with('user')
        ->with('training')
        ->with('status')
        ->whereHas('user', function($query){
            $query->where('company_id', auth()->user()->company_id);
        })
        ->select('training_user.*');
          
        if($request->start_date){
          $query = $query->whereBetween('training_user.updated_at', [
              Carbon::parse($request->start_date)->startOfDay()->toDateTimeString(),
              Carbon::parse($request->end_date)->endOfDay()->toDateTimeString(),
          ]);
        }
        if(auth()->user()->hasRole('trainer')){
            $query = $query->where('user_id', auth()->user()->id);
        }
        $datatable =  $datatables->eloquent($query)
        ->addColumn('action', function ($training_user) {
              return view('training-users.actions', ['training_user' => $training_user]);
          })
        ->addColumn('training_title', function ($training_user) {
              return $training_user->training->title;
          })
        ->addColumn('username', function ($training_user) {
              return $training_user->user->username;
          })

        ->addColumn('status', function ($training_user) {
              return $training_user->status->name;
          })

        ->rawColumns(['name', 'action'])
        ->make(true);
        return $datatable;
    }

    public function create(Request $request)
    {
        $company_id = auth()->user()->company->id;
        $trainings = Training::where('status_id', 1)->pluck('title', 'id');
        
        $users = User::whereHas('roles', function($query){
            $query->where('name', '=', 'trainer');
        })
        ->where('company_id', $company_id)
        ->where('is_active', true)
        ->pluck('name', 'id');

        if($request->training_id){
            $training = Training::find($request->training_id);
        }

        return view('training-users.create', compact('trainings', 'users', 'training'));

    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'started_at'=> 'required',
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
            'training_id' => 'required',
            'user_ids' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if(!empty($request->started_at)
                    && !empty($request->ended_at)
                    && !empty($request->user_ids)
                    && !empty($request->training_id)){
                        $started_at = Carbon::parse($request->started_at);
                        $ended_at = Carbon::parse($request->ended_at);
                        if(!$started_at->gt($ended_at)){
                            $busy_user = TrainingUser::whereIn('user_id', $request->user_ids)
                            ->where(function($query) use($started_at, $ended_at){
                                $query->whereBetween('started_at', [$started_at, $ended_at])
                                ->orWhereBetween('ended_at', [$started_at, $ended_at]);
                            })
                            ->first();
                            if(!empty($busy_user)){
                                return $fail("The user " . $busy_user->user->name . " is busy during this period");
                            }
                        }
                    }
                }
            ]
        ],[
            'started_at.required' => 'Start Date is Required',
            'ended_at.required' => 'End Date is Required',
            'user_ids.required' => 'Select at least a user',
            'training_id.required' => 'Please select a Training'
        ]);
        foreach($request->user_ids as $user_id){
            $training_user = new TrainingUser();
            $training_user->fill($request->all());
            $training_user->user()->associate($user_id);
            $training_user->training()->associate($request->training_id);
            $training_user->status()->associate(3);
            $training_user->save();
        }
        return ['success' => 'Training Assigned Successfully'];
    }
    public function edit($id)
    {
        
        $training_user = TrainingUser::find($id);
        //dd($training_user->training_id);
        // $training_ids = TrainingUser::select('training_id')->where('training_id',$training_user->training_id)->get();
        // foreach($training_ids as $training_id)
        // {
        //     dd($training_id->training_id);
        // }
        $users = User::whereHas('roles', function($query){
            $query->where('name', '=', 'trainer');
        })
        ->where('company_id', auth()->user()->company_id)
        ->where('is_active', true)
        ->pluck('name', 'id');
        
        if(auth()->user()->hasRole('trainer'))
        {    
            $statuses = Status::where('whose', 'training_user')
            ->where('name', '!=', 'close' )
            ->orderBy('display_order')
            ->pluck('display_name', 'id');
        }
        elseif(auth()->user()->hasRole('admin'))
        {
            $statuses = Status::where('whose', 'training_user')
            ->orderBy('display_order')
            ->pluck('display_name', 'id');   
        }
        return view('training-users.edit', [
            'training_user' => $training_user,
            'users' => $users,
            'statuses' => $statuses
        ]);
    }

    public function update(Request $request, $id)
    {
        if(auth()->user()->hasRole('admin')) {
            $this->validate($request, [
                'started_at' => 'required',
                'ended_at' => 'required',
                'user_id' => 'required',
                'status_id' => 'required',
            ]);
        }else if(auth()->user()->hasRole('trainer')){
            $this->validate($request, [
                'status_id' => 'required',
            ]);
        }
        if($request->status_id == 3){
            $request->merge(['completed_at' => null]);
        }else if($request->status_id == 4){
            $request->merge(['completed_at' => Carbon::now()]);
        }
        
        
        // foreach($request->user_id as $user_id)
        // {
        //     $training_user = new TrainingUser();
            
        //     $training_user->where('user_id',$user_id)->update($request->all());
        //     $training_user->user()->associate($user_id);
        //     $training_user->training()->associate($request->training_id);
        //     $training_user->status()->associate(3);
        //     $training_user->save();
        // }
        $training_user = TrainingUser::find($id);
        //dd($training_user);
        $training_user->update($request->all());
        $training_user->status()->associate($request->status_id);
        $training_user->save();
        return ["success" => "Training Assignment updated Successfully"];
    }

    public function delete($id)
    {
        $training_user = TrainingUser::find($id);
        return view('training-users.delete', ['training_user' => $training_user]);
    }
    public function destroy($id)
    {
        $training_user = TrainingUser::find($id);
        $training_user->delete();
        return ["success" => "The assignement Deleted Successfully"];
    }
}
