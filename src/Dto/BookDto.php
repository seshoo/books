<?php

declare(strict_types=1);

namespace App\Dto;

use DateTime;

class BookDto
{
    public ?string $title;
    public ?string $isbn;
    public ?int $pageCount;
    public ?DateTime $publishedDate;
    public ?string $thumbnailUrl;
    public ?string $shortDescription;
    public ?string $longDescription;
    public ?string $status;
    public ?array $authors;
    public ?array $categories;
}