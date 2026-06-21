<?php
$name        = $name        ?? '';
$label       = $label       ?? '';
$type        = $type        ?? 'text';    // text|email|password|textarea|select|number|tel|url
$value       = $value       ?? '';
$placeholder = $placeholder ?? '';
$required    = $required    ?? false;
$error       = $error       ?? '';
$options     = $options     ?? [];        // select için: ['value' => 'label']
$rows        = $rows        ?? 4;
$helper      = $helper      ?? '';
?>
<div class="form-control w-full mb-4">
    <?php if ($label): ?>
    <label class="label" for="<?= esc($name) ?>">
        <span class="ck-label">
            <?= esc($label) ?>
            <?php if ($required): ?><span class="text-error ml-1">*</span><?php endif; ?>
        </span>
    </label>
    <?php endif; ?>

    <?php if ($type === 'textarea'): ?>
    <textarea id="<?= esc($name) ?>" name="<?= esc($name) ?>"
              class="ck-textarea <?= $error ? 'textarea-error' : '' ?>"
              rows="<?= (int) $rows ?>"
              placeholder="<?= esc($placeholder) ?>"
              <?= $required ? 'required' : '' ?>><?= esc($value) ?></textarea>

    <?php elseif ($type === 'select'): ?>
    <select id="<?= esc($name) ?>" name="<?= esc($name) ?>"
            class="select select-bordered w-full bg-base-100 <?= $error ? 'select-error' : '' ?>"
            <?= $required ? 'required' : '' ?>>
        <?php foreach ($options as $optVal => $optLabel): ?>
        <option value="<?= esc($optVal) ?>" <?= $value == $optVal ? 'selected' : '' ?>>
            <?= esc($optLabel) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <?php else: ?>
    <input type="<?= esc($type) ?>"
           id="<?= esc($name) ?>"
           name="<?= esc($name) ?>"
           value="<?= esc($value) ?>"
           placeholder="<?= esc($placeholder) ?>"
           class="ck-input <?= $error ? 'input-error' : '' ?>"
           <?= $required ? 'required' : '' ?>>
    <?php endif; ?>

    <?php if ($error): ?>
    <label class="label">
        <span class="label-text-alt text-error"><?= esc($error) ?></span>
    </label>
    <?php elseif ($helper): ?>
    <label class="label">
        <span class="label-text-alt text-base-content/50"><?= esc($helper) ?></span>
    </label>
    <?php endif; ?>
</div>
