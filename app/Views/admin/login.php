<!DOCTYPE html>
<html lang="tr" data-theme="cekirdekcms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lang('Auth.login_title') ?> — <?= esc(setting('site.title', 'CekirdekCMS')) ?></title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>?v=<?= setting('app.css_version', '1') ?>">
    <meta name="robots" content="noindex,nofollow">
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">

<div class="card w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body p-8">

        <!-- Logo/Başlık -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-primary">
                <?= esc(setting('site.title', 'CekirdekCMS')) ?>
            </h1>
            <p class="text-base-content/50 text-sm mt-1"><?= lang('Auth.login_subtitle') ?></p>
        </div>

        <!-- Hata mesajı -->
        <?php if (session()->getFlashdata('error')): ?>
        <?= component('alert', ['type' => 'error', 'message' => session()->getFlashdata('error')]) ?>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
        <?= component('alert', ['type' => 'success', 'message' => session()->getFlashdata('success')]) ?>
        <?php endif; ?>

        <!-- Form -->
        <form action="<?= site_url('admin/login') ?>" method="post" class="space-y-2">
            <?= csrf_field() ?>

            <?= component('form-field', [
                'name'        => 'email',
                'label'       => lang('Auth.login_email'),
                'type'        => 'email',
                'value'       => old('email'),
                'placeholder' => 'admin@example.com',
                'required'    => true,
                'error'       => session()->getFlashdata('errors')['email'] ?? '',
            ]) ?>

            <?= component('form-field', [
                'name'     => 'password',
                'label'    => lang('Auth.login_password'),
                'type'     => 'password',
                'required' => true,
                'error'    => session()->getFlashdata('errors')['password'] ?? '',
            ]) ?>

            <div class="pt-2">
                <?= component('button', [
                    'label' => lang('Auth.login_submit'),
                    'type'  => 'submit',
                    'full'  => true,
                    'size'  => 'lg',
                ]) ?>
            </div>
        </form>

    </div>
</div>

</body>
</html>
