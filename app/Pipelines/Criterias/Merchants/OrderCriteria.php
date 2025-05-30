<?php

namespace App\Pipelines\Criterias\Merchants;

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
        $status = $this->request->status;

        $builder->where('merchant_id', getUser(guard: 'merchant')->id);

        if ($this->request->has('status')) {
            $builder = $builder->where('status', $status);
        }

        return $builder;
    }
}