<?php

namespace App\Pipelines\Criterias\Merchants;

use App\Pipelines\PipelineFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PackagesCriteria extends PipelineFactory
{
    private $request;
    private $countryId;

    public function __construct($countryId, Request $request = null)
    {
        $this->request = $request;
        $this->countryId = $countryId;
    }

    protected function apply($builder)
    {
        $builder = $builder->where('country_id', $this->countryId);

        return $builder;
    }
}