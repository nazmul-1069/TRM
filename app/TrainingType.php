<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;

class TrainingType extends Model
{
    use Userstamps;
    protected $fillable = ['name'];
}
