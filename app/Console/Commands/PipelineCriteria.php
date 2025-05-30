<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PipelineCriteria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pipeline:criteria {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Pipeline criteria';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        $input = $this->extractPathAndClass($name);

        $folder = $input['folderPath'];
        

        $className = ucfirst($input['className']);

        $destination = 'Pipelines/Criterias/';
        $namespace = 'App\Pipelines\Criterias';
        $pathPreview = 'app/Pipelines/Criterias';

        if ($folder) {
            $destination .= "$folder/";

            $folderPath = str_replace('/','\\',$folder);

            $namespace .= "\\$folderPath";

            $pathPreview .= "/$folder";
        }

        $filePath = app_path("$destination/{$className}.php");

        // Check if the file already exists
        if (File::exists($filePath)) {
            $this->error("File {$filePath} already exists!");
            return 1;
        }

        // Define the class template
        $classTemplate = <<<EOT
        <?php

        namespace {$namespace};

        use App\Pipelines\PipelineFactory;
        use Illuminate\Http\Request;

        class {$className} extends PipelineFactory
        {
            private \$request;

            public function __construct(Request \$request = null)
            {
                \$this->request = \$request;
            }

            protected function apply(\$builder)
            {
                return \$builder;
            }
        }
        EOT;

        // Ensure the directory exists
        if (!File::isDirectory(app_path("$destination"))) {
            File::makeDirectory(app_path("$destination"), 0755, true);
        }

        // Create the class file
        File::put($filePath, $classTemplate);

        
        $this->info("Pipeline Criteria class {$className} created successfully at $pathPreview");

        return 0;
    }


    private function extractPathAndClass($input) {
        $lastSlashPos = strrpos($input, '/');
        
        if ($lastSlashPos === false) {
            // No slash found, it's just a class name
            $folderPath = null;
            $className = $input;
        } else {
            // Extract folder path and class name
            $folderPath = substr($input, 0, $lastSlashPos);
            $className = substr($input, $lastSlashPos + 1);
        }
    
        return [
            'folderPath' => $folderPath,
            'className' => $className
        ];
    }
}
