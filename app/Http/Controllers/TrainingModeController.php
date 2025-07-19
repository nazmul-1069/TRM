<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\TrainingMode;

class TrainingModeController extends Controller
{
    public function index()
    {
        return view('training-modes.index');
    }

    public function indexData(Datatables $datatables)
    {
        $query = TrainingMode::query();
        return $datatables->eloquent($query)
          ->addColumn('action', function ($training_mode) {
              return view('training-modes.actions', ['training_mode' => $training_mode]);
          })
          ->make(true);
    }
    public function create(Request $request)
    {
        return view('training-modes.create');
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $trainingMode = TrainingMode::create($request->all());

        return ["success" => "Training Mode created Successfully"];
    }

    public function edit($id)
    {
        $training_mode = TrainingMode::find($id);
        return view('training-modes.edit', ['training_mode' => $training_mode]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $training_mode = TrainingMode::find($id);
        $training_mode->update($request->all());
        return ["success" => "Training Mode updated Successfully"];
    }

    public function delete($id)
    {
        $training_mode = TrainingMode::find($id);
        return view('training-modes.delete', ['training_mode' => $training_mode]);
    }
    public function destroy($id)
    {
        $user = TrainingMode::find($id);
        $user->delete();
        return ["success" => "TrainingMode Deleted Successfully"];
    }
}
