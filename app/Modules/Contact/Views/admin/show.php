<?= admin_component('page-header', [
    'title'       => $pageTitle,
    'breadcrumbs' => [['label' => lang('Contact.admin_title'), 'href' => site_url('admin/contact')]],
]) ?>

<div class="card bg-base-100 shadow-sm rounded-2xl max-w-2xl">
    <div class="card-body">
        <dl class="space-y-4">
            <div class="grid grid-cols-3 gap-2">
                <dt class="text-sm font-medium text-base-content/50"><?= lang('Contact.from') ?></dt>
                <dd class="col-span-2 text-base-content"><?= esc($message['name']) ?> &lt;<?= esc($message['email']) ?>&gt;</dd>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <dt class="text-sm font-medium text-base-content/50"><?= lang('Contact.subject') ?></dt>
                <dd class="col-span-2 text-base-content"><?= esc($message['subject'] ?: '—') ?></dd>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <dt class="text-sm font-medium text-base-content/50"><?= lang('Contact.date') ?></dt>
                <dd class="col-span-2 text-base-content/70 text-sm"><?= date('d.m.Y H:i', strtotime($message['created_at'])) ?></dd>
            </div>
            <div class="divider my-2"></div>
            <div>
                <dt class="text-sm font-medium text-base-content/50 mb-2">Mesaj</dt>
                <dd class="bg-base-200 rounded-xl p-4 text-base-content whitespace-pre-wrap text-sm leading-relaxed">
                    <?= esc($message['message']) ?>
                </dd>
            </div>
        </dl>

        <div class="card-actions mt-6">
            <a href="<?= site_url('admin/contact') ?>" class="btn btn-ghost btn-sm rounded-full">
                ← <?= lang('Common.back') ?>
            </a>
            <a href="mailto:<?= esc($message['email']) ?>?subject=Re: <?= esc($message['subject'] ?? '') ?>"
               class="btn btn-primary btn-sm rounded-full">
                E-posta ile Yanıtla
            </a>
        </div>
    </div>
</div>
