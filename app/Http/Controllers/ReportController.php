<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TrainingHistory;
use App\TrainingTarget;
use Yajra\Datatables\Datatables;

class ReportController extends Controller
{
    public function topicWiseReprot()
    {
        return view('reports.topic-wise-reprot');
    }

    public function topicWiseReprotData(Datatables $datatables)
    {
        $query = TrainingHistory::with('training')
        ->with('audience')
        ->with('user')
        ->with('status')
        ->select('training_histories.*');
        if(auth()->user()->hasRole('trainer')){
            $query = $query->where('user_id', auth()->user()->id);
        }
        $request  = $datatables->getRequest();
        if($request){
          $query = $query->whereBetween('training_histories.updated_at', [$request->start_date, $request->end_date]);
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
          });
          
        return $datatables->make(true);
    }

    public function targetAchievementWiseReport()
    {
      return view('reports.target-achievement-reprot');
    }

    public function  targetAchievementWiseReportData(Datatables $datatables)
    {
        $query = TrainingTarget::with('user')->select('training_targets.*');

        $request  = $datatables->getRequest();
        if($request){
          $query = $query->whereBetween('training_targets.updated_at', [$request->start_date, $request->end_date]);
        }

        // if($request->usergroup){
        //   $query = $query->usergroup;
        // }

        return $datatables->eloquent($query)
          ->addColumn('user_name', function ($training_target) {
              return $training_target->user->name;
          })

          ->addColumn('remaining', function ($training_target) {
              return $training_target->target_hour - $training_target->achieved_hour;
          })          
          ->make(true);
    }
}
