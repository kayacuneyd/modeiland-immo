<?php

use App\Core\Auth\AuthService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AuthServiceTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        session()->destroy();

        parent::tearDown();
    }

    public function testPermissionChecksSupportExactPrefixAndGlobalWildcards(): void
    {
        $auth = new AuthService();

        session()->set([
            'admin_user_id'     => 10,
            'admin_permissions' => ['pages.*', 'media.delete'],
        ]);

        $this->assertTrue($auth->can('pages.create'));
        $this->assertTrue($auth->can('media.delete'));
        $this->assertFalse($auth->can('settings.edit'));

        session()->set('admin_permissions', ['*']);

        $this->assertTrue($auth->can('settings.edit'));
    }
}
