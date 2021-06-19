<?php

namespace App\Tests\Spider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Spider\NyaaSi;
use App\Spider\Dto\TopicDto;
use App\Spider\Dto\ForumDto;
use App\Entity\Torrent\ShowTorrent;
use App\Entity\Anime;
use VCR\VCR;

final class NyaaSiTest extends SpiderTestCase
{
    public function testNyaaSiGetPage(): void
    {
        $spider = $this->getService(NyaaSi::class);

        $topic = new ForumDto("1_2");

        VCR::insertCassette("nyaaSiGetPage");
        $page = iterator_to_array($spider->getPage($topic));
        VCR::eject();

        $this->assertEquals(75, count($page));
        $this->assertEquals("/view/1399535", $page[0]->id);
        $this->assertEquals(106, $page[0]->seed);
        $this->assertEquals(174, $page[0]->leech);
    }

    public function testNyaaSiGetTopicEnglishTranslated(): void
    {
        $spider = $this->getService(NyaaSi::class);

        $topic = new TopicDto("/view/1366028", 6, 4, 10);

        VCR::insertCassette("nyaaSiGetTopicEnglishTranslated");
        $spider->getTopic($topic);
        VCR::eject();

        $entityManager = $this->getService('doctrine')->getManager();

        $torrentRepo = $entityManager->getRepository(ShowTorrent::class);
        $torrent = $torrentRepo->findOneBy(["provider" => "NyaaSi"]);

        $this->assertEquals("NyaaSi", $torrent->getProvider());
        $this->assertEquals("[Pog42] Yojouhan Shinwa Taikei + Specials | The Tatami Galaxy (BD 1080p HEVC x265 10-bit FLAC)", $torrent->getProviderTitle());
        $this->assertEquals("17.74 GB", $torrent->getFilesize());
        $this->assertEquals("1080p", $torrent->getQuality());
        $this->assertEquals("en", $torrent->getLanguage());
        $this->assertEquals(6, $torrent->getSeed());
        $this->assertEquals(10, $torrent->getPeer());
        $this->assertEquals(14, count($torrent->getFiles()));
        $this->assertEquals(1, preg_match("#/\[Pog42\] Yojouhan Shinwa Taikei S1 \(BD 1080p x265 FLAC\)\.mkv$#", $torrent->getFiles()[0]->getName()));
        $this->assertEquals(433166745, $torrent->getFiles()[0]->getSize());

        $anime = $torrent->getShow();

        $this->assertEquals("tt1847445", $anime->getImdb());
    }

    public function testNyaaSiGetTopicRaw(): void
    {
        $spider = $this->getService(NyaaSi::class);

        $topic = new TopicDto("/view/848234", 10, 3, 10);

        VCR::insertCassette("nyaaSiGetTopicRaw");
        $spider->getTopic($topic);
        VCR::eject();

        $entityManager = $this->getService('doctrine')->getManager();

        $torrentRepo = $entityManager->getRepository(ShowTorrent::class);
        $torrent = $torrentRepo->findOneBy(["provider" => "NyaaSi"]);

        $this->assertEquals("NyaaSi", $torrent->getProvider());
        $this->assertEquals("[VCB-Studio] Puella Magi Madoka Magica/魔法少女まどか☆マギカ TV+Movies 10bit 1080p HEVC BDRip [Rev Fin]", $torrent->getProviderTitle());
        $this->assertEquals("55.36 GB", $torrent->getFilesize());
        $this->assertEquals("1080p", $torrent->getQuality());
        $this->assertEquals("en", $torrent->getLanguage());
        $this->assertEquals(10, $torrent->getSeed());
        $this->assertEquals(13, $torrent->getPeer());
        $this->assertEquals(308, count($torrent->getFiles()));
        $this->assertEquals(1, preg_match("#/Original soundtrack.cue$#", $torrent->getFiles()[0]->getName()));
        $this->assertEquals(6656, $torrent->getFiles()[0]->getSize());

        $anime = $torrent->getShow();

        $this->assertEquals("tt1773185", $anime->getImdb());
    }

    public function testNyaaSiGetTopicAmbiguousYear()
    {
        $spider = $this->getService(NyaaSi::class);

        $topic = new TopicDto("/view/1399634", 10, 3, 10);

        // might match 2004 version instead of 1980 version
        VCR::insertCassette("nyaaSiGetTopicAmbiguousYear");
        $spider->getTopic($topic);
        VCR::eject();

        $entityManager = $this->getService('doctrine')->getManager();

        $torrentRepo = $entityManager->getRepository(\App\Entity\Torrent\BaseTorrent::class);
        $torrent = $torrentRepo->findOneBy(["provider" => "NyaaSi"]);

        $this->assertEquals("NyaaSi", $torrent->getProvider());
        $this->assertEquals("Tetsujin #28 (1980) [TSHS] episode 01 (Betamax rip) original broadcast with subtitled commercials [26D3B260].mkv", $torrent->getProviderTitle());
        $this->assertEquals("346.5 MB", $torrent->getFilesize());
        $this->assertEquals("480p", $torrent->getQuality());
        $this->assertEquals("en", $torrent->getLanguage());
        $this->assertEquals(10, $torrent->getSeed());
        $this->assertEquals(13, $torrent->getPeer());

        $anime = $torrent->getMedia();

        $this->assertEquals("tt0458218", $anime->getImdb());
        $this->assertEquals("1980", $anime->getYear());
    }
}
