<?php

use App\Core\Modules\ModuleRegistry;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Filters;

/**
 * @internal
 */
final class ModuleRegistryTest extends CIUnitTestCase
{
    public function testRouteFilesFollowManifestPriority(): void
    {
        helper('url');

        $moduleNames = array_map(
            static fn (string $path): string => basename(dirname($path, 2)),
            ModuleRegistry::routeFiles()
        );

        $this->assertSame(['Blog', 'Contact', 'Pages'], $moduleNames);
    }

    public function testAdminNavIncludesManifestItems(): void
    {
        helper(['url', 'cekirdek']);

        $labels = array_column(ModuleRegistry::adminNav(), 'label');

        $this->assertContains(lang('Common.nav_pages'), $labels);
        $this->assertContains(lang('Common.nav_blog'), $labels);
        $this->assertContains(lang('Common.nav_contact'), $labels);
    }

    public function testPostRequestsUseCsrfWithoutForcedHttpsRequiredFilter(): void
    {
        $filters = new Filters();

        $this->assertSame(['csrf'], $filters->methods['POST']);
        $this->assertNotContains('forcehttps', $filters->required['before']);
    }
}
