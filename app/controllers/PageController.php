<?php
/**
 * app/controllers/PageController.php — Halaman publik.
 */

function page_home() {
    // Jika ada salespage yang ditandai untuk homepage, render full salespage itu di root domain.
    $st = db()->query("SELECT * FROM salespages WHERE show_home = 1 AND status = 'publish' ORDER BY id DESC LIMIT 1");
    $home_sales = $st->fetch();

    if ($home_sales && trim((string)$home_sales['html']) !== '') {
        view('layout', [
            'title'   => $home_sales['meta_title'] ?: $home_sales['title'],
            'metadesc'=> $home_sales['meta_desc'] ?: setting('seo_desc', 'Platform edukasi & membership untuk pengusaha travel Indonesia.'),
            'content' => 'salespage',
            'vars'    => [
                'sp' => $home_sales,
                'is_home_sales' => true,
                'page_facebook_pixel' => $home_sales['facebook_pixel_id'] ?? '',
                'body_class' => 'theme-home',
            ],
        ]);
        return;
    }

    $products = db()->query("SELECT * FROM products WHERE status = 'publish' ORDER BY id DESC LIMIT 6")->fetchAll();
    $latestPosts = db()->query("SELECT *
                                FROM posts
                                WHERE status = 'publish'
                                  AND (published_at IS NULL OR published_at <= NOW())
                                ORDER BY COALESCE(published_at, created_at) DESC, id DESC
                                LIMIT 3")->fetchAll();

    view('layout', [
        'title'   => setting('seo_title', setting('site_name', 'The Travel CEO')),
        'metadesc'=> setting('seo_desc', 'Platform edukasi & membership untuk pengusaha travel Indonesia.'),
        'content' => 'home',
        'vars'    => [
            'home_sales' => $home_sales,
            'products' => $products,
            'latest_posts' => $latestPosts,
            'page_facebook_pixel' => $home_sales['facebook_pixel_id'] ?? '',
        ],
    ]);
}

function page_salespage($slug) {
    $st = db()->prepare("SELECT * FROM salespages WHERE slug = ? AND status = 'publish'");
    $st->execute([$slug]);
    $sp = $st->fetch();
    if (!$sp) { http_response_code(404); flash('error', 'Halaman tidak ditemukan.'); redirect(url('home')); }

    view('layout', [
        'title'   => $sp['meta_title'] ?: $sp['title'],
        'metadesc'=> $sp['meta_desc'] ?: '',
        'content' => 'salespage',
        'vars'    => ['sp' => $sp, 'page_facebook_pixel' => $sp['facebook_pixel_id'] ?? ''],
    ]);
}

function page_products() {
    $products = db()->query("SELECT * FROM products WHERE status = 'publish' ORDER BY id DESC")->fetchAll();
    view('layout', [
        'title'   => 'Semua Kelas & Produk — ' . setting('site_name', 'The Travel CEO'),
        'content' => 'products',
        'vars'    => ['products' => $products],
    ]);
}

function page_blog() {
    $posts = db()->query("SELECT *
                          FROM posts
                          WHERE status = 'publish'
                            AND (published_at IS NULL OR published_at <= NOW())
                          ORDER BY COALESCE(published_at, created_at) DESC, id DESC")->fetchAll();
    view('layout', [
        'title'   => 'Blog — ' . setting('site_name', 'The Travel CEO'),
        'metadesc'=> 'Artikel terbaru, insight, dan update dari ' . setting('site_name', 'The Travel CEO') . '.',
        'content' => 'blog',
        'vars'    => ['posts' => $posts],
    ]);
}

function page_post($slug) {
    $st = db()->prepare("SELECT *
                         FROM posts
                         WHERE slug = ?
                           AND status = 'publish'
                           AND (published_at IS NULL OR published_at <= NOW())
                         LIMIT 1");
    $st->execute([$slug]);
    $post = $st->fetch();
    if (!$post) { http_response_code(404); flash('error', 'Artikel tidak ditemukan.'); redirect(url('blog')); }

    $related = db()->prepare("SELECT id, title, slug, excerpt, html, featured_image, published_at, created_at
                              FROM posts
                              WHERE id <> ?
                                AND status = 'publish'
                                AND (published_at IS NULL OR published_at <= NOW())
                              ORDER BY COALESCE(published_at, created_at) DESC, id DESC
                              LIMIT 3");
    $related->execute([(int)$post['id']]);

    view('layout', [
        'title'   => $post['meta_title'] ?: $post['title'],
        'metadesc'=> $post['meta_desc'] ?: ($post['excerpt'] ?: excerpt_text($post['html'], 160)),
        'content' => 'post',
        'vars'    => ['post' => $post, 'related_posts' => $related->fetchAll()],
    ]);
}

function page_access($slug) {
    require_member();
    $u = current_user();

    $st = db()->prepare("SELECT a.*, p.id AS product_id, p.title AS product_title, p.slug AS product_slug
                         FROM access_pages a
                         JOIN products p ON p.id = a.product_id
                         WHERE a.slug = ? AND a.status = 'publish' AND p.status = 'publish'");
    $st->execute([$slug]);
    $ap = $st->fetch();
    if (!$ap) { http_response_code(404); flash('error', 'Halaman akses tidak ditemukan.'); redirect(url('dashboard')); }

    $chk = db()->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND product_id = ?");
    $chk->execute([$u['id'], $ap['product_id']]);
    if (!$chk->fetchColumn()) {
        flash('error', 'Anda belum memiliki akses ke produk ini.');
        redirect(url('product', ['slug' => $ap['product_slug']]));
    }

    view('layout', [
        'title'   => $ap['meta_title'] ?: $ap['title'],
        'metadesc'=> $ap['meta_desc'] ?: '',
        'content' => 'access_page',
        'vars'    => ['ap' => $ap],
    ]);
}

function page_product($slug) {
    $st = db()->prepare("SELECT p.*, a.slug AS access_slug
                         FROM products p
                         LEFT JOIN access_pages a ON a.product_id = p.id AND a.status = 'publish'
                         WHERE p.slug = ? AND p.status = 'publish'");
    $st->execute([$slug]);
    $product = $st->fetch();
    if (!$product) { http_response_code(404); flash('error', 'Produk tidak ditemukan.'); redirect(url('products')); }

    $ls = db()->prepare("SELECT * FROM lessons WHERE product_id = ? ORDER BY sort, id");
    $ls->execute([$product['id']]);
    $lessons = $ls->fetchAll();

    // Apakah user sudah punya akses?
    $owned = false;
    if ($u = current_user()) {
        $c = db()->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND product_id = ?");
        $c->execute([$u['id'], $product['id']]);
        $owned = (bool)$c->fetchColumn();
    }
    $seatStats = product_seat_stats((int)$product['id']);

    view('layout', [
        'title'   => $product['title'] . ' — ' . setting('site_name', 'The Travel CEO'),
        'metadesc'=> $product['short_desc'],
        'content' => 'product',
        'vars'    => ['product' => $product, 'lessons' => $lessons, 'owned' => $owned, 'seat_stats' => $seatStats],
    ]);
}
