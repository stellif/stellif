<?php

namespace Stellif\Stellif;

use Symfony\Component\Yaml\Yaml;
use Ramsey\Uuid\Uuid;

class Store
{
    private static function getIdFromPath(string $path): string
    {
        $parts = explode('/', $path);

        return str_replace('.yaml', '', end($parts));
    }

    public static function get(string $path): array
    {
        $fullPath = STELLIF_ROOT . '/store/' . $path . '/*.yaml';
        $items = [];

        foreach (glob($fullPath) as $item) {
            $items[] = static::getItem($item);
        }

        return $items;
    }

    public static function getItem(string $path, mixed $default = []): array
    {
        $fullPath = STELLIF_ROOT . '/store/' . $path . '.yaml';

        if (str_contains($path, STELLIF_ROOT)) {
            $fullPath = $path;
        }

        if (file_exists($fullPath)) {
            return [
                ...Yaml::parseFile($fullPath),
                '_id' => static::getIdFromPath($fullPath),
                '_path' => $fullPath,
            ];
        }

        return $default;
    }

    public static function getInItem(string $path, string $key, mixed $default = null): mixed
    {
        $data = static::getItem($path);

        if (isset($data[$key])) {
            return $data[$key];
        }

        return $default;
    }

    public static function find(string $path, array $rules = []): array
    {
        $items = static::get($path);
        $matchedItems = [];

        foreach ($items as $item) {
            $requirements = count($rules);

            foreach ($rules as $k => $v) {
                if (isset($item[$k]) && $item[$k] === $v) {
                    $requirements--;
                }
            }

            if ($requirements === 0) {
                $matchedItems[] = $item;
            }
        }

        return $matchedItems;
    }

    public static function findFirst(string $path, array $rules = []): bool|array
    {
        $items = static::find($path, $rules);

        if (count($items) > 0) {
            return $items[0];
        }

        return false;
    }

    public static function put(string $path, array $data): ?string
    {
        // Construct path
        $fullPath = STELLIF_ROOT . '/store/' . $path . '.yaml';

        if (str_contains($path, STELLIF_ROOT)) {
            $fullPath = $path;
        }

        // Generate ID
        $generatedId = Uuid::uuid4();

        // Create dir
        $dirname = dirname(str_replace(':id', $generatedId, $fullPath));

        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        // Store data
        unset($data['_id']);
        unset($data['_path']);
        file_put_contents(str_replace(':id', $generatedId, $fullPath), Yaml::dump($data));

        if (str_contains($fullPath, ':id')) {
            return $generatedId;
        }

        return null;
    }

    public static function update(string $path, array $data): void
    {
        $item = static::getItem($path);

        static::put($path, [
            ...$item,
            ...$data,
        ]);
    }

    public static function remove(string $path, array $rules = []): void
    {
        // If no rules are provided and the `$path` leads to an actual file, 
        // then let's straight up delete it from `$path`.
        if (empty($rules) && is_file(STELLIF_ROOT . '/store/' . $path . '.yaml')) {
            unlink($path);
        }

        // Otherwise, let's try to find the file according to `$rules`. 
        $item = static::findFirst($path, $rules);

        if ($item) {
            unlink($item['_path']);
        }
    }
}
