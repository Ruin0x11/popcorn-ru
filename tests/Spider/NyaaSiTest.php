<?php

namespace App\Tests\Spider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Spider\NyaaSi;
use App\Spider\Dto\TopicDto;
use App\Entity\Torrent\ShowTorrent;
use VCR\VCR;

final class NyaaSiTest extends SpiderTestCase
{
    public function testNyaaSiGetTopic(): void
    {
        $spider = $this->getService(NyaaSi::class);

        $topic = new TopicDto("/view/848234", 10, 3, 10);

        VCR::insertCassette("nyaaSiGetTopic");
        $spider->getTopic($topic);
        VCR::eject();

        $entityManager = $this->getService('doctrine')->getManager();
        $torrentRepo = $entityManager->getRepository(ShowTorrent::class);

        $torrent = $torrentRepo->findOneBy(["provider" => "NyaaSi"]);
        \Doctrine\Common\Util\Debug::dump($torrent);

        $this->assertEquals("NyaaSi", $torrent->getProvider());
        $this->assertEquals("[VCB-Studio] Puella Magi Madoka Magica/魔法少女まどか☆マギカ TV+Movies 10bit 1080p HEVC BDRip [Rev Fin]", $torrent->getProviderTitle());
        $this->assertEquals("55.36 GB", $torrent->getFilesize());
        $this->assertEquals("1080p", $torrent->getQuality());
        $this->assertEquals("ja", $torrent->getLanguage());
        $this->assertEquals(10, $torrent->getSeed());
        $this->assertEquals(13, $torrent->getPeer());
        $this->assertEquals(308, count($torrent->getFiles()));
        $this->assertEquals(1, preg_match("#/Original soundtrack.cue$#", $torrent->getFiles()[0]->getName()));
        $this->assertEquals(6656, $torrent->getFiles()[0]->getSize());
    }
}
