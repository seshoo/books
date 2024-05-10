<?php

declare(strict_types=1);

namespace App\Service;

use App\Builder\BookBuilder;

readonly class ParseBookService
{
    public function __construct(
        private BookBuilder $bookBuilder,
    ) {
    }


    public function process(string $json): iterable
    {
        $data = json_decode($json, true);

        foreach ($data as $item) {
            yield $this->bookBuilder->resolve($item);
        }
    }
}