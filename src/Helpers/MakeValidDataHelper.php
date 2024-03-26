<?php

namespace Fillincode\Tests\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MakeValidDataHelper
{
    protected string $key;

    protected array|string $rules;

    protected string $type = 'undefined';

    protected array $in = [];

    protected array $exists = [
        'table' => null,
        'key' => null,
    ];

    protected int $min = 1;

    protected int $max = 255;

    public function make(string $key, array|string $rules): string
    {
        $this->setProperties($key, $rules);

        $this->determineType();

        $this->setIn();

        $this->setExists();

        $this->setRange();

        if ($this->type === 'password') {
            $pass = $this->makeValue();

            return "'password' => $pass,\n\t\t\t 'password_repeat' => $pass,\n\t\t\t";
        }

        return "'$key' => {$this->makeValue()},\n\t\t\t";
    }

    protected function setProperties(string $key, array|string $rules): void
    {
        $this->in = [];
        $this->exists = [
            'table' => null,
            'key' => null,
        ];
        $this->min = 1;
        $this->max = 255;
        $this->type = 'undefined';
        $this->key = $key;
        $this->rules = is_string($rules) ? explode('|', $rules) : $rules;
    }

    protected function determineType(): void
    {
        if ($this->isNullable()) {
            $this->type = 'nullable';
            return;
        }

        if ($this->isTableExists()) {
            $this->type = 'table_exists';
            return;
        }

        if ($this->isEnum()) {
            $this->type = 'enum';
            return;
        }

        if ($this->isEmail()) {
            $this->type = 'email';
            return;
        }

        if ($this->key === 'password') {
            $this->type = 'password';
            return;
        }

        if ($this->isInteger()) {
            $this->type = 'integer';
            return;
        }

        if ($this->isString()) {
            $this->type = 'string';
            return;
        }

        if ($this->isBoolean()) {
            $this->type = 'boolean';
            return;
        }

        if ($this->isFile()) {
            $this->type = 'file';
            return;
        }

        if ($this->isImage()) {
            $this->type = 'image';
            return;
        }

        if ($this->isArray()) {
            $this->type = 'array';
            return;
        }

        if ($this->isDate()) {
            $this->type = 'date';
        }
    }

    protected function setIn(): void
    {
        $ruleIn = null;

        foreach ($this->rules as $rule) {
            if (str($rule)->contains('in:')) {
                $ruleIn = $rule;
                break;
            }
        }

        if ($ruleIn) {
            $this->in = str($ruleIn)->after(':')->explode(',')->toArray();
        }
    }

    protected function setExists(): void
    {
        $ruleExists = null;

        foreach ($this->rules as $rule) {
            if (str($rule)->contains('exists:')) {
                $ruleExists = str($rule)->after('exists:')->explode(',')->toArray();
                break;
            }
        }

        if ($ruleExists) {
            $this->exists = [
                'table' => $ruleExists[0],
                'key' => $ruleExists[1] ?? $this->key,
            ];
        }
    }

    protected function setRange(): void
    {
        foreach ($this->rules as $rule) {
            if (str($rule)->contains('min:')) {
                $this->min = (int) str($rule)->after('min:')->value();
            }

            if (str($rule)->contains('max:')) {
                $this->max = (int) str($rule)->after('max:')->value();

                if ($this->max > 750) {
                    $this->max = 750;
                }
            }
        }
    }

    protected function makeValue(): mixed
    {
        return match ($this->type) {
            'table_exists' => DB::table($this->exists['table'])->select($this->exists['key'])->first()->{$this->exists['key']},
            'enum' => '\'' . trim(collect($this->in)->random(), "\"") . '\'',
            'email' => '\'' . fake()->email() . '\'',
            'string' => '"' . str(fake()->realTextBetween($this->min, $this->max))->replace('"', '\'') . '"',
            'integer' => fake()->numberBetween($this->min, $this->max),
            'boolean' => fake()->numberBetween(0,1),
            'file' => UploadedFile::fake()->create('file.docs'),
            'image' => UploadedFile::fake()->image('image.png'),
            'date' => '\'' . now()->subYears(5)->format('Y-m-d') . '\'',
            'array' => '[]',
            'undefined' => str($this->key)->contains('id') ? 1 : '\'' . Str::random(12) . '\'',
            'password' =>  '\'' . Str::password() . '\'',
            default => '\'\'',
        };
    }

    protected function isNullable(): bool
    {
        return (!in_array('required', $this->rules) && !in_array('sometimes', $this->rules)) || in_array('nullable', $this->rules);
    }

    protected function isArray(): bool
    {
        return in_array('array', $this->rules);
    }

    protected function isTableExists(): bool
    {
        foreach ($this->rules as $rule) {
            if (str($rule)->contains('exists:')) {
                return true;
            }
        }

        return false;
    }

    protected function isEnum(): bool
    {
        foreach ($this->rules as $rule) {
            if (str_starts_with($rule, 'in:')) {
                return true;
            }
        }

        return false;
    }

    protected function isInteger(): bool
    {
        return in_array('integer', $this->rules);
    }

    protected function isString(): bool
    {
        return in_array('string', $this->rules);
    }

    protected function isBoolean(): bool
    {
        return in_array('boolean', $this->rules) || in_array('bool', $this->rules);
    }

    protected function isEmail(): bool
    {
        return in_array('email', $this->rules);
    }

    protected function isFile(): bool
    {
        return in_array('file', $this->rules);
    }

    protected function isImage(): bool
    {
        return in_array('image', $this->rules);
    }

    protected function isDate(): bool
    {
        return in_array('date', $this->rules);
    }
}