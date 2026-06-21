<?php

namespace App\Modules\Contact\Controllers\Admin;

use App\Core\Controllers\BaseAdminController;
use App\Modules\Contact\Models\ContactMessageModel;

class ContactAdminController extends BaseAdminController
{
    private ContactMessageModel $model;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->model = new ContactMessageModel();
    }

    public function index(): string
    {
        $page     = (int) ($this->request->getGet('page') ?: 1);
        $messages = $this->model->getAllPaginated($page, 20);

        return $this->render('App\Modules\Contact\Views\admin\index', [
            'pageTitle' => lang('Contact.admin_title'),
            'messages'  => $messages,
        ]);
    }

    public function show(int $id): string
    {
        $message = $this->model->find($id);
        if (! $message) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        if (! $message['is_read']) {
            $this->model->markAsRead($id);
        }

        return $this->render('App\Modules\Contact\Views\admin\show', [
            'pageTitle' => lang('Contact.from') . ': ' . $message['name'],
            'message'   => $message,
        ]);
    }
}
