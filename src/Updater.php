<?php

namespace Stellif\Stellif;

use WpOrg\Requests\Requests;
use Illuminate\Database\Capsule\Manager as Capsule;

class Updater
{
    private array $backupItems = [
        STELLIF_ROOT . '/assets/themes',
        STELLIF_ROOT . '/views/themes',
        STELLIF_ROOT . '/db/core.db',
    ];

    private string $infoURL = 'https://api.github.com/repos/stellif/stellif/releases/latest';
    private string $latestReleaseURL;
    private int|bool $updateCheckedTimestamp = false;

    public function __construct()
    {
        $lastChecked = Capsule::table('meta')->where('key', 'update_checked_timestamp')->first();

        if ($lastChecked && $lastChecked->value !== '') {
            $this->updateCheckedTimestamp = (int) $lastChecked->value;
        }

        if ($this->isUpdateAvailable()) {
            $this->update();
        }
    }

    public function isUpdateAvailable(): bool
    {
        // We don't want to run updater in devmode
        if (file_exists(STELLIF_ROOT . '/composer.json')) {
            return false;
        }

        // We also don't want to run it if we checked it recently (less than 1 hour ago)
        if ($this->updateCheckedTimestamp && (abs(time() - $this->updateCheckedTimestamp) / 3600) < 1) {
            return false;
        }

        try {
            $version = @file_get_contents(STELLIF_ROOT . '/version.txt', true);
            $latestReleaseRequest = Requests::get($this->infoURL, ['User-Agent' => 'stellif\stellif']);

            if ($latestReleaseRequest->success) {
                $latestRelease = json_decode($latestReleaseRequest->body, true);

                Capsule::table('meta')->upsert([[
                    'key' => 'update_checked_timestamp',
                    'value' => strval(time()),
                ]], ['key']);

                if ($latestRelease['tag_name'] !== $version) {
                    $this->latestReleaseURL = $latestRelease['assets'][0]['browser_download_url'];

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Logger::log(__METHOD__, $e->getMessage());

            return false;
        }
    }

    private function findFilesInPath($path): array
    {
        $directory = new \RecursiveDirectoryIterator($path,  \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);
        $result = [];

        foreach ($files as $file) {
            if (!is_dir($file)) {
                $result[] = $file->getPathName();
            }
        }

        return $result;
    }

    private function removeDir($path)
    {
        $directory = new \RecursiveDirectoryIterator($path,  \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if (is_dir($file)) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }

    private function deleteFiles(): void
    {
        foreach (glob(STELLIF_ROOT . '/*') as $path) {
            if ($path === STELLIF_ROOT . '/_tmp') continue;
            if ($path === STELLIF_ROOT . '/stellif-update.zip') continue;

            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
    }

    private function backupFiles(): void
    {
        foreach ($this->backupItems as $item) {
            if (is_dir($item)) {
                foreach ($this->findFilesInPath($item) as $path) {
                    $backupPath = STELLIF_ROOT . '/_tmp' . str_replace(STELLIF_ROOT, '', $path);

                    if (!is_dir(dirname($backupPath))) {
                        mkdir(dirname($backupPath), 0644, true);
                    }

                    rename($path, $backupPath);
                }
            }

            if (is_file($item)) {
                $backupPath = STELLIF_ROOT . '/_tmp' . str_replace(STELLIF_ROOT, '', $item);

                if (!is_dir(dirname($backupPath))) {
                    mkdir(dirname($backupPath), 0644, true);
                }

                rename($item, $backupPath);
            }
        }
    }

    private function restoreBackupFiles(): void
    {
        $files = $this->findFilesInPath(STELLIF_ROOT . '/_tmp');

        foreach ($files as $item) {
            if (is_dir($item)) {
                foreach ($this->findFilesInPath($item) as $path) {
                    $restorePath = STELLIF_ROOT . '/' . str_replace(STELLIF_ROOT . '/_tmp', '', $path);

                    if (!is_dir(dirname($restorePath))) {
                        mkdir(dirname($restorePath), 0644, true);
                    }

                    rename($path, $restorePath);
                }
            }

            if (is_file($item)) {
                $restorePath = STELLIF_ROOT . '/' . str_replace(STELLIF_ROOT . '/_tmp', '', $item);

                if (!is_dir(dirname($restorePath))) {
                    mkdir(dirname($restorePath), 0644, true);
                }

                rename($item, $restorePath);
            }
        }

        $this->removeDir(STELLIF_ROOT . '/_tmp');
    }

    private function update(): void
    {
        if (isset($this->latestReleaseURL) && $this->latestReleaseURL !== '') {
            // Download latest version
            $response = Requests::get($this->latestReleaseURL, [], [
                'filename' => STELLIF_ROOT . '/stellif-update.zip'
            ]);

            if ($response->success) {
                // Backup files
                $this->backupFiles();

                // Delete files
                $this->deleteFiles();

                // Unpack files
                $zip = new \ZipArchive;

                if ($zip->open(STELLIF_ROOT . '/stellif-update.zip') === true) {
                    $zip->extractTo(STELLIF_ROOT);
                    $zip->close();
                } else {
                    Logger::log(__METHOD__, 'Could not unzip update.');
                }

                // Delete update zip
                unlink(STELLIF_ROOT . '/stellif-update.zip');

                // Restore backup files
                $this->restoreBackupFiles();
            }
        }
    }
}
