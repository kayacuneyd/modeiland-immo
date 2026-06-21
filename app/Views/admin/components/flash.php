<?php
$success = session()->getFlashdata('success');
$error   = session()->getFlashdata('error');
$errors  = session()->getFlashdata('errors');
?>
<?php if ($success || $error || $errors): ?>
<div class="px-4 lg:px-8 pt-4">
    <?php if ($success): ?>
    <?= component('alert', ['type' => 'success', 'message' => $success, 'dismissible' => true]) ?>
    <?php endif; ?>

    <?php if ($error): ?>
    <?= component('alert', ['type' => 'error', 'message' => $error, 'dismissible' => true]) ?>
    <?php endif; ?>

    <?php if ($errors && is_array($errors)): ?>
    <div class="alert alert-error mb-4 relative">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <ul class="list-disc list-inside text-sm">
            <?php foreach ($errors as $err): ?>
            <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button onclick="this.parentElement.remove()" class="btn btn-ghost btn-xs absolute right-2 top-2">✕</button>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
