<?php

namespace App\Command;

use App\Entity\Book;
use App\Service\FileDownloader;
use App\Service\FinderService;
use App\Service\ParseBookService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:parse-book',
    description: 'Parse book from file and save to database'
)]
class ParseBookCommand extends Command
{
    public function __construct(
        private readonly string $dataLocation,
        private readonly string $defaultCategory,
        private readonly Filesystem $filesystem,
        private readonly ParseBookService $parseBookService,
        private readonly FileDownloader $fileDownloader,
        private readonly FinderService $categoryFinder,
        private readonly FinderService $authorFinder,
        private readonly EntityManagerInterface $entityManager,

    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Parse book from file and save to database');

        if (!$this->filesystem->exists($this->dataLocation)) {
            $io->error('The specified JSON file does not exist.');
            return Command::FAILURE;
        }

        $data = file_get_contents($this->dataLocation);

        $progressBar = $io->createProgressBar();

        $this->parseBookService->setProcessElementsCallback(
            function (int $currentIndex, int $elementsCount) use ($progressBar) {
                $progressBar->setMaxSteps($elementsCount);
                $progressBar->setProgress($currentIndex);
                $progressBar->display();
            }
        );

        foreach ($this->parseBookService->process($data) as $bookDto) {
            $localPath = null;
            if ($bookDto->thumbnailUrl !== null) {
                try {
                    $localPath = $this->fileDownloader->load($bookDto->thumbnailUrl);
                } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface) {
                }
            }

            $book = new Book();

            $book->setTitle($bookDto->title);
            $book->setIsbn($bookDto->isbn);
            $book->setPageCount($bookDto->pageCount);
            $book->setPublishedDate($bookDto->publishedDate);
            $book->setThumbnailUrl($localPath);
            $book->setShortDescription($bookDto->shortDescription);
            $book->setLongDescription($bookDto->longDescription);
            $book->setStatus($bookDto->status);

            foreach ($bookDto->authors as $authorName) {
                $author = $this->authorFinder->getByName($authorName);
                $book->addAuthor($author);
            }

            if (!empty($bookDto->categories)) {
                foreach ($bookDto->categories as $categoryName) {
                    $category = $this->categoryFinder->getByName($categoryName);
                    $book->addCategory($category);
                }
            } else {
                $category = $this->categoryFinder->getByName($this->defaultCategory);
                $book->addCategory($category);
            }

            $this->entityManager->persist($book);
        }
        $this->entityManager->flush();


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
