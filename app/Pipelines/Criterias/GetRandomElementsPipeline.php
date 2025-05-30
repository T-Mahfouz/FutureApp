<?php

namespace App\Pipelines\Criterias;

use App\Pipelines\PipelineFactory;
use Illuminate\Http\Request;

class GetRandomElementsPipeline extends PipelineFactory
{
    private $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    protected function apply($builder)
    {
        $limit = ($this->request && $this->request->filled('limit')) ? $this->request->limit : 10;
        return $builder->inRandomOrder()->limit($limit);
    }
}
