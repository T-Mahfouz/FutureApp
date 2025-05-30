<?php

namespace App\Pipelines;
use \Illuminate\Pipeline\Pipeline as Pipe;

class Pipeline
{
    private $namespace = 'App\\Models\\';

    private $model;
    private $pipelines = [];

    public function setModel($model)
    {
        $class = $this->namespace . $model;
        $this->model = new $class();

        $this->resetPipelines();

        return $this;
    }

    public function pushPipeline($pipelines)
    {
        if(is_array($pipelines)) {
            foreach($pipelines as $pipeline) {
                if ($pipeline instanceof PipelineFactory) {
                    array_push($this->pipelines, $pipeline);
                }
            }
        } else {
            if ($pipelines instanceof PipelineFactory) {
                array_push($this->pipelines, $pipelines);
            }
        }
        return $this;
    }

    public function resetPipelines()
    {
        $this->pipelines = [];
    }

    public function then()
    {
        return app(Pipe::class)
            ->send($this->model->query())
            ->through($this->pipelines)
            ->thenReturn();
    }

    public function __call(string $name, array $arguments)
    {
        $result = $this->then()
            ->$name(...$arguments);
        
        $this->resetPipelines();
        return $result;
    }

}
