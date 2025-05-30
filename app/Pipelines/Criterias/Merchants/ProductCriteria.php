<?php

namespace App\Pipelines\Criterias\Merchants;

use App\Pipelines\PipelineFactory;
use Illuminate\Http\Request;

class ProductCriteria extends PipelineFactory
{
    private $request;
    private $user;
    public function __construct(Request $request = null)
    {
        $this->request = $request;
        $this->user = getUser(guard: 'merchant');
    }

    protected function apply($builder)
    {
        $merchant = getUser(guard: 'merchant');

        $keyword = $this->request->keyword;
        $categoryId = $this->request->category_id;
        $sectionId = $this->request->section_id;
        
        $builder = $builder->where('merchant_id', $merchant->id);
        
        if ($categoryId) {
            $builder = $builder->where('category_id', $categoryId);
        }
        if ($sectionId) {
            $builder = $builder->where('section_id', $sectionId);
        }

        // if ($keyword) {
        //     // ->where('name', 'LIKE', '%'.$this->request->name.'%');
        //     $builder = $builder->where('name', 'LIKE', "%$keyword%");
        // }
        return $builder;
    }
}
