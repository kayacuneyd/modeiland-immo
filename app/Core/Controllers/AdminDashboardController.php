<?php

namespace App\Core\Controllers;

class AdminDashboardController extends BaseAdminController
{
    public function index(): string
    {
        $db = db_connect();

        $stats = [
            'pages'    => $db->table('pages')->where('deleted_at IS NULL', null, false)->where('status', 'published')->countAllResults(),
            'posts'    => $db->table('posts')->where('deleted_at IS NULL', null, false)->where('status', 'published')->countAllResults(),
            'messages' => $db->table('contact_messages')->countAllResults(),
            'media'    => $db->table('media')->where('deleted_at IS NULL', null, false)->countAllResults(),
        ];

        return $this->render('admin/dashboard', [
            'pageTitle' => lang('Common.nav_dashboard'),
            'stats'     => $stats,
        ]);
    }
}
