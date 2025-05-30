<?php

namespace App\Pipelines\Criterias;

use App\Pipelines\PipelineFactory;
use Illuminate\Http\Request;

class SearchVendorsPipeline extends PipelineFactory
{
    private $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    protected function apply($builder)
    {
        $keyword = $this->request->get('keyword');

        if ($this->request->filled('keyword')) {
            $builder = $builder->where('name', 'LIKE' , "%$keyword%")
                ->orWhere('bio', 'LIKE' , "%$keyword%")
                ->orWhere('phone', 'LIKE' , "%$keyword%")
                ->orWhere('whatsapp', 'LIKE' , "%$keyword%");
        }
        
        return $builder;
    }
}
