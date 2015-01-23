<?php

use Cms\Classes\Theme;

class ThemeTest extends TestCase
{
    public function setUp()
    {
        Config::set('cms.activeTheme', 'test');
        Event::flush('cms.activeTheme');
        Theme::resetCache();
    }

    protected function countThemePages($path)
    {
        $result = 0;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $it->setMaxDepth(1);
        $it->rewind();

        while ($it->valid()) {
            if (!$it->isDot() && !$it->isDir() && $it->getExtension() == 'htm')
                $result++;

            $it->next();
        }

        return $result;
    }

    public function testGetPath()
    {
        $theme = Theme::load('test');

        $this->assertEquals(base_path().'/tests/fixtures/cms/themes/test', $theme->getPath());
    }

    public function testListPages()
    {
        $theme = Theme::load('test');

        $pages = $theme->listPages();
        $this->assertInternalType('array', $pages);

        $expectedPageNum = $this->countThemePages(base_path().'/tests/fixtures/cms/themes/test/pages');
        $this->assertEquals($expectedPageNum, count($pages));

        $this->assertInstanceOf('\Cms\Classes\Page', $pages[0]);
        $this->assertNotEmpty($pages[0]->url);
        $this->assertInstanceOf('\Cms\Classes\Page', $pages[1]);
        $this->assertNotEmpty($pages[1]->url);
    }

    public function testGetActiveTheme()
    {
        $activeTheme = Theme::getActiveTheme();

        $this->assertNotNull($activeTheme);
        $this->assertEquals('test', $activeTheme->getDirName());
    }

    /**
     * @expectedException        \System\Classes\SystemException
     * @expectedExceptionMessage The active theme is not set.
     */
    public function testNoActiveTheme()
    {
        Config::set('cms.activeTheme', null);
        Theme::getActiveTheme();
    }

    public function testApiTheme()
    {
        Event::flush('cms.activeTheme');
        Event::listen('cms.activeTheme', function() { return 'apitest'; });

        $activeTheme = Theme::getActiveTheme();
        $this->assertNotNull($activeTheme);
        $this->assertEquals('apitest', $activeTheme->getDirName());
    }
}
