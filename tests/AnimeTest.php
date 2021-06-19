<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use S1njar\Kitsu\Builder\SearchBuilder;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Tmdb\Event\Listener\Request\AcceptJsonRequestListener;
use Tmdb\Event\Listener\Request\AdultFilterRequestListener;
use Tmdb\Event\Listener\Request\ApiTokenRequestListener;
use Tmdb\Event\Listener\Request\ContentTypeJsonRequestListener;
use Tmdb\Event\Listener\Request\LanguageFilterRequestListener;
use Tmdb\Event\Listener\Request\RegionFilterRequestListener;
use Tmdb\Event\Listener\Request\UserAgentRequestListener;
use Tmdb\Event\Listener\Psr6CachedRequestListener;
use Tmdb\Event\RequestEvent;
use Tmdb\Token\Api\ApiToken;

final class AnimeTest extends TestCase
{
    public function testAnitomyExtension(): void
    {
        $result = anitomy_parse("[Golumpa] WONDER EGG PRIORITY - 07 [English Dub] [FuniDub 720p x264 AAC] [MKV] [91BD87A8]");

        $this->assertSame($result["file_name"], "[Golumpa] WONDER EGG PRIORITY - 07 [English Dub] [FuniDub 720p x264 AAC] [MKV] [91BD87A8]");
        $this->assertSame($result["video_resolution"], "720p");
        $this->assertSame($result["language"], "English");
        $this->assertSame($result["subtitles"], "Dub");
        $this->assertSame($result["video_term"], "x264");
        $this->assertSame($result["audio_term"], "AAC");
        $this->assertSame($result["file_checksum"], "91BD87A8");
        $this->assertSame($result["episode_number"], "07");
        $this->assertSame($result["anime_title"], "WONDER EGG PRIORITY");
        $this->assertSame($result["release_group"], "Golumpa");
    }
}
