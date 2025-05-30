<?php

namespace App\Pipelines\Criterias\Users;

use App\Pipelines\PipelineFactory;
use Illuminate\Http\Request;

class ProductSearchCriteria extends PipelineFactory
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function apply($builder)
    {
        $categoryId = $this->request->category_id;
        $sectionId = $this->request->section_id;
        $merchantId = $this->request->merchant_id;
        $productId = $this->request->product_id;

        $keyword = $this->request->get('keyword');
        
        if ($this->request->has('keyword')) {
            $builder = $builder->where(function($query) use ($keyword) {
                $query->where('merchant_products.name', 'LIKE', "%$keyword%")
                    ->orWhere('merchant_products.description', 'LIKE', "%$keyword%");
            });
        }

        if ($categoryId) {
            $builder = $builder->where('category_id', $categoryId);
        }
        if ($sectionId) {
            $builder = $builder->where('section_id', $sectionId);
        }
        if ($merchantId) {
            $builder = $builder->where('merchant_id', $merchantId);
        }
        if ($productId) {
            $builder = $builder->where('product_id', $productId);
        }
        
        $builder = $builder->where('visibility_status', true);

        $builder = $builder->where('in_stock_count','>=', 1);
        
        return $builder;
    }
}