<?php

namespace go1\monolith\scripts;

return function ($pwd, $domain) {
    $files = [
        $pwd . '/web/ui/app/scripts/constants/monolith.json',
        $pwd . '/web/website/env/monolith.json',
        $pwd . '/web/ui/app/apps/core/services/instance.repository.js',
        $pwd . '/web/ui/app/scripts/supported.js',
    ];
    $patterns = [
        '(^\s*"default_domain":\s*)"[^"]+"\s*,'  => '$1"' . $domain . '",',
        'localhost([/:][^"]+)"'                  => $domain . '$1"',
        '(whiteDomainList\s*=[^\]]+)(.+$)'       => '$1, "' . $domain . '"$2',
        '\'https://api.go1.co([^\']+)\''         => '\'http://' . $domain . '/GO1$1\'',
        '(apiomGlobal.GO1_DOMAINS\s*=\s*\[\s*)$' => '$1\'' . $domain . '\',',
    ];

    foreach ($files as $file) {
        if ( ! is_file($file)) {
            fwrite(STDERR, "File not found: $file\n");
            continue;
        }
        $content = file_get_contents($file);
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace('~' . $pattern . '~m', $replacement, $content);
        }
        file_put_contents($file, $content);
    }
};
