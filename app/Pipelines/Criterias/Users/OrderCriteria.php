<?php

namespace App\Pipelines\Criterias\Users;

use App\Pipelines\PipelineFactory;
use Illuminate\Http\Request;

class OrderCriteria extends PipelineFactory
{
    private $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    protected function apply($builder)
    {
        // We've two scenarios: 
        // 1- getting the whole order then display all sub orders 
        // 2- getting sub orders as a single order for each (we'll go with this)
        $status = $this->request->status;

        // $builder->whereHas('sub_orders', function ($query) use ($status) {
        //     $query->where('status', $status);
        // })->with(['sub_orders.items', 'items'])->get();

        $builder->where('user_id', getUser()->id);

        if ($this->request->has('status')) {
            $builder = $builder->where('status', $status);
        }

        return $builder;
    }
}