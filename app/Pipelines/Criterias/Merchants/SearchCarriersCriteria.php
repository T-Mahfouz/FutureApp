<?php

namespace App\Pipelines\Criterias\Merchants;

use App\Pipelines\PipelineFactory;
use Illuminate\Http\Request;

class SearchCarriersCriteria extends PipelineFactory
{
    private $request;
    private $mine;

    public function __construct(Request $request = null, $onlyMine = false)
    {
        $this->request = $request;
        $this->mine = $onlyMine;
    }

    protected function apply($builder)
    {
        $keyword = $this->request->get('keyword');
        
        $selectedData = ['carriers.*', 'user_settings.verified'];
        if ($this->mine) {
            $selectedData = [
                'carriers.*', 'user_settings.verified',
                'merchant_carriers.id as assignmentID', 'merchant_carriers.merchant_id', 'merchant_carriers.carrier_id',
                'merchant_carriers.start_working_time', 'merchant_carriers.end_working_time'
            ];
        }

        $builder = $builder->select($selectedData)->join('user_settings', function ($join) {
            $join->on('user_settings.model_id', '=', 'carriers.id')
                ->where('user_settings.model_name', '=', 'Carrier');
        })
        ->where('user_settings.verified', 1);
        
        if ($this->mine) {
            // $builder = $builder->carriers();
            $builder = $builder->join('merchant_carriers', 'carriers.id', '=', 'merchant_carriers.carrier_id')
                ->where('merchant_carriers.merchant_id', getUser(guard: 'merchant')->id);
        }

        if ($this->request->has('keyword')) {
            $builder = $builder->where(function($query) use ($keyword) {
                $query->where('carriers.name', 'LIKE', "%$keyword%")
                    ->orWhere('carriers.phone', 'LIKE', "%$keyword%")
                    ->orWhere('carriers.email', 'LIKE', "%$keyword%");
            });
        }

        return $builder;
    }
}