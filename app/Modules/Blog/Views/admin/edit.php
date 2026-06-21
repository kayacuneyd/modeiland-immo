<?php $isNew = $post === null; ?>
<?= admin_component('page-header', [
    'title'       => $pageTitle,
    'breadcrumbs' => [['label' => lang('Blog.admin_title'), 'href' => site_url('admin/blog')]],
]) ?>

<form action="<?= $isNew ? site_url('admin/blog') : site_url('admin/blog/' . $post['id']) ?>"
      method="post">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-4">
            <div class="card bg-base-100 shadow-sm rounded-2xl">
                <div class="card-body">
                    <?= component('form-field', ['name' => 'title',   'label' => lang('Blog.post_title'),   'value' => $post['title'] ?? old('title'),   'required' => true]) ?>
                    <?= component('form-field', ['name' => 'slug',    'label' => 'Slug',                    'value' => $post['slug']  ?? old('slug'),    'helper' => 'Boş bırakılırsa başlıktan oluşturulur.']) ?>
                    <?= component('form-field', ['name' => 'excerpt', 'label' => lang('Blog.post_excerpt'), 'value' => $post['excerpt'] ?? old('excerpt'), 'type' => 'textarea', 'rows' => 3]) ?>
                    <?= component('form-field', ['name' => 'content', 'label' => lang('Blog.post_content'), 'value' => $post['content'] ?? old('content'), 'type' => 'textarea', 'rows' => 14]) ?>
                </div>
            </div>

            <!-- SEO -->
            <div class="card bg-base-100 shadow-sm rounded-2xl">
                <div class="card-body">
                    <h3 class="font-semibold text-sm text-base-content/60 uppercase tracking-wide mb-3">SEO</h3>
                    <?= component('form-field', ['name' => 'meta_title',       'label' => 'Meta Başlık',   'value' => $post['meta_title'] ?? '']) ?>
                    <?= component('form-field', ['name' => 'meta_description', 'label' => 'Meta Açıklama', 'value' => $post['meta_description'] ?? '', 'type' => 'textarea', 'rows' => 3]) ?>
                </div>
            </div>
        </div>

        <!-- Yan panel -->
        <div class="space-y-4">
            <div class="card bg-base-100 shadow-sm rounded-2xl">
                <div class="card-body">
                    <?= component('form-field', [
                        'name'    => 'status',
                        'label'   => lang('Blog.post_status'),
                        'type'    => 'select',
                        'value'   => $post['status'] ?? 'draft',
                        'options' => ['draft' => lang('Common.draft'), 'published' => lang('Common.publish')],
                    ]) ?>
                    <?= component('form-field', [
                        'name'    => 'lang',
                        'label'   => lang('Blog.post_lang'),
                        'type'    => 'select',
                        'value'   => $post['lang'] ?? 'tr',
                        'options' => ['tr' => 'Türkçe', 'de' => 'Deutsch', 'en' => 'English'],
                    ]) ?>
                    <?= component('form-field', [
                        'name'    => 'category_id',
                        'label'   => lang('Blog.category'),
                        'type'    => 'select',
                        'value'   => $post['category_id'] ?? '',
                        'options' => $categories,
                    ]) ?>
                    <?= component('form-field', [
                        'name'  => 'published_at',
                        'label' => lang('Blog.post_published_at'),
                        'type'  => 'text',
                        'value' => $post['published_at'] ?? date('Y-m-d H:i:s'),
                        'helper' => 'YYYY-MM-DD HH:MM:SS',
                    ]) ?>
                    <?= component('form-field', ['name' => 'media_id', 'label' => lang('Blog.featured_image') . ' ID', 'type' => 'number', 'value' => $post['media_id'] ?? '']) ?>

                    <div class="pt-2">
                        <?= component('button', ['label' => lang('Common.save'), 'type' => 'submit', 'full' => true]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
