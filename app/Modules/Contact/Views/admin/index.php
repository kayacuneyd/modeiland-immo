<?= admin_component('page-header', ['title' => lang('Contact.admin_title')]) ?>

<div class="card bg-base-100 shadow-sm rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="ck-table">
            <thead>
                <tr>
                    <th><?= lang('Contact.from') ?></th>
                    <th><?= lang('Contact.subject') ?></th>
                    <th><?= lang('Contact.date') ?></th>
                    <th><?= lang('Contact.status') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr><td colspan="5" class="text-center py-8 text-base-content/40"><?= lang('Contact.no_messages') ?></td></tr>
                <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <tr class="<?= ! $msg['is_read'] ? 'font-semibold' : '' ?>">
                    <td>
                        <p><?= esc($msg['name']) ?></p>
                        <p class="text-xs text-base-content/40"><?= esc($msg['email']) ?></p>
                    </td>
                    <td><?= esc($msg['subject'] ?: '—') ?></td>
                    <td class="text-sm text-base-content/50"><?= date('d.m.Y H:i', strtotime($msg['created_at'])) ?></td>
                    <td>
                        <span class="badge <?= $msg['is_read'] ? 'badge-outline' : 'badge-warning' ?> badge-sm">
                            <?= $msg['is_read'] ? lang('Contact.read') : lang('Contact.unread') ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= site_url('admin/contact/' . $msg['id']) ?>"
                           class="btn btn-ghost btn-xs"><?= lang('Common.view') ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
