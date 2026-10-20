<?php
declare(strict_types=1);

function render_header(string $title): void
{
    $user = current_user();
    $pageTitle = e($title);
    $brand = e(getenv('APP_NAME') ?: 'CatatUsaha');
    csrf_token();
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta name="theme-color" content="#f8f8f5">';
    echo '<title>' . $pageTitle . ' | ' . $brand . '</title>';
    echo '<link rel="stylesheet" href="' . e(url('assets/css/style.css')) . '"></head><body>';
    echo '<div class="shell">';
    if ($user !== null) {
        echo '<header class="topbar"><a class="brand" href="' . e(url('index.php')) . '"><span class="brand-mark">C</span><span>' . $brand . '</span></a>';
        echo '<nav class="main-nav" aria-label="Navigasi utama">';
        echo '<a href="' . e(url('index.php')) . '">Ringkasan</a>';
        echo '<a href="' . e(url('sales/index.php')) . '">Penjualan</a>';
        echo '<a href="' . e(url('expenses/index.php')) . '">Pengeluaran</a>';
        echo '<a href="' . e(url('reports/index.php')) . '">Laporan</a></nav>';
        echo '<div class="user-menu"><span>' . e($user['name']) . '</span><form action="' . e(url('logout.php')) . '" method="post">' . csrf_field() . '<button class="link-button" type="submit">Keluar</button></form></div></header>';
    }
    echo '<main class="main-content">';
}

function render_footer(): void
{
    echo '</main><footer class="footer"><span>CatatUsaha</span><span>Pencatatan sederhana, laporan lebih teratur.</span></footer></div>';
    echo '<script src="' . e(url('assets/js/app.js')) . '" defer></script></body></html>';
}

function page_heading(string $eyebrow, string $title, string $description = '', ?string $actionLabel = null, ?string $actionUrl = null): void
{
    echo '<div class="page-heading"><div><p class="eyebrow">' . e($eyebrow) . '</p><h1>' . e($title) . '</h1>';
    if ($description !== '') {
        echo '<p class="muted heading-description">' . e($description) . '</p>';
    }
    echo '</div>';
    if ($actionLabel !== null && $actionUrl !== null) {
        echo '<a class="button button-primary" href="' . e(url($actionUrl)) . '">' . e($actionLabel) . '</a>';
    }
    echo '</div>';
}

function field_error(array $errors, string $field): void
{
    if (isset($errors[$field])) {
        echo '<small class="field-error">' . e($errors[$field]) . '</small>';
    }
}
