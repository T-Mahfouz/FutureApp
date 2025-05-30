<?php

namespace App\Pipelines\Criterias\Merchants;

use App\Pipelines\PipelineFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdsCriteria extends PipelineFactory
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
        $categoryId = $this->request->category_id;
        $screenPosition = $this->request->screen_position;
        
        $builder = $builder->where('expiration_date','>=', Carbon::now())
            ->where('is_payment_completed', 1)
            ->where(function($sql) {
                $sql->where('merchant_id', $this->user->id)
                    ->orWhere('by_admin', 1);
            });

        if ($this->request->has('category_id')) {
            $builder = $builder->where('category_id', $categoryId);
        }
        if ($this->request->has('screen_position')) {
            $builder = $builder->where('screen_position', $screenPosition);
        }

        return $builder;
    }
}