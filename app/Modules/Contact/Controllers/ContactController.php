<?php

namespace App\Modules\Contact\Controllers;

use App\Core\Controllers\BaseWebController;
use App\Core\Mail\MailService;
use App\Modules\Contact\Models\ContactMessageModel;

class ContactController extends BaseWebController
{
    public function show(): string
    {
        $this->setSeo(['title' => lang('Contact.title')]);

        return $this->render('App\Modules\Contact\Views\form', []);
    }

    public function submit(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        $rules = [
            'name'    => 'required|max_length[190]',
            'email'   => 'required|valid_email',
            'message' => 'required|min_length[10]',
        ];

        if (! $this->validate($rules)) {
            return $this->render('App\Modules\Contact\Views\form', [
                'errors' => $this->validator->getErrors(),
            ]);
        }

        $data = [
            'name'       => $this->request->getPost('name'),
            'email'      => $this->request->getPost('email'),
            'subject'    => $this->request->getPost('subject') ?: '',
            'message'    => $this->request->getPost('message'),
            'ip_address' => $this->request->getIPAddress(),
        ];

        $model = new ContactMessageModel();
        $model->insert($data);

        // Mail gönder (sessiz hata — mail config yanlışsa formu bozmasın)
        try {
            (new MailService())->sendContactNotification($data);
        } catch (\Throwable) {
            // Mail hatası sessizce loglanır
            log_message('error', 'Contact mail gönderilemedi: ' . json_encode($data));
        }

        return redirect()->to(site_url('contact/success'));
    }

    public function success(): string
    {
        $this->setSeo(['title' => lang('Contact.success_title')]);

        return $this->render('App\Modules\Contact\Views\success', []);
    }
}
