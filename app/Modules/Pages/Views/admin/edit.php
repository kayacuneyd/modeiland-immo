<?php $isNew = $page === null; ?>
<?= admin_component('page-header', [
    'title'       => $pageTitle,
    'breadcrumbs' => [['label' => lang('Common.nav_pages'), 'href' => site_url('admin/pages')]],
]) ?>

<form action="<?= $isNew ? site_url('admin/pages') : site_url('admin/pages/' . $page['id']) ?>"
      method="post">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Ana içerik -->
        <div class="lg:col-span-2 space-y-4">
            <div class="card bg-base-100 shadow-sm rounded-2xl">
                <div class="card-body">
                    <?= component('form-field', [
                        'name'     => 'title',
                        'label'    => lang('Blog.post_title'),
                        'value'    => $page['title'] ?? old('title'),
                        'required' => true,
                    ]) ?>
                    <?= component('form-field', [
                        'name'   => 'slug',
                        'label'  => 'Slug',
                        'value'  => $page['slug'] ?? old('slug'),
                        'helper' => 'Boş bırakılırsa başlıktan otomatik oluşturulur.',
                    ]) ?>
                    <?= component('form-field', [
                        'name'  => 'content',
                        'label' => lang('Blog.post_content'),
                        'type'  => 'textarea',
                        'value' => $page['content'] ?? old('content'),
                        'rows'  => 12,
                    ]) ?>
                </div>
            </div>

            <!-- SEO -->
            <div class="card bg-base-100 shadow-sm rounded-2xl">
                <div class="card-body">
                    <h3 class="font-semibold text-sm text-base-content/60 uppercase tracking-wide mb-3">SEO</h3>
                    <?= component('form-field', [
                        'name'  => 'meta_title',
                        'label' => 'Meta Başlık',
                        'value' => $page['meta_title'] ?? old('meta_title'),
                    ]) ?>
                    <?= component('form-field', [
                        'name'  => 'meta_description',
                        'label' => 'Meta Açıklama',
                        'type'  => 'textarea',
                        'value' => $page['meta_description'] ?? old('meta_description'),
                        'rows'  => 3,
                    ]) ?>
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
                        'value'   => $page['status'] ?? 'draft',
                        'options' => ['draft' => lang('Common.draft'), 'published' => lang('Common.publish')],
                    ]) ?>
                    <?= component('form-field', [
                        'name'    => 'lang',
                        'label'   => lang('Blog.post_lang'),
                        'type'    => 'select',
                        'value'   => $page['lang'] ?? 'tr',
                        'options' => ['tr' => 'Türkçe', 'de' => 'Deutsch', 'en' => 'English'],
                    ]) ?>
                    <?= component('form-field', [
                        'name'  => 'sort_order',
                        'label' => 'Sıralama',
                        'type'  => 'number',
                        'value' => $page['sort_order'] ?? 0,
                    ]) ?>

                    <div class="pt-2">
                        <?= component('button', ['label' => lang('Common.save'), 'type' => 'submit', 'full' => true]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
