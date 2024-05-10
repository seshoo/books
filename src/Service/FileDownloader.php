<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class FileDownloader
{

    public function __construct(
        private string $publicDir,
        private string $uploadsDir,
        private HttpClientInterface $client,
        private Filesystem $filesystem
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function load(string $url): ?string
    {
        $response = $this->client->request('GET', $url);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return null;
        }
        $imageData = $response->getContent();

        $index = 0;

        while (true) {
            $prefix = $index === 0 ? '' : ++$index;
            $imageName = $this->generateFileName($url, $prefix);
            $folderName = substr($imageName, 0, 3);
            $path = $this->publicDir . $this->uploadsDir . '/' . $folderName;
            if (!$this->filesystem->exists($path)) {
                $this->filesystem->mkdir($path);
            }
            $filePath = $path . '/' . $imageName;
            if (!$this->filesystem->exists($filePath)) {
                $this->filesystem->dumpFile($filePath, $imageData);

                return $this->uploadsDir . '/' . $folderName . '/' . $imageName;
            }
        }
    }

    private function generateFileName(string $url, string $salt = ''): string
    {
        $pathInfo = pathinfo($url);
        $extension = $pathInfo['extension'] ?? 'jpg';

        return md5($url . $salt . time()) . '.' . $extension;
    }
}