<?php $errors = $errors ?? []; ?>
<?= component('section', [
    'title'    => lang('Contact.title'),
    'subtitle' => lang('Contact.subtitle'),
    'content'  => (function() use ($errors) {
        $html = '<div class="max-w-lg mx-auto">';

        if (! empty($errors)):
            foreach ($errors as $err):
                $html .= component('alert', ['type' => 'error', 'message' => esc($err), 'dismissible' => true]);
            endforeach;
        endif;

        $html .= '<form action="' . site_url('contact') . '" method="post" class="space-y-1">';
        $html .= csrf_field();
        $html .= component('form-field', ['name' => 'name',    'label' => lang('Contact.form_name'),    'required' => true]);
        $html .= component('form-field', ['name' => 'email',   'label' => lang('Contact.form_email'),   'type' => 'email', 'required' => true]);
        $html .= component('form-field', ['name' => 'subject', 'label' => lang('Contact.form_subject')]);
        $html .= component('form-field', ['name' => 'message', 'label' => lang('Contact.form_message'), 'type' => 'textarea', 'rows' => 6, 'required' => true]);
        $html .= '<div class="pt-2">' . component('button', ['label' => lang('Contact.form_submit'), 'type' => 'submit', 'full' => true]) . '</div>';
        $html .= '</form></div>';

        return $html;
    })(),
]) ?>
