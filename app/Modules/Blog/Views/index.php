<?= component('section', [
    'title'    => lang('Blog.title'),
    'subtitle' => lang('Blog.all_posts'),
    'content'  => (function() use ($posts) {
        if (empty($posts)) {
            return '<p class="text-center text-base-content/50 py-12">' . lang('Blog.no_posts') . '</p>';
        }
        $html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
        foreach ($posts as $post) {
            $html .= component('card', [
                'title'     => $post['title'],
                'body'      => $post['excerpt'] ?? '',
                'badge'     => $post['category_name'] ?? '',
                'cta_label' => lang('Blog.read_more'),
                'cta_href'  => site_url('blog/' . $post['slug']),
            ]);
        }
        $html .= '</div>';
        return $html;
    })(),
]) ?>
