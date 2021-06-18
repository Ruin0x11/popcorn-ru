<?php

namespace App\Tests\Spider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Spider\NyaaSi;
use App\Spider\Dto\TopicDto;
use App\Repository\TorrentRepository;
use VCR\VCR;

final class NyaaSiTest extends SpiderTestCase
{
    public function testNyaaSiGetTopic(): void
    {
        $spider = $this->getService(NyaaSi::class);

        $topic = new TopicDto("/view/962260", 1, 5, 10);

        VCR::insertCassette("nyaaSiGetTopic");
        $spider->getTopic($topic);
        VCR::eject();

        $torrentRepo = $this->getService(TorrentRepository::class);

        $this->assertEquals(0, 1);
    }
}
