<?= admin_component('page-header', ['title' => lang('Common.nav_settings')]) ?>

<form action="<?= site_url('admin/settings') ?>" method="post">
    <?= csrf_field() ?>

    <?php
    $groupLabels = [
        'general' => lang('Common.settings_general'),
        'mail'    => lang('Common.settings_mail'),
        'seo'     => lang('Common.settings_seo'),
        'footer'  => lang('Common.settings_footer'),
        'contact' => lang('Common.settings_contact'),
    ];
    ?>

    <?php foreach ($grouped as $group => $settings): ?>
    <div class="card bg-base-100 shadow-sm rounded-2xl mb-6">
        <div class="card-body">
            <h2 class="card-title text-base font-semibold text-primary border-b border-base-200 pb-3 mb-4">
                <?= esc($groupLabels[$group] ?? ucfirst($group)) ?>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                <?php foreach ($settings as $row):
                    $fieldName = str_replace('.', '__', $row['key']);
                    $type      = $row['type'] === 'bool' ? 'checkbox' : ($row['key'] === 'seo.robots_txt' ? 'textarea' : 'text');
                ?>
                <?php if ($row['type'] === 'bool'): ?>
                <div class="form-control mb-4">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="<?= esc($fieldName) ?>"
                               class="toggle toggle-primary"
                               value="1"
                               <?= $row['value'] ? 'checked' : '' ?>>
                        <span class="ck-label"><?= esc($row['label'] ?? $row['key']) ?></span>
                    </label>
                </div>
                <?php elseif ($type === 'textarea'): ?>
                <div class="col-span-2">
                    <?= component('form-field', [
                        'name'  => $fieldName,
                        'label' => $row['label'] ?? $row['key'],
                        'type'  => 'textarea',
                        'value' => $row['value'] ?? '',
                        'rows'  => 4,
                    ]) ?>
                </div>
                <?php elseif ($row['type'] === 'string' && str_contains($row['key'], 'pass')): ?>
                <?= component('form-field', [
                    'name'   => $fieldName,
                    'label'  => $row['label'] ?? $row['key'],
                    'type'   => 'password',
                    'value'  => $row['value'] ?? '',
                    'helper' => lang('Common.settings_pass_hint'),
                ]) ?>
                <?php else: ?>
                <?= component('form-field', [
                    'name'  => $fieldName,
                    'label' => $row['label'] ?? $row['key'],
                    'type'  => $row['type'] === 'int' ? 'number' : 'text',
                    'value' => $row['value'] ?? '',
                ]) ?>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="flex gap-3 mt-2">
        <?= component('button', ['label' => lang('Common.save'), 'type' => 'submit']) ?>
        <a href="<?= site_url('admin/settings/cache/clear') ?>"
           class="btn btn-ghost btn-sm rounded-full"
           onclick="return confirm('<?= lang('Common.cache_clear_confirm') ?>')">
            <?= lang('Common.cache_clear') ?>
        </a>
    </div>
</form>
