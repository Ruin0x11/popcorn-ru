<?php

namespace App\Spider;

use App\Entity\File;
use App\Entity\Torrent\BaseTorrent;
use App\Service\EpisodeService;
use App\Service\TorrentService;
use App\Spider\Dto\ForumDto;
use App\Spider\Dto\TopicDto;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class NyaaSi extends AbstractSpider
{
    public const BASE_URL = 'https://nyaa.si/';

    /** @var Client */
    private $client;

    public function __construct(TorrentService $torrentService, EpisodeService $episodeService, LoggerInterface $logger, string $torProxy)
    {
        //$torProxy = '';
        parent::__construct($torrentService, $episodeService, $logger);
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            RequestOptions::TIMEOUT => 10,
            'curl' => [
                CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5_HOSTNAME
            ],
            'cookies' => new FileCookieJar(sys_get_temp_dir() . '/torrentgalaxy.cookie.json', true)
        ]);
    }

    public function getPriority(BaseTorrent $torrent): int
    {
        return -10;
    }

    public function getSource(BaseTorrent $torrent): string
    {
        return self::BASE_URL . ltrim($torrent->getProviderExternalId(), '/');
    }

    public function getForumKeys(): array
    {
        return [
            "1_2", // Anime - English-translated
            "4_1", // Live Action - English-translated
        ];
    }

    public function getPage(ForumDto $forum): \Generator
    {
        $res = $this->client->get(sprintf('https://nyaa.si/?f=0&c=%s&q=&p=%d', $forum->id, $forum->page - 1));
        $html = $res->getBody()->getContents();
        $crawler = new Crawler($html);

        $table = $crawler->filter('table.torrent-list');
        $lines = array_filter(
            $table->filter('tr')->each(
                static function (Crawler $c) {
                    return $c;
                }
            ),
            function (Crawler $c) use ($forum) {
                return strpos($c->html(), 'href="/view/') !== false;
            }
        );

        $after = $forum->last ? new \DateTime($forum->last . ' hours ago') : false;
        $exist = false;

        foreach ($lines as $n => $line) {
            /** @var Crawler $line */
            if (preg_match('#href="(/view/[^"]+)"#', $line->html(), $m)) {
                $unixTimestamp = $line->filter('td')->eq(4)->attr('data-timestamp');
                $time = new DateTime("@$unixTimestamp");
                if ($time < $after) {
                    continue;
                }

                $seed = $line->filter('td')->eq(5)->text();
                $seed = preg_replace('#[^0-9]#', '', $seed);
                $leech = $line->filter('td')->eq(6)->text();
                $leech = preg_replace('#[^0-9]#', '', $leech);

                yield new TopicDto(
                    $m[1],
                    (int) $seed,
                    (int) $leech,
                    $n * 10 + random_int(10, 20)
                );
                $exist = true;
                continue;
            }
        }

        if (!$exist) {
            return;
        }

        if (strpos($crawler->html(), sprintf('/?f=0&c=%s&q=&p=%d', $forum->id, $forum->page)) !== false) {
            yield new ForumDto($forum->id, $forum->page + 1, $forum->last, random_int(1800, 3600));
        }
    }

    public function getTopic(TopicDto $topic)
    {
        $this->context = ['spider' => $this->getName(), 'topicId' => $topic->id];

        $res = $this->client->get($topic->id);
        $html = $res->getBody()->getContents();
        $crawler = new Crawler($html);

        $title = $crawler->filter('h3.panel-title')->first()->text();
        $this->context["title"] = $title;

        $anitomy = anitomy_parse(title);
        if (!$anitomy["anime_title"]) {
            $this->logger->info('Anitomy failed to parse', $this->context);
            return;
        }
        $this->context["anitomy"] = $anitomy;

        $kitsu = $this->getKitsu($anitomy["anime_title"]);

        if (!$kitsu) {
            $this->logger->info('No Kitsu', $this->context);
            return;
        }
        $this->context["kitsu"] = $kitsu;

        $quality = $anitomy["video_resolution"];
        if (!$quality) {
            $quality = "480p";
        }

        $footer = $crawler->filter('.card-footer-item')->first();

        preg_match('#"(magnet[^"]+)"#', $torrentTable->html(), $m);
        if (empty($m[1])) {
            $this->logger->warning('No Magnet torrent', $this->context);
            return;
        }
        $url = $m[1];

        $files = $this->getFiles($crawler);

        $lang = anitomy["language"];

        if (preg_match('#S(\d\d)E(\d\d)#', $title, $m)) {
            $torrent = $this->getEpisodeTorrentByImdb($topic->id, $imdb, (int)$m[1], (int)$m[2]);
        } else {
            $torrent = $this->getTorrentByImdb($topic->id, $imdb);
        }

        if (!$torrent) {
            return;
        }
        $torrent
            ->setProviderTitle($title)
            ->setUrl($url)
            ->setSeed($topic->seed)
            ->setPeer($topic->seed + $topic->leech)
            ->setQuality($quality)
            ->setLanguage($this->langName2IsoCode($lang))
        ;

        $torrent->setFiles($files);

        $this->torrentService->updateTorrent($torrent);

        $this->logger->debug('Saved torrent', $this->context);
    }
}
