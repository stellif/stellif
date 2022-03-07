<?php

namespace Stellif\Stellif;

use Symfony\Component\Yaml\Yaml;
use Ramsey\Uuid\Uuid;

/**
 * The Store class is Stellif's data engine. It takes care of
 * things such as creating, updating and querying for data.
 * 
 * @author Asko Nomm <asko@bien.ee>
 */
class Store
{
    /**
     * Given a file `$path`, returns the name of it, without extension, 
     * which serves as the item' ID.
     *
     * @param string $path
     * @return string
     */
    private static function getIdFromPath(string $path): string
    {
        $parts = explode('/', $path);

        return str_replace('.yaml', '', end($parts));
    }

    /**
     * Undocumented function
     *
     * @param string $path
     * @return array
     */
    private static function get(string $path): array
    {
        $fullPath = STELLIF_ROOT . '/store/' . $path . '/*.yaml';
        $items = [];

        foreach (glob($fullPath) as $item) {
            $items[] = static::getItem($item);
        }

        return $items;
    }

    private static function getItem(string $path, mixed $default = []): array
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

    public static function find(string $path): StoreSearch
    {
        $items = static::get($path);

        return new StoreSearch($items);
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

        // If the directory does not exist, create it.
        $dirname = dirname(str_replace(':id', $generatedId, $fullPath));

        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
        }

        // Unset transient data
        unset($data['_id']);
        unset($data['_path']);

        // Store data
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
        $item = static::find($path)->where($rules)->first();

        if ($item) {
            unlink($item['_path']);
        }
    }
}
