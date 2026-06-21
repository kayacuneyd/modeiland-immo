<?php

use App\Core\Auth\AuthController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AuthControllerTest extends CIUnitTestCase
{
    public function testSafeAdminRedirectRejectsExternalUrls(): void
    {
        helper('url');

        $controller = new AuthController();
        $method     = new ReflectionMethod($controller, 'safeAdminRedirect');

        $this->assertSame(site_url('admin/dashboard'), $method->invoke($controller, 'https://example.net/admin'));
        $this->assertSame(site_url('admin/users'), $method->invoke($controller, site_url('admin/users')));
    }
}
