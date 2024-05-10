<?php

declare(strict_types=1);

namespace App\Builder;

use App\Dto\BookDto;
use DateTime;
use Exception;

class BookBuilder
{
    public function resolve(array $item): BookDto
    {
        try {
            $publishedDate = new DateTime($item['publishedDate']['$date']);
        } catch (Exception) {
            $publishedDate = null;
        }

        $book = new BookDto();
        $book->title = $item['title'];
        $book->isbn = $item['isbn'] ?? null;
        $book->pageCount = $item['pageCount'] ?? null;
        $book->publishedDate = $publishedDate;
        $book->thumbnailUrl = $item['thumbnailUrl'] ?? null;
        $book->shortDescription = $item['shortDescription'] ?? null;
        $book->longDescription = $item['longDescription'] ?? null;
        $book->status = $item['status'];
        $book->authors = is_array($item['authors']) ? array_filter($item['authors']) : [];
        $book->categories = is_array($item['categories']) ? array_filter($item['categories']) : [];

        return $book;
    }
}