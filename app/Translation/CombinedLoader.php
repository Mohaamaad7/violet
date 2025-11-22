<?php

namespace App\Translation;

use App\Models\Translation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Contracts\Translation\Loader as LoaderInterface;

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
            // For keys like "store.header.home", when group="store",
            // we need to extract "header.home" and set it nested
            $fullKey = $row->key;
            
            // If group matches the start of the key, remove it
            if ($group && strpos($fullKey, $group . '.') === 0) {
                $nestedKey = substr($fullKey, strlen($group) + 1);
                data_set($lines, $nestedKey, $row->value);
            } else {
                // Fallback: set the entire key
                data_set($lines, $fullKey, $row->value);
            }
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
