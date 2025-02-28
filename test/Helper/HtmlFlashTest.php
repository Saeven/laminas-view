<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\HtmlFlash;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;

class HtmlFlashTest extends TestCase
{
    /**
     * @psalm-suppress DeprecatedClass
     * @var HtmlFlash
     */
    public $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp(): void
    {
        $this->view = new View();
        /** @psalm-suppress DeprecatedClass */
        $this->helper = new HtmlFlash();
        $this->helper->setView($this->view);
    }

    public function testMakeHtmlFlash(): void
    {
        $htmlFlash = $this->helper->__invoke('/path/to/flash.swf');

        // @codingStandardsIgnoreStart
        $objectStartElement = '<object data="&#x2F;path&#x2F;to&#x2F;flash.swf" type="application&#x2F;x-shockwave-flash">';
        // @codingStandardsIgnoreEnd

        $this->assertStringContainsString($objectStartElement, $htmlFlash);
        $this->assertStringContainsString('</object>', $htmlFlash);
    }
}
