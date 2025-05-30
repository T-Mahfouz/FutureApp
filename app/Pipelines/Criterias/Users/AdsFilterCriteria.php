<?php

namespace App\Pipelines\Criterias\Users;

use App\Pipelines\PipelineFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdsFilterCriteria extends PipelineFactory
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function apply($builder)
    {
        $position = $this->request->position ?? 'home';
        $categoryId = $this->request->category_id;
        $merchantId = $this->request->merchant_id;

        // TODO: Get banners and up levels as below:
            // (profile => (home, category, profile), category => (home, category), home => (home only)) 
        $builder
            ->whereIn('screen_position', ['all', $position])
            ->where('is_payment_completed', 1)
            ->whereDate('expiration_date','>=',Carbon::now());
        
        if ($categoryId && $position == 'category') {
            $builder = $builder->where('category_id', $categoryId);
        }
        if ($merchantId && $position == 'profile') {
            $builder = $builder->where('merchant_id', $merchantId);
        }

        return $builder;
    }
}