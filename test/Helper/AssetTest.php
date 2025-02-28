<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Exception;
use Laminas\View\Helper\Asset;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\TestCase;

class AssetTest extends TestCase
{
    /** @var array<non-empty-string, non-empty-string> */
    protected $resourceMap = [
        'css/style.css' => 'css/style-3a97ff4ee3.css',
        'js/vendor.js'  => 'js/vendor-a507086eba.js',
    ];

    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->asset = new Asset($this->resourceMap);
    }

    public function testHelperPluginManagerReturnsAssetHelper(): void
    {
        $helpers = $this->getHelperPluginManager();
        $asset   = $helpers->get('asset');

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testHelperPluginManagerReturnsAssetHelperByClassName(): void
    {
        $helpers = $this->getHelperPluginManager();
        $asset   = $helpers->get(Asset::class);

        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testInvalidAssetName(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The asset with the name "unknown" has not been defined');

        $this->asset->__invoke('unknown');
    }

    /**
     * @dataProvider assets
     * @param non-empty-string $name
     */
    public function testInvokeResult(string $name, string $expected): void
    {
        $result = $this->asset->__invoke($name);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array<array-key, array{0: non-empty-string, 1: non-empty-string}>
     */
    public function assets(): array
    {
        $data = [];
        foreach ($this->resourceMap as $key => $value) {
            $data[] = [$key, $value];
        }
        return $data;
    }

    protected function getHelperPluginManager(array $config = []): HelperPluginManager
    {
        $services = $this->createMock(ServiceManager::class);
        $services->expects(self::atLeast(1))
            ->method('get')
            ->with('config')
            ->willReturn($config);

        return new HelperPluginManager($services);
    }
}
