<?php

namespace App\Repository;

use App\Entity\Anime;
use App\Repository\Locale\BaseLocaleRepository;
use App\Service\Search\SearchInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Anime|null find($id, $lockMode = null, $lockVersion = null)
 * @method Anime|null findOneBy(array $criteria, array $orderBy = null)
 * @method Anime[]    findAll()
 * @method Anime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnimeRepository extends MediaRepository
{
    public function __construct(SearchInterface $search, ManagerRegistry $registry)
    {
        parent::__construct($search, $registry, Anime::class);
    }

    public function findOrCreateAnimeByImdb(string $imdbId): Anime
    {
        $movie = $this->findByImdb($imdbId);
        if (!$movie) {
            $movie = new Anime();
            $movie->setImdb($imdbId);
            $this->_em->persist($movie);
        }

        return $movie;
    }
}
