<?php

declare(strict_types=1);

namespace Asseco\OpenApi\App\Console\Commands;

use Asseco\OpenApi\Exceptions\OpenApiException;
use Asseco\OpenApi\SchemaGenerator;
use Illuminate\Console\Command;
use ReflectionException;
use Symfony\Component\Yaml\Yaml;

class OpenApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asseco:open-api 
                                    {--b|bust-cache : Override using cache, will re-cache with new values}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OpenApi yaml file from app routes.';

    protected SchemaGenerator $generator;

    public function __construct(SchemaGenerator $generator)
    {
        parent::__construct();

        $this->generator = $generator;
    }

    /**
     * @return int
     * @throws ReflectionException
     * @throws OpenApiException
     */
    public function handle()
    {
        config()->set('asseco-open-api.bust_cache', $this->option('bust-cache'));
        config()->set('asseco-open-api.verbose', $this->option('verbose'));

        $documentation = $this->generator->generate($this->output);

        $yaml = Yaml::dump($documentation, 10);

        $fileName = config('asseco-open-api.file_name');

        $file = fopen($fileName, 'w') or exit('Unable to open file!');
        fwrite($file, $yaml);
        fclose($file);

        $this->newLine(2);
        $this->info('YML file generated successfully!');

        return 0;
    }
}
