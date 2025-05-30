<?php

namespace App\Pipelines\Criterias\Users;

use App\Pipelines\PipelineFactory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MerchantFilterCriteria extends PipelineFactory
{
    private $request;
    private $distance;

    public function __construct(Request $request, $distance = 5)
    {
        $this->request = $request;
        $this->distance = $distance;
    }

    protected function apply($builder)
    {
        $categoryId = $this->request->category_id;
        $countryId = $this->request->country_id;
        $cityId = $this->request->city_id;
        $areaId = $this->request->area_id;

        $lat = $this->request->lat;
        $lon = $this->request->lon;

        $keyword = $this->request->get('keyword');
        
        if ($this->request->has('lat') && $this->request->has('lon')) {
            $builder = $builder->withDistance($lat, $lon);
        }
        
        $builder = $builder->join('user_settings', function ($join) {
                $join->on('user_settings.model_id', '=', 'merchants.id')
                    ->where('user_settings.model_name', '=', 'Merchant');
            })
            ->whereDate('package_expiration_date', '>', Carbon::now())
            ->where('user_settings.verified', 1);
        
        if ($this->request->has('keyword')) {
            $builder = $builder->where(function($query) use ($keyword) {
                $query->where('merchants.name', 'LIKE', "%$keyword%")
                    ->orWhere('merchants.phone', 'LIKE', "%$keyword%")
                    ->orWhere('merchants.email', 'LIKE', "%$keyword%");
            });
        }

        if ($categoryId) {
            $builder = $builder->where('category_id', $categoryId);
        }
        if ($countryId) {
            $builder = $builder->where('country_id', $countryId);
        }
        if ($cityId) {
            $builder = $builder->where('city_id', $cityId);
        }
        if ($areaId) {
            $builder = $builder->where('area_id', $areaId);
        }

        if ($this->request->has('lat') && $this->request->has('lon')) {
            $builder = $builder->having('distance', '<=', $this->distance)
                ->orderBy('distance', 'ASC');
        }
        

        return $builder;
    }
}