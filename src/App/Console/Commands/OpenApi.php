<?php

declare(strict_types=1);

namespace Voice\OpenApi\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use ReflectionException;
use Symfony\Component\Yaml\Yaml;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\SchemaGenerator;

class OpenApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voice:open-api 
                                    {--b|bust-cache : Override using cache, will re-cache with new values}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OpenApi yaml file from app routes.';

    /**
     * @return int
     * @throws ReflectionException
     * @throws OpenApiException
     */
    public function handle()
    {
        Config::set('asseco-open-api.bust_cache', $this->option('bust-cache'));
        Config::set('asseco-open-api.verbose', $this->option('verbose'));

        /**
         * @var $generator SchemaGenerator
         */
        $generator = App::make(SchemaGenerator::class);
        $documentation = $generator->generate();

        $yaml = Yaml::dump($documentation, 10);

        $fileName = Config::get('asseco-open-api.file_name');

        $file = fopen($fileName, 'w') or exit('Unable to open file!');
        fwrite($file, $yaml);
        fclose($file);

        $this->info('YML file generated successfully!');

        return 0;
    }
}
