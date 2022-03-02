<?php

namespace Stellif\Stellif;

use Symfony\Component\Yaml\Yaml;

class Store
{
    private static function getIdFromPath(string $path): int
    {
        return (int) str_replace('.yaml', '', last(explode('/', $path)));
    }

    public static function get(string $path): array
    {
        $fullPath = STELLIF_ROOT . '/store/' . $path . '/*.yaml';
        $items = [];

        foreach (glob($fullPath) as $item) {
            $items[] = [
                ...static::getItem($item),
                '_id' => static::getIdFromPath($item),
            ];
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
            return Yaml::parseFile($fullPath);
        }

        return $default;
    }

    public static function getInItem(string $path, string $key, mixed $default = null): mixed
    {
        $data = static::getItem($path);

        if (in_array($key, $data)) {
            return $data[$key];
        }

        return $default;
    }

    public static function find(string $path, array $rules = []): bool|array
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
                $matchedItem[] = $item;
                break;
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

    public static function put(string $path, array $data): void
    {
        $fullPath = STELLIF_ROOT . '/store/' . $path . '.yaml';

        if (str_contains($path, STELLIF_ROOT)) {
            $fullPath = $path;
        }

        $dirname = dirname($fullPath);

        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        unset($data['_id']);

        file_put_contents($fullPath, Yaml::dump($data));
    }

    public static function update(string $path, array $data): void
    {
        $item = static::getItem($path);

        static::put($path, [
            ...$item,
            ...$data,
        ]);
    }
}
