<?php

namespace App\Http\Controllers\API;

use App\Models\Country;
use App\Pipelines\Pipeline;
use App\Http\Controllers\Controller;
use GeoIP;
use Auth;

class InitController extends Controller
{
    protected $pipeline;
    protected $countryId;
    protected $user;
    
    public function __construct($guard = 'api')
    {
        $this->pipeline = new Pipeline();
    }
}
