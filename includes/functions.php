<?php
/**
 * Build an absolute URL that matches your project base path.
 * Example: app_url('dashboard.php') => /2nd-yr-2nd-sem/electricity-billing/dashboard.php
 */
function app_url(string $path = ''): string {
    $base = '/electricity-billing';  // <-- adjust only if your folder name changes
    $path = ltrim($path, '/');                       // remove any leading slash
    return $path === '' ? $base : $base . '/' . $path;
}