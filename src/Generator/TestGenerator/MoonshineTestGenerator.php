<?php

namespace Fillincode\Tests\Generator\TestGenerator;

use Fillincode\Tests\Helpers\MakeValidDataHelper;
use Fillincode\Tests\Generator\BaseGenerator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use MoonShine\Resources\ModelResource;

class MoonshineTestGenerator extends BaseGenerator
{
    protected MakeValidDataHelper $makeValidDataHelper;

    protected ModelResource $resource;

    protected string $resourceName;

    /**
     * Шаблоны для каждого типа теста
     *
     * @var array|string[]
     */
    protected array $stubs = [
        'index' => 'index_test',
        'show' => 'show_test',
        'create' => 'create_test',
        'store' => 'store_test',
        'edit' => 'edit_test',
        'update' => 'update_test',
        'destroy' => 'remove_test',
    ];

    /**
     * Имена классов для каждого теста
     *
     * @var array|string[]
     */
    protected array $classNames = [
        'index' => 'IndexTest',
        'show' => 'DetailTest',
        'create' => 'CreateTest',
        'store' => 'CreateProcessTest',
        'edit' => 'EditTest',
        'update' => 'EditProcessTest',
        'destroy' => 'RemoveTest',
    ];

    /**
     * Имена маршрутов для каждого теста
     *
     * @var array|string[]
     */
    protected array $routeNames = [
        'index' => 'moonshine.resource.page',
        'show' => 'moonshine.resource.page',
        'create' => 'moonshine.resource.page',
        'store' => 'moonshine.crud.store',
        'edit' => 'moonshine.resource.page',
        'update' => 'moonshine.crud.update',
        'destroy' => 'moonshine.crud.destroy',
    ];

    /**
     * Коды ответа для каждого запроса админа
     *
     * @var array|int[]
     */
    protected array $codes = [
        'index' => 200,
        'show' => 200,
        'create' => 200,
        'store' => 302,
        'edit' => 200,
        'update' => 302,
        'destroy' => 302,
    ];

    protected string $type;

    public function setProperty(ModelResource $resource, string $type): static
    {
        $this->resource = $resource;
        $this->resourceName = str(get_class($this->resource))->afterLast('\\');
        $this->type = $type;
        $this->makeValidDataHelper = new MakeValidDataHelper();

        return $this;
    }

    /**
     * @throws FileNotFoundException
     */
    public function generate(): void
    {
        $stubPath = 'moonshine' . $this->ds . $this->stubs[$this->type];

        $stub = $this->stubReplace($this->getSearches(), $this->getReplaces(), $this->getStub($stubPath));

        $this->saveClass($stub);
    }

    protected function getSearches(): array
    {
        return [
            '{{ namespace }}',
            '{{ middlewares }}',
            '{{ default_codes }}',
            '{{ resource_uri }}',
            '{{ default_invalid_param_codes }}',
            ...match ($this->type) {
                'store', 'update' => [
                    '{{ validation_data }}',
                    '{{ invalidation_data }}',
                ],
                'show', 'edit', 'destroy' => [
                    '{{ resource_item }}'
                ],
                default => [],
            },
        ];
    }

    protected function getReplaces(): array
    {
        return [
            $this->makeNamespace(),
            $this->makeMiddlewares(),
            $this->makeDefaultCodes(),
            $this->makeResourceUri(),
            $this->makeDefaultInvalidParamCodes(),
            ...match ($this->type) {
                'store', 'update' => [
                    $this->makeValidationData(),
                    $this->makeInvalidationData(),
                ],
                'show', 'edit', 'destroy' => [
                    "DB::table('{$this->resource->getModel()->getTable()}')->first()->id"
                ],
                default => [],
            },
        ];
    }

    protected function makeNamespace(): string
    {
        return "Tests\\Feature\\{$this->getPrefix('\\')}" . str($this->resourceName)->replace('Resource', '');
    }

    protected function makeResourceUri(): string
    {
        return str($this->resourceName)->snake('-');
    }

    protected function makeMiddlewares(): string
    {
        $result = '';

        foreach (Route::getRoutes()->getByName($this->routeNames[$this->type])->middleware() as $middleware) {
            $result .= "'$middleware', ";
        }

        return rtrim(trim($result), ',');
    }

    protected function makeDefaultInvalidParamCodes(): string
    {
        $result = '';

        foreach (config('fillincode-tests.admin_panel.users') as $user => $guard) {
            if ($user === 'guest') {
                $result .= "'$user' => 401," . $this->character;
                continue;
            }

            $result .= "'$user' => " . config('fillincode-tests.admin_panel.invalid.parameters') . ',' . $this->character;
        }

        return rtrim($result, ",$this->character");
    }

    protected function makeDefaultCodes(): string
    {
        $result = '';
        foreach (config('fillincode-tests.admin_panel.users') as $user => $guard) {
            if ($user === 'guest') {
                $result .= "'$user' => 401," . $this->character;
                continue;
            }

            $result .= "'$user' => {$this->codes[$this->type]}," . $this->character;
        }

        return rtrim($result, ",$this->character");
    }

    protected function makeValidationData(): string
    {
        $result = '';

        foreach ($this->resource->rules($this->resource->getItemOrInstance()) ?? [] as $key => $rules) {
            $result .= $this->makeValidDataHelper->make($key, $rules);
        }

        return rtrim($result, ",$this->character");
    }

    protected function makeInvalidationData(): string
    {
        $result = '';

        foreach ($this->resource->rules($this->resource->getItemOrInstance()) ?? [] as $key => $value) {
            $result .= "'$key' => '',\n\t\t\t";
        }

        return rtrim($result, ",$this->character");
    }

    protected function getPrefix(string $replace): string
    {
        $prefix = config('fillincode-tests.admin_panel.prefix');

        return $prefix ? str($prefix)->lower()->ucfirst() . $replace : '';
    }

    protected function saveClass(string $stub): void
    {
        $path = "tests{$this->ds}Feature$this->ds{$this->getPrefix($this->ds)}"
            . str($this->resourceName)->replace('Resource', '') . $this->ds
            . $this->classNames[$this->type] . '.php';

        if (!File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, true);
        }

        File::put($path, $stub);
    }
}