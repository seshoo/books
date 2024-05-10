<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class FinderService
{
    private bool $initiatedCache = false;

    public function __construct(
        private readonly AdapterInterface $cache,
        private readonly SluggerInterface $slugger,
        private readonly ServiceEntityRepository $repository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function getByName(string $name): mixed
    {
        $this->initCache();

        $cacheKey = $this->getCacheKey($name);
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $entity = $this->createEntity($name);

        $cacheItem->set($entity);
        $this->cache->save($cacheItem);
        return $entity;
    }

    private function initCache(): void
    {
        if ($this->initiatedCache) {
            return;
        }
        $this->cache->clear();
        $categories = $this->repository->findAll();
        foreach ($categories as $entity) {
            $cacheKey = $this->getCacheKey($entity->getName());

            $cacheItem = $this->cache->getItem($cacheKey);
            $cacheItem->set($entity);
            $this->cache->save($cacheItem);
        }

        $this->initiatedCache = true;
    }

    private function createEntity(string $name): mixed
    {
        $class = $this->repository->getClassName();
        $entity = new $class();
        $entity
            ->setName($name)
            ->setSlug($this->slugger->slug($name)->lower()->toString());
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

    private function getCacheKey(string $name): string
    {
        return $this->slugger->slug($name, '_')->lower()->lower()->toString();
    }
}