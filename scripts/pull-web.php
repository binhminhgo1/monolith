<?php
/**
 * Pull newest apiom-ui, fix domain & re-build it
 */

$baseDir = realpath(dirname(__DIR__));
$customFile = $baseDir . '/build.json';

if ( ! is_file($customFile)) {
    fwrite(STDERR, "Custom build config not found: $customFile");
    exit(1);
}

$custom = json_decode(file_get_contents($customFile), true);
if (empty($custom['features']['domain']) || empty($custom['features']['services']['apiom-ui']['branch'])) {
    fwrite(STDERR, 'Missing config $custom.features.domain or $custom.services.apiom-ui.branch');
    exit(1);
}

if ( ! in_array('-f', $argv)) {
    fwrite(STDERR, "DANGEROUS this script throws away all your apiom-ui uncommitted changes\nplease re-run with -f argument for confirm");
    exit(1);
}

$domain = $custom['features']['domain'];
chdir($baseDir . '/web/ui');
passthru('git reset --hard');
passthru("git pull origin {$custom['features']['services']['apiom-ui']['branch']} --rebase");

$fix = require __DIR__ . '/fix-web.php';
$fix($baseDir, $domain);

$build = require __DIR__ . '/build-web.php';
$build($baseDir);
