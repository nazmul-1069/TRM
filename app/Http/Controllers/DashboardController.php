<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Content;
use App\TrainingTarget;
use App\TrainingUser;

class DashboardController extends Controller
{
  public function index(){
    if(auth()->user()->hasRole('admin')){
      return view('dashboard.admin');
    }
    $targets = TrainingTarget::where('user_id','=',auth()->user()->id)->get();  
    $targets = $targets->map(function($target){
    	$target->percentage = $target->achieved_hour / $target->target_hour * 100;
    	return $target;
    });
    
    $top_achievements = TrainingTarget::orderBy('achieved_hour', 'DESC')->take(10)->get();
    
      
    return view('dashboard.trainer',compact('targets','top_achievements'));
  }
}
