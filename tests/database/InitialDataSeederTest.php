<?php

use App\Database\Seeds\InitialDataSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class InitialDataSeederTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate   = true;
    protected $namespace = 'App';

    public function testSeederDoesNotTruncateRolesOrBreakUserRoleLinks(): void
    {
        $seeder = new InitialDataSeeder(config('Database'), $this->db);
        $seeder->run();

        $editor = $this->db->table('roles')->where('slug', 'editor')->get()->getRowArray();
        $this->assertNotEmpty($editor);

        $this->db->table('users')->insert([
            'name'          => 'Editor User',
            'email'         => 'editor@example.com',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'role_id'       => $editor['id'],
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $seeder->run();

        $roles = $this->db->table('roles')->countAllResults();
        $user  = $this->db->table('users')->where('email', 'editor@example.com')->get()->getRowArray();

        $this->assertSame(2, $roles);
        $this->assertSame($editor['id'], $user['role_id']);
    }
}
