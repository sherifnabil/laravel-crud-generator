<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class CrudGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:crud {name : Class (singular), e.g Post}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a crud operations controller, model and request';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        $pluralName = strtolower(Str::plural($name));

        $this->model($name);
        $this->request($name);
        $this->controller($name);

        File::append(base_path('routes/api.php'), "Route::resource('{$pluralName}', '{$name}Controller');");
        Artisan::call('make:migration create_' . $pluralName . '_table --create=' . $pluralName);
    }

    protected function getStub($type)
    {   
        return file_get_contents(resource_path("stubs/{$type}.stub"));
    }

    protected function model($name)
    {
        $template = str_replace(['{{modelName}}'], [$name], $this->getStub('Model'));
        file_put_contents(app_path("/{$name}.php"), $template);
    }
  
    protected function request($name)
    {
        if (!file_exists($path = app_path('/Http/Requests'))) {
            mkdir($path, 0777, true);
        }
        $template = str_replace(['{{modelName}}'], [$name], $this->getStub('Request'));
        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $template);
    }

    protected function controller($name)
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
            ], 
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
            ], 
            $this->getStub('Controller')
        );
        file_put_contents(app_path("/Http/Controllers/{$name}Controller.php"), $template);
    }
}
