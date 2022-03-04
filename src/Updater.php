<?php

namespace Stellif\Stellif;

use Stellif\Stellif\Store;
use WpOrg\Requests\Requests;

/**
 * The Updater makes sure that the installation of Stellif is always
 * the latest one. It updates everything in silence, without any user
 * action required, between a single HTTP request, and response. 
 * 
 * @author Asko Nomm <asko@bien.ee> 
 */
class Updater
{
    /**
     * Paths that should not be deleted when replacing 
     * files with new ones. 
     *
     * @var array
     */
    private array $doNotDeletePaths = [
        '\/public\/assets\/themes.*',
        '\/htdocs\/assets\/themes.*',
        '\/public_html\/assets\/themes.*',
        '\/views\/themes.*',
        '\/store.*',
    ];

    /**
     * API endpoint where to get the latest release information.
     *
     * @var string
     */
    private string $latestReleaseEndpoint = 'https://api.github.com/repos/stellif/stellif/releases/latest';

    /**
     * The ZIP URL of the latest release if there is one, `false` 
     * otherwise.
     *
     * @var string|boolean
     */
    private string|bool $latestReleaseURL = false;

    /**
     * Timestamp of the last time Stellif checked if an update
     * is available.
     *
     * @var integer|boolean
     */
    private int|bool $updateCheckedTimestamp = false;

    /**
     * Upon initialization of the class, Stellif will check 
     * when was the last time an update was checked and then
     * check if an update is avaiable, and if it is, will update.
     */
    public function __construct()
    {
        $lastCheckedTimestamp = Store::getInItem('meta/update', 'last_checked_timestamp');

        if ($lastCheckedTimestamp) {
            $this->updateCheckedTimestamp = (int) $lastCheckedTimestamp;
        }

        if ($this->isUpdateAvailable()) {
            $this->update();
        }
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function isUpdateAvailable(): bool
    {
        // We don't want to run updater in devmode
        if (file_exists(STELLIF_ROOT . '/composer.json')) {
            return false;
        }

        // We also don't want to run it if we checked it recently (less than 24 hour ago)
        if ($this->updateCheckedTimestamp && (abs(time() - $this->updateCheckedTimestamp) / 3600) < 24) {
            return false;
        }

        try {
            $version = @file_get_contents(STELLIF_ROOT . '/version.txt', true);
            $latestReleaseRequest = Requests::get($this->latestReleaseEndpoint, ['User-Agent' => 'stellif\stellif']);

            if ($latestReleaseRequest->success) {
                $latestRelease = json_decode($latestReleaseRequest->body, true);

                Store::update('meta/update', [
                    'last_checked_timestamp' => time(),
                ]);

                if (trim($latestRelease['tag_name']) !== trim($version)) {
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

    /**
     * Undocumented function
     *
     * @param string $path
     * @return void
     */
    private function removeDir(string $path): void
    {
        $directory = new \RecursiveDirectoryIterator($path,  \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if (preg_match('/' . implode('|', $this->doNotDeletePaths) . '/', $file->getPathname())) continue;

            if (is_dir($file)) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }

        @rmdir($path);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    private function deleteFiles(): void
    {
        foreach (glob(STELLIF_ROOT . '/*') as $path) {
            if (preg_match('/' . implode('|', $this->doNotDeletePaths) . '/', $path)) continue;
            if ($path === STELLIF_ROOT . '/stellif-update.zip') continue;

            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
    }

    private function moveFiles(string $from, string $to): void
    {
        $directory = new \RecursiveDirectoryIterator($from,  \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            $path = $file->getPathname();
            if (is_file($path)) {
                rename($path, str_replace($from, $to, $path));
            }
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    private function update(): void
    {
        if (isset($this->latestReleaseURL) && $this->latestReleaseURL !== '') {
            // Download latest version
            $response = Requests::get($this->latestReleaseURL, [], [
                'filename' => STELLIF_ROOT . '/stellif-update.zip'
            ]);

            if ($response->success) {
                // Figure out the public facing directory on this server
                $dir = 'public';

                if (is_dir(STELLIF_ROOT . '/public_html')) {
                    $dir = 'public_html';
                }

                if (is_dir(STELLIF_ROOT . '/htdocs')) {
                    $dir = 'htdocs';
                }

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

                // If the public facing directory is not "public", rename
                // it accordingly.
                if ($dir !== 'public') {
                    $this->moveFiles(STELLIF_ROOT . '/public', STELLIF_ROOT . '/' . $dir);
                }

                // Delete update zip
                unlink(STELLIF_ROOT . '/stellif-update.zip');
            }
        }
    }
}
