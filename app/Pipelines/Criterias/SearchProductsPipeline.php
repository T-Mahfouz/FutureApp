<?php

namespace App\Pipelines\Criterias;

use App\Pipelines\PipelineFactory;
use Illuminate\Http\Request;

class SearchProductsPipeline extends PipelineFactory
{
    private $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    protected function apply($builder)
    {
        $keyword = $this->request->keyword;
        $categoryId = $this->request->category_id;
        
        if ($keyword) {
            // ->where('name', 'LIKE', '%'.$this->request->name.'%');
            $builder = $builder->where('name', 'LIKE', "%$keyword%");
        }
        if ($categoryId) {
            $builder = $builder->where('category_id', $categoryId);
        }
        return $builder;
    }
}
