<?php

namespace App\Core\Controllers;

use App\Core\Media\MediaService;

class MediaAdminController extends BaseAdminController
{
    private MediaService $media;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->media = new MediaService();
    }

    public function index(): string
    {
        $page  = (int) ($this->request->getGet('page') ?: 1);
        $items = $this->media->getLibrary($page, 40);
        $total = $this->media->getCount();

        return $this->render('admin/media/index', [
            'pageTitle' => lang('Common.nav_media'),
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => 40,
        ]);
    }

    public function upload(): \CodeIgniter\HTTP\RedirectResponse|\CodeIgniter\HTTP\ResponseInterface
    {
        $file = $this->request->getFile('file');

        if (! $file) {
            return redirect()->back()->with('error', lang('Common.no_file'));
        }

        try {
            $result = $this->media->upload($file);

            // AJAX isteği ise JSON döndür
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'id'      => $result['id'],
                    'url'     => base_url($result['path']),
                    'thumb'   => media_url($result, 'thumb'),
                ]);
            }

            return redirect()->to(site_url('admin/media'))->with('success', lang('Common.uploaded'));
        } catch (\Throwable $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requirePermission('media.delete');
        $this->media->delete($id);

        return redirect()->to(site_url('admin/media'))->with('success', lang('Common.deleted'));
    }

    public function picker(): string
    {
        $items = $this->media->getLibrary(1, 60);

        return view('admin/media/_picker', [
            'items' => $items,
        ]);
    }
}
