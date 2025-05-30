<?php

namespace App\Pipelines\Criterias;

use App\Pipelines\PipelineFactory;
use Illuminate\Http\Request;

class SortPipeline extends PipelineFactory
{
    private $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    protected function apply($builder)
    {
        $sort_by = 'id';
        $sort_type = 'Desc';

        if ($this->request && $this->request->filled('sortby') && $this->request->get('sortby') != 'undefined') {
            $sort_by = $this->request->get('sortby');
        }
        if ($this->request && $this->request->filled('sorttype') && $this->request->get('sorttype') != 'undefined') {
            $sort_type = $this->request->get('sorttype');
        }

        return $builder->orderBy($sort_by, $sort_type);
    }
}
