<?= component('section', [
    'content' => '<div class="text-center max-w-md mx-auto py-12">
        <div class="text-success mb-6">
            <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-base-content mb-4">' . lang('Contact.success_title') . '</h1>
        <p class="text-base-content/60 mb-8">' . lang('Contact.success_text') . '</p>
        ' . component('button', ['label' => lang('Contact.back'), 'href' => site_url('/'), 'variant' => 'outline']) . '
    </div>',
]) ?>
