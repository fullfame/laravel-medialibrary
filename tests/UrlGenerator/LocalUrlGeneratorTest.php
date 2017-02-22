<?php

namespace Spatie\MediaLibrary\Test\UrlGenerator;

use Spatie\MediaLibrary\Exceptions\UrlCannotBeDetermined;
use Spatie\MediaLibrary\Test\TestCase;
use Spatie\MediaLibrary\UrlGenerator\LocalUrlGenerator;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;

class LocalUrlGeneratorTest extends TestCase
{
    protected $config;

    /**
     * @var \Spatie\MediaLibrary\Media
     */
    protected $mediaInPublic;

    /**
     * @var \Spatie\MediaLibrary\Conversion\Conversion
     */
    protected $conversion;

    /**
     * @var LocalUrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var BasePathGenerator
     */
    protected $pathGenerator;

    public function setUp()
    {
        parent::setUp();

        $this->config = app('config');



        $this->mediaInPublic = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.jpg'))->toMediaLibrary();

        $this->conversion = ConversionCollection::createForMedia($this->mediaInPublic)->getByName('thumb');

        // because BaseUrlGenerator is abstract we'll use LocalUrlGenerator to test the methods of base
        $this->urlGenerator = new LocalUrlGenerator($this->config);
        $this->pathGenerator = new BasePathGenerator();

        $this->urlGenerator
            ->setMedia($this->mediaInPublic)
            ->setConversion($this->conversion)
            ->setPathGenerator($this->pathGenerator);
    }

    /** @test */
    public function it_throws_an_error_if_the_path_is_not_public()
    {
        $this->config->set('filesystems.disks.media', [
            'driver' => 'local',
            'root' => storage_path('/media')
        ]);

        $this->expectException(UrlCannotBeDetermined::class);
        $this->urlGenerator->getUrl();
    }

    /** @test */
    public function it_can_work_with_laravels_storage_link()
    {
        symlink($this->getStorageDirectory('app/public'), $this->getPublicDirectory('storage'));

        $media = $this->testModelWithConversion->addMedia($this->getTestFilesDirectory('test.png'))->toMediaLibrary('test', 'storageLink');

        $conversion = ConversionCollection::createForMedia($media)->getByName('thumb');

        // because BaseUrlGenerator is abstract we'll use LocalUrlGenerator to test the methods of base
        $urlGenerator = new LocalUrlGenerator($this->config);
        $pathGenerator = new BasePathGenerator();

        $urlGenerator
            ->setMedia($media)
            ->setConversion($conversion)
            ->setPathGenerator($pathGenerator);
//        $this->assertEquals(public_path('storage/media/1/conversions/thumb.jpg'), $urlGenerator->getUrl());

        $pathRelativeToRoot = $media->id.'/conversions/'.$conversion->getName().'.'.$conversion->getResultExtension($media->extension);

        $this->assertEquals($pathRelativeToRoot, $urlGenerator->getPathRelativeToRoot());
    }


}
