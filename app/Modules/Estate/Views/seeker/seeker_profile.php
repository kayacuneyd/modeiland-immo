<?php /** @var array $seeker @var array|null $profile */ ?>
<div data-theme="modeiland" class="min-h-screen bg-base-100">

  <header class="bg-primary text-primary-content py-4 px-6">
    <div class="max-w-2xl mx-auto flex items-center justify-between">
      <span class="font-bold text-xl">modeiland</span>
      <div class="flex gap-4 text-sm">
        <a href="<?= site_url('seeker/panel') ?>" class="link link-hover text-primary-content/60">← Mein Bereich</a>
        <a href="<?= site_url('seeker/logout') ?>" class="link link-hover text-primary-content/60 text-xs">Abmelden</a>
      </div>
    </div>
  </header>

  <main class="max-w-2xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-primary mb-1">Mein Profil</h1>
    <p class="text-slate-500 text-sm mb-6">
      Ihr Profil wird für personalisierte Bewerbungsunterlagen verwendet.
      Je vollständiger, desto besser die generierten Texte.
    </p>

    <?php if (session()->has('success')): ?>
      <div class="alert alert-success mb-4"><span><?= esc(session('success')) ?></span></div>
    <?php endif ?>
    <?php if (session()->has('errors')): ?>
      <div class="alert alert-error mb-4">
        <ul class="list-disc list-inside text-sm">
          <?php foreach ((array) session('errors') as $e): ?>
            <li><?= esc($e) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
    <?php endif ?>

    <form method="post" action="<?= site_url('seeker/profil') ?>" class="card bg-base-100 shadow border border-base-300">
      <?= csrf_field() ?>
      <div class="card-body space-y-4">

        <div class="form-control">
          <label class="label"><span class="label-text font-medium">Vollständiger Name</span></label>
          <input type="text" name="name" maxlength="150"
                 value="<?= esc(old('name', $profile['name'] ?? '')) ?>"
                 class="input input-bordered" placeholder="Max Mustermann">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-control">
            <label class="label"><span class="label-text font-medium">Gewünschter Einzug</span></label>
            <input type="text" name="move_in_date" maxlength="20"
                   value="<?= esc(old('move_in_date', $profile['move_in_date'] ?? '')) ?>"
                   class="input input-bordered" placeholder="z.B. 01.03.2026 oder sofort">
          </div>
          <div class="form-control">
            <label class="label"><span class="label-text font-medium">Haushaltsgröße (Personen)</span></label>
            <input type="number" name="household_size" min="1" max="20"
                   value="<?= esc(old('household_size', $profile['household_size'] ?? '')) ?>"
                   class="input input-bordered" placeholder="1">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-control">
            <label class="label"><span class="label-text font-medium">Beruf / Tätigkeit</span></label>
            <input type="text" name="occupation" maxlength="100"
                   value="<?= esc(old('occupation', $profile['occupation'] ?? '')) ?>"
                   class="input input-bordered" placeholder="z.B. Softwareentwickler">
          </div>
          <div class="form-control">
            <label class="label">
              <span class="label-text font-medium">Nettoeinkommen ca. (€/Monat)</span>
            </label>
            <input type="number" name="income_range" min="0" step="100"
                   value="<?= esc(old('income_range', isset($profile['income_range_cents']) ? $profile['income_range_cents'] / 100 : '')) ?>"
                   class="input input-bordered" placeholder="2500">
            <p class="text-xs text-slate-400 mt-0.5">Nur für den generierten Bewerbungstext — wird nicht öffentlich angezeigt.</p>
          </div>
        </div>

        <div class="form-control">
          <label class="label cursor-pointer justify-start gap-3">
            <input type="checkbox" name="pets" value="1" class="checkbox checkbox-sm"
                   <?= old('pets', $profile['pets'] ?? 0) ? 'checked' : '' ?>>
            <span class="label-text">Haustiere vorhanden</span>
          </label>
        </div>

        <div class="form-control">
          <label class="label"><span class="label-text font-medium">Persönliche Notiz (optional)</span></label>
          <textarea name="notes" maxlength="1000" rows="4"
                    class="textarea textarea-bordered"
                    placeholder="z.B. Ruhige Mieter, keine Partys, gepflegter Umgang mit der Wohnung …"><?= esc(old('notes', $profile['notes'] ?? '')) ?></textarea>
          <p class="text-xs text-slate-400 mt-0.5">Fließt in den generierten Bewerbungstext ein.</p>
        </div>

        <div class="card-actions justify-end pt-2">
          <button type="submit" class="btn btn-primary">Profil speichern</button>
        </div>

      </div>
    </form>
  </main>

  <footer class="text-center text-xs text-slate-400 py-6 border-t border-base-200 mt-8">
    <a href="<?= site_url('impressum') ?>" class="link">Impressum</a> ·
    <a href="<?= site_url('datenschutz') ?>" class="link">Datenschutz</a>
  </footer>

</div>
