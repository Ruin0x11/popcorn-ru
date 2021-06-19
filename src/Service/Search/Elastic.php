<?php

namespace App\Service\Search;

use App\Entity\BaseMedia;
use App\Entity\Movie;
use App\Entity\Show;
use App\Entity\Anime;
use App\Request\PageRequest;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Elastica\Query;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class Elastic implements SearchInterface
{
    const MAX_LIMIT = 10000;

    protected TransformedFinder $moviesFiner;
    protected TransformedFinder $showFiner;
    protected TransformedFinder $animesFiner;

    /**
     * Elastic constructor.
     *
     * @param TransformedFinder $moviesFiner
     * @param TransformedFinder $showFiner
     * @param TransformedFinder $animesFiner
     */
    public function __construct(TransformedFinder $moviesFiner, TransformedFinder $showFiner, TransformedFinder $animesFiner)
    {
        $this->moviesFiner = $moviesFiner;
        $this->showFiner = $showFiner;
        $this->animesFiner = $animesFiner;
    }

    public static function isIndexMovie(Movie $movie)
    {
        return true;
    }

    public static function isIndexShow(Show $show)
    {
        // There seems to be a race condition when linking episodes through
        // EpisodeService. The new show won't be indexed after the episodes are
        // linked and won't be listed in the API, but they will if
        // fos:elastica:populate is run by hand.
        //
        // return $anime->getEpisodes()->count() > 0;
        //
        return true;
    }

    public static function isIndexAnime(Anime $anime)
    {
        // return $anime->getEpisodes()->count() > 0;
        return true;
    }

    public function search(QueryBuilder $qb, ClassMetadata $class, PageRequest $pageRequest, string $locale, int $offset, int $limit): array
    {
        if (($offset + $limit) > self::MAX_LIMIT) {
            return [];
        }

        $boolQuery = new \Elastica\Query\BoolQuery();

        if ($pageRequest->keywords) {
            $nestedLocale = new \Elastica\Query\Nested();
            $nestedLocale->setPath('locales');

            $fieldQuery = new \Elastica\Query\MatchPhrasePrefix();
            $fieldQuery->setFieldQuery('locales.title', $pageRequest->keywords);
            $nestedLocale->setQuery($fieldQuery);
            $boolQuery->addShould($nestedLocale);

            $fieldQuery = new \Elastica\Query\MatchPhrasePrefix();
            $fieldQuery->setFieldQuery('title', $pageRequest->keywords);
            $boolQuery->addShould($fieldQuery);

            $imdb = new \Elastica\Query\Term();
            $imdb->setTerm('imdb', $pageRequest->keywords);
            $boolQuery->addShould($imdb);

            $boolQuery->setMinimumShouldMatch(1);
        }
        if ($pageRequest->genre) {
            $genre = new \Elastica\Query\Term();
            $genre->setTerm('genres', $pageRequest->genre);
            $boolQuery->addMust($genre);
        }

        $lang = new \Elastica\Query\Term();
        $lang->setTerm('existTranslations', $locale);
        $boolQuery->addMust($lang);

        $query = Query::create($boolQuery);
        $query->setFrom($offset)->setSize($limit);

        $query->setSort($this->buildSort($pageRequest->sort, $pageRequest->order));

        $finder = $class->getName() === Show::class ? $this->showFiner :
                ($class->getName() === Anime::class ? $this->animesFiner : $this->moviesFiner);
        /** @var BaseMedia[] $result */
        return $finder->find($query);
    }

    private function buildSort(string $sort, string $order)
    {
        switch ($sort) {
            case 'title':
            case 'name':
                return [ 'title' => $order, 'locales.title' => $order ];
            case 'popularity':
                return [
                    'rating.popularity' => [ 'nested_path' => 'rating', 'order' => $order],
                    'rating.watchers' => [ 'nested_path' => 'rating', 'order' => $order],
                ];
            case 'rating':
                return [
                    'rating.weightRating' => [ 'nested_path' => 'rating', 'order' => $order],
                ];
            case 'released':
            case 'updated':
                return [ 'released' => $order ];
            case 'last added':
                return [ 'created' => $order ];
            case 'trending':
                return [
                    'rating.watching' => [ 'nested_path' => 'rating', 'order' => $order],
                    'rating.watchers' => [ 'nested_path' => 'rating', 'order' => $order],
                ];
            case 'year':
                return [ 'year' => $order ];
        }
        return [
            'rating.popularity' => [ 'nested_path' => 'rating', 'order' => 'desc'],
            'rating.watchers' => [ 'nested_path' => 'rating', 'order' => 'desc'],
        ];
    }
}
