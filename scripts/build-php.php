<?php

namespace go1\monolith\scripts;

function buildComposerJson($pwd, $projects, $baseDir = 'php')
{
    $json = json_decode(file_get_contents("$pwd/php/composer.json"), true);
    foreach (array_keys($projects) as $service) {
        # @todo Remove this hack after updating mbosi-export service.
        if (($baseDir === 'php/libraries' && $service === 'report_helpers') || ($baseDir === 'php' && $service === 'mbosi-export')) {
            continue;
        }

        if ($service !== 'my-team') {
            if ($baseDir === 'php' && strpos($service, '-') !== false) {
                $namespace = 'go1\\' . str_replace('-', '', ucwords($service, '-')) . '\\';
            }
            else {
                $namespace = "go1\\$service\\";
            }

            $json['autoload']['psr-4'][$namespace] = [
                ($baseDir === 'php') ? "./$service" : "./libraries/$service",
                ($baseDir === 'php') ? "/app/$service" : "/app/libraries/$service",
            ];
        }

        if (file_exists("$pwd/$baseDir/$service/composer.json")) {
            $sub = json_decode(file_get_contents("$pwd/$baseDir/{$service}/composer.json"), true);
            if (!empty($sub['require'])) {
                foreach ($sub['require'] as $lib => $version) {
                    if (false === strpos($lib, 'go1/')) {
                        if (!in_array($lib, ['php', 'phpunit/phpunit', 'microservices/explore', 'symfony/yaml']) && (!isset($json['require'][$lib]) || version_compare($version, $json['require'][$lib]) > 0)) {
                            $json['require'][$lib] = $version;
                        }
                    }
                }
            }

            if (!empty($sub['repositories'])) {
                foreach ($sub['repositories'] as $name => $info) {
                    $json['repositories'][$name] = $info;
                }
            }

            if (!empty($sub['autoload']['psr-4'])) {
                foreach ($sub['autoload']['psr-4'] as $subNamespace => $subPath) {
                    if ($subNamespace !== $namespace) {
                        // Only add custom namespaces, namespace for service is
                        // already added.
                        $json['autoload']['psr-4'][$subNamespace] = [
                            $baseDir === 'php' ? "./$service/$subPath" : "./libraries/$service/$subPath",
                            $baseDir === 'php' ? "/app/$service/$subPath" : "/app/libraries/$service/$subPath",
                        ];
                    }
                }
            }

            // #quiz-rpc already defined a function that #quiz need.
            if (!empty($sub['autoload']['files']) && $service !== 'quiz') {
                foreach ($sub['autoload']['files'] as $filePath) {
                    $json['autoload']['files'][] = $baseDir === 'php' ? "./$service/$filePath" : "./libraries/$service/$filePath";
                }
                $json['autoload']['files'] = array_unique(array_values($json['autoload']['files']));
            }
        }

        passthru("mkdir -p $pwd/$baseDir/$service/vendor");
        $path = $baseDir === 'php' ? '/../..' : '/../../..';
        file_put_contents(
            "$pwd/$baseDir/$service/vendor/autoload.php",
            '<?php' . "\n\n"
            . 'if (is_file("/autoload/autoload.php")) return require_once "/autoload/autoload.php";' . "\n"
            . 'if (is_file(__DIR__ . "' . $path . '/vendor/autoload.php")) return require_once __DIR__ . "' . $path . '/vendor/go1.autoload.php";'
        );
    }

    ksort($json['autoload']['psr-4']);
    ksort($json['require']);
    $json = json_encode($json, JSON_PRETTY_PRINT);
    $json = str_replace('\/', '/', $json);
    file_put_contents("$pwd/php/composer.json", $json);
}

return function ($pwd, $home, $projects) {
    buildComposerJson($pwd, $projects['php'], 'php');
    buildComposerJson($pwd, $projects['php/libraries'], 'php/libraries');
    passthru("cd $pwd/php && composer install -vvv --no-scripts --ignore-platform-reqs -vvv");
};
