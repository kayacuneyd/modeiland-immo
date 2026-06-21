<?= admin_component('page-header', [
    'title'        => lang('Blog.admin_title'),
    'action_label' => lang('Blog.new_post'),
    'action_href'  => site_url('admin/blog/new'),
]) ?>

<div class="card bg-base-100 shadow-sm rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="ck-table">
            <thead>
                <tr>
                    <th><?= lang('Blog.post_title') ?></th>
                    <th><?= lang('Blog.category') ?></th>
                    <th><?= lang('Blog.post_lang') ?></th>
                    <th><?= lang('Blog.post_status') ?></th>
                    <th><?= lang('Blog.post_published_at') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                <tr><td colspan="6" class="text-center py-8 text-base-content/40"><?= lang('Blog.no_posts') ?></td></tr>
                <?php else: ?>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td class="font-medium max-w-xs truncate"><?= esc($post['title']) ?></td>
                    <td class="text-sm text-base-content/60"><?= esc($post['category_name'] ?? '—') ?></td>
                    <td><span class="badge badge-outline badge-sm uppercase"><?= esc($post['lang']) ?></span></td>
                    <td>
                        <span class="<?= $post['status'] === 'published' ? 'ck-badge-published' : 'ck-badge-draft' ?>">
                            <?= $post['status'] === 'published' ? lang('Common.publish') : lang('Common.draft') ?>
                        </span>
                    </td>
                    <td class="text-sm text-base-content/50">
                        <?= $post['published_at'] ? date('d.m.Y', strtotime($post['published_at'])) : '—' ?>
                    </td>
                    <td>
                        <a href="<?= site_url('admin/blog/' . $post['id'] . '/edit') ?>"
                           class="btn btn-ghost btn-xs"><?= lang('Common.edit') ?></a>
                        <form action="<?= site_url('admin/blog/' . $post['id'] . '/delete') ?>" method="post" class="inline"
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
