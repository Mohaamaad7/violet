<?php

namespace App\Translation;

use App\Models\Translation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\LoaderInterface;

class CombinedLoader implements LoaderInterface
{
    protected FileLoader $fileLoader;

    public function __construct(Filesystem $files, string $path)
    {
        $this->fileLoader = new FileLoader($files, $path);
    }

    public function load($locale, $group, $namespace = null)
    {
        // Load from files first
        $lines = $this->fileLoader->load($locale, $group, $namespace);

        // Then overlay DB translations (DB wins)
        $query = Translation::query()
            ->where('locale', $locale)
            ->where('is_active', true);

        if ($group && $group !== '*') {
            $query->where('group', $group);
        } else {
            $query->whereNull('group');
        }

        foreach ($query->get(['key', 'value']) as $row) {
            // Expect keys like 'messages.welcome' when group='messages': we need the part after the first dot
            $dotPos = strpos($row->key, '.');
            $item = $dotPos !== false ? substr($row->key, $dotPos + 1) : $row->key;
            data_set($lines, $item, $row->value);
        }

        return $lines;
    }

    public function addNamespace($namespace, $hint)
    {
        $this->fileLoader->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->fileLoader->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->fileLoader->namespaces();
    }
}
