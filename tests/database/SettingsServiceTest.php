<?php

use App\Core\Settings\SettingsService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class SettingsServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate   = true;
    protected $namespace = 'App';

    public function testSetAndGetCastsStoredSettingValues(): void
    {
        $settings = new SettingsService();
        $settings->bustCache();

        $settings->set('feature.enabled', '1', 'feature', 'bool');
        $settings->set('feature.limit', '5', 'feature', 'int');
        $settings->set('feature.payload', ['a' => 1], 'feature', 'json');

        $this->assertTrue($settings->get('feature.enabled'));
        $this->assertSame(5, $settings->get('feature.limit'));
        $this->assertSame(['a' => 1], $settings->get('feature.payload'));
        $this->assertSame('fallback', $settings->get('missing.key', 'fallback'));
    }
}
