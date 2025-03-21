<?php
namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;

class DatagenerateController extends Controller
{
    /**
     * Generate data view
     * @return mixed
     */
    public function datagenerate(){
        return view('pages.datagenerate');
    }
}
