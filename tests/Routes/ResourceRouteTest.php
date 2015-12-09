<?php

namespace Waavi\Test\Routes;

use Illuminate\Routing\Router;
use Waavi\Translation\Repositories\LanguageRepository;
use Waavi\Translation\Routes\ResourceRegistrar;
use Waavi\Translation\Test\TestCase;
use \Mockery;

class ResourceRouteTest extends TestCase
{
    public function setUp()
    {
        // During the parent's setup, both a 'es' 'Spanish' and 'en' 'English' languages are inserted into the database.
        parent::setUp();
        $this->languageRepository = Mockery::mock(LanguageRepository::class);
        $this->router             = Mockery::mock(Router::class);
        $this->registrar          = new ResourceRegistrar($this->router, $this->languageRepository);
    }

    protected function getMethod()
    {
        // Set the method to public for testing
        $class  = new \ReflectionClass(ResourceRegistrar::class);
        $method = $class->getMethod('getGroupResourceName');
        $method->setAccessible(true);
        return $method;
    }

    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function test_group_resource_name_filters_out_locales()
    {
        $this->router->shouldReceive('getLastGroupPrefix')->andReturn('en/admin/blog');
        $this->languageRepository->shouldReceive('availableLocales')->andReturn(['en', 'es']);
        $method = $this->getMethod();
        $result = $method->invoke($this->registrar, '', 'post', 'index');
        $this->assertEquals('admin.blog.post.index', $result);
    }

    /**
     * @test
     */
    public function test_group_resource_name_doesnt_mess_with_prefixes_containing_part_of_the_locale()
    {
        $this->router->shouldReceive('getLastGroupPrefix')->andReturn('en/enabled/enabler');
        $this->languageRepository->shouldReceive('availableLocales')->andReturn(['en', 'es']);
        $method = $this->getMethod();
        $result = $method->invoke($this->registrar, '', 'women', 'index');
        $this->assertEquals('enabled.enabler.women.index', $result);
    }

    /**
     * @test
     */
    public function test_only_locale_prefix()
    {
        $this->router->shouldReceive('getLastGroupPrefix')->andReturn('en');
        $this->languageRepository->shouldReceive('availableLocales')->andReturn(['en', 'es']);
        $method = $this->getMethod();
        $result = $method->invoke($this->registrar, '', 'post', 'index');
        $this->assertEquals('post.index', $result);
    }

    /**
     * @test
     */
    public function test_no_locale_prefix()
    {
        $this->router->shouldReceive('getLastGroupPrefix')->andReturn('admin');
        $this->languageRepository->shouldReceive('availableLocales')->andReturn(['en', 'es']);
        $method = $this->getMethod();
        $result = $method->invoke($this->registrar, '', 'post', 'index');
        $this->assertEquals('admin.post.index', $result);
    }

    /**
     * @test
     */
    public function test_no_prefix()
    {
        $this->router->shouldReceive('getLastGroupPrefix')->andReturn('');
        $this->languageRepository->shouldReceive('availableLocales')->andReturn(['en', 'es']);
        $method = $this->getMethod();
        $result = $method->invoke($this->registrar, '', 'post', 'index');
        $this->assertEquals('post.index', $result);
    }
}
