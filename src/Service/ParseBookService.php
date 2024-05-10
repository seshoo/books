<?php

declare(strict_types=1);

namespace App\Service;

use App\Builder\BookBuilder;

class ParseBookService
{
    protected array $processElementsCallback = [];

    public function __construct(
        private readonly BookBuilder $bookBuilder,
    ) {
    }

    public function setProcessElementsCallback(\Closure $callback): void
    {
        $this->processElementsCallback[] = $callback;
    }

    protected function emitProcessElementCallback(int $currentIndex, int $elementsCount): void
    {
        foreach ($this->processElementsCallback as $callback) {
            $callback($currentIndex, $elementsCount);
        }
    }

    public function process(string $json): iterable
    {
        $data = json_decode($json, true);
        $count = count($data);
        $index = 0;
        foreach ($data as $item) {
            $this->emitProcessElementCallback(++$index, $count);

            yield $this->bookBuilder->resolve($item);
        }
    }
}