<?php

namespace Spatie\MediaLibrary\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\UrlCannotBeDetermined;

class LocalUrlGenerator extends BaseUrlGenerator
{
    /**
     * Get the url for the profile of a media item.
     *
     * @return string
     *
     * @throws \Spatie\MediaLibrary\Exceptions\UrlCannotBeDetermined
     */
    public function getUrl(): string
    {
        if (!$this->isPubliclyAvailable()) {
            throw UrlCannotBeDetermined::mediaNotPubliclyAvailable($this->getStoragePath(), public_path().':'.storage_path('app/public'));
        }

        $url = $this->getBaseMediaDirectory().'/'.$this->getPathRelativeToRoot();

        $url = $this->makeCompatibleForNonUnixHosts($url);

        $url = $this->rawUrlEncodeFilename($url);

        return $url;
    }

    /*
     * Get the path for the profile of a media item.
     */
    public function getPath(): string
    {
        return $this->getStoragePath().'/'.$this->getPathRelativeToRoot();
    }

    /*
     * Get the directory where all files of the media item are stored.
     */
    protected function getBaseMediaDirectory(): string
    {
        return str_replace(public_path(), '', $this->getStoragePath());
    }

    /*
     * Get the path where the whole medialibrary is stored.
     */
    protected function getStoragePath() : string
    {
        $diskRootPath = $this->config->get("filesystems.disks.{$this->media->disk}.root");

        return realpath($diskRootPath);
    }

    protected function makeCompatibleForNonUnixHosts(string $url): string
    {
        if (DIRECTORY_SEPARATOR != '/') {
            $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
        }

        return $url;
    }

    public function rawUrlEncodeFilename(string $path = ''): string
    {
        return pathinfo($path, PATHINFO_DIRNAME).'/'.rawurlencode(pathinfo($path, PATHINFO_BASENAME));
    }

    /**
     * @return bool
     */
    protected function isPubliclyAvailable(): bool
    {
        $real_storage_path = $this->getStoragePath(); // Symlinks are resolved here.
        return starts_with($real_storage_path, public_path()) || starts_with($real_storage_path, storage_path('app/public'));
    }
}
