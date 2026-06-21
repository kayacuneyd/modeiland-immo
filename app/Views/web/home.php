<?= component('hero', [
    'headline'    => esc(setting('site.title', 'CekirdekCMS')),
    'subheadline' => esc(setting('site.tagline', 'Yeniden kullanılabilir CodeIgniter 4 CMS çekirdeği. Clone et, modül ekle, yayınla.')),
    'cta_label'   => 'Admin Paneli',
    'cta_href'    => site_url('admin'),
    'cta2_label'  => 'Blog',
    'cta2_href'   => site_url('blog'),
]) ?>

<!-- Özellikler -->
<?= component('section', [
    'title'    => 'Neden CekirdekCMS?',
    'subtitle' => 'Her müşteri projesini sıfırdan kurmak yerine bir kez inşa et, defalarca kullan.',
    'bg'       => 'base-200',
    'content'  => '
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        ' . component('card', [
            'title' => '⚡ 2 dakikada kurulum',
            'body'  => 'clone + php spark setup ile hazır. Migration, seed ve admin kullanıcısı otomatik oluşur.',
        ]) . '
        ' . component('card', [
            'title' => '🧩 Modüler mimari',
            'body'  => 'Core/Modules ayrımı. Her modül kendi dosyaları ile self-contained. Sil = tek klasörü sil.',
        ]) . '
        ' . component('card', [
            'title' => '🤖 Ajan dostu',
            'body'  => 'docs/ sözleşme dosyaları sayesinde yapay zeka ajanlar projeyi token harcamadan anlar.',
        ]) . '
    </div>',
]) ?>

<!-- Son yazılar -->
<?php if ($posts): ?>
<?= component('section', [
    'title'   => 'Son Yazılar',
    'content' => (function() use ($posts) {
        $html = '<div class="grid grid-cols-1 md:grid-cols-3 gap-6">';
        foreach ($posts as $post) {
            $html .= component('card', [
                'title'     => $post['title'],
                'body'      => $post['excerpt'] ?? '',
                'cta_label' => 'Devamını Oku',
                'cta_href'  => site_url('blog/' . $post['slug']),
            ]);
        }
        $html .= '</div>';
        $html .= '<div class="text-center mt-8">' . component('button', ['label' => 'Tüm Yazılar', 'href' => site_url('blog'), 'variant' => 'outline']) . '</div>';
        return $html;
    })(),
]) ?>
<?php endif; ?>

<!-- CTA band -->
<?= component('cta', [
    'headline'  => 'Projenize başlamaya hazır mısınız?',
    'body'      => 'CekirdekCMS\'yi klonlayın, brief verin, birlikte inşa edelim.',
    'cta_label' => 'İletişime Geçin',
    'cta_href'  => site_url('contact'),
    'bg'        => 'primary',
]) ?>
