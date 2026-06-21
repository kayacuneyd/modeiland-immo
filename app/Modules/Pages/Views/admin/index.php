<?= admin_component('page-header', [
    'title'        => lang('Common.nav_pages'),
    'action_label' => lang('Common.new_page'),
    'action_href'  => site_url('admin/pages/new'),
]) ?>

<div class="card bg-base-100 shadow-sm rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="ck-table">
            <thead>
                <tr>
                    <th><?= lang('Blog.post_title') ?></th>
                    <th><?= lang('Blog.post_lang') ?></th>
                    <th><?= lang('Blog.post_status') ?></th>
                    <th><?= lang('Common.edit') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pages)): ?>
                <tr><td colspan="4" class="text-center py-8 text-base-content/40"><?= lang('Common.no_media') ?></td></tr>
                <?php else: ?>
                <?php foreach ($pages as $page): ?>
                <tr>
                    <td class="font-medium"><?= esc($page['title']) ?></td>
                    <td><span class="badge badge-outline badge-sm uppercase"><?= esc($page['lang']) ?></span></td>
                    <td>
                        <span class="<?= $page['status'] === 'published' ? 'ck-badge-published' : 'ck-badge-draft' ?>">
                            <?= $page['status'] === 'published' ? lang('Common.publish') : lang('Common.draft') ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= site_url('admin/pages/' . $page['id'] . '/edit') ?>"
                           class="btn btn-ghost btn-xs"><?= lang('Common.edit') ?></a>
                        <form action="<?= site_url('admin/pages/' . $page['id'] . '/delete') ?>" method="post" class="inline"
                              onsubmit="return confirm('<?= lang('Common.delete_confirm') ?>')">
                            <?= csrf_field() ?>
                            <button class="btn btn-ghost btn-xs text-error"><?= lang('Common.delete') ?></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
