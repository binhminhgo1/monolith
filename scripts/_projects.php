<?php

namespace go1\monolith\scripts;

$pwd = dirname(__DIR__);
$hasCustom = file_exists($pwd . '/build.json') ? true : false;

$gh = function ($path) use ($hasCustom) {
    # if custom, change
    #   from git@github.com:go1com/util.git
    #   to   https://github.com/go1com/util.git

    return !$hasCustom ? $path : str_replace('git@github.com:', 'https://github.com/', $path);
};

// @note all keys (e.g. app, ui, website, assessor) under lang (e.g. php, web, golang) must be unique.
return [
    'php'            => [
        'activity'       => 'git@code.go1.com.au:microservices/activity.git',
        'api'            => 'git@code.go1.com.au:microservices/api.git',
        'assessor'       => 'git@code.go1.com.au:microservices/assessor.git',
        'assignment'     => 'git@code.go1.com.au:microservices/assignment.git',
        'award'          => 'git@code.go1.com.au:microservices/award.git',
        'cloudinary'     => 'git@code.go1.com.au:microservices/cloudinary.git',
        'contract'       => 'git@code.go1.com.au:microservices/contract.git',
        'coupon'         => 'git@code.go1.com.au:microservices/coupon.git',
        'credit'         => 'git@code.go1.com.au:microservices/credit.git',
        'currency'       => 'git@code.go1.com.au:microservices/currency.git',
        'eck'            => 'git@code.go1.com.au:microservices/eck.git',
        'enrolment'      => 'git@code.go1.com.au:microservices/enrolment.git',
        'entity'         => 'git@code.go1.com.au:microservices/entity.git',
        'etc'            => 'git@code.go1.com.au:microservices/etc.git',
        'exim'           => 'git@code.go1.com.au:microservices/exim.git',
        'explore'        => 'git@code.go1.com.au:microservices/explore.git',
        'featuretoggle'  => 'git@code.go1.com.au:microservices/featuretoggles.git',
        'finder'         => 'git@code.go1.com.au:microservices/finder.git',
        'graphin'        => 'git@code.go1.com.au:microservices/graphin.git',
        'history'        => 'git@code.go1.com.au:microservices/history.git',
        'index'          => 'git@code.go1.com.au:microservices/index.git',
        'interactive-li' => 'git@code.go1.com.au:microservices/interactive-li.git',
        'launcher'       => 'git@code.go1.com.au:microservices/launcher.git',
        'lo'             => 'git@code.go1.com.au:microservices/lo.git',
        'lob'            => 'git@code.go1.com.au:microservices/lob.git',
        'low'            => 'git@code.go1.com.au:microservices/low.git',
        'lti-consumer'   => 'git@code.go1.com.au:microservices/lti-consumer.git',
        'mail'           => 'git@code.go1.com.au:microservices/mail.git',
        'mbosi-export'   => 'git@code.go1.com.au:microservices/mbosi-export.git',
        'migration'      => 'git@code.go1.com.au:microservices/migration.git',
        'my-team'        => 'git@code.go1.com.au:microservices/my-team.git',
        'note'           => 'git@code.go1.com.au:microservices/note.git',
        'notify'         => 'git@code.go1.com.au:microservices/notify.git',
        'oembed'         => 'git@code.go1.com.au:microservices/oembed.git',
        'onboard'        => 'git@code.go1.com.au:microservices/onboard.git',
        'payment'        => 'git@code.go1.com.au:microservices/payment.git',
        'portal'         => 'git@code.go1.com.au:microservices/portal.git',
        'po'             => 'git@code.go1.com.au:microservices/po.git',
        'quiz'           => 'git@code.go1.com.au:microservices/quiz.git',
        'quiz-rpc'       => 'git@code.go1.com.au:microservices/quiz-rpc.git',
        'report'         => 'git@code.go1.com.au:microservices/report.git',
        'report-data'    => 'git@code.go1.com.au:microservices/report-data.git',
        'rules'          => 'git@code.go1.com.au:microservices/rules.git',
        's3'             => 'git@code.go1.com.au:microservices/s3.git',
        'scheduler'      => 'git@code.go1.com.au:microservices/scheduler.git',
        'scorm'          => 'git@code.go1.com.au:microservices/scorm.git',
        'scraping'       => 'git@code.go1.com.au:microservices/scraping.git',
        'share'          => 'git@code.go1.com.au:microservices/share.git',
        'sms'            => 'git@code.go1.com.au:microservices/sms.git',
        'social'         => 'git@code.go1.com.au:microservices/social.git',
        'sso'            => 'git@code.go1.com.au:microservices/sso.git',
        'staff'          => 'git@code.go1.com.au:microservices/staff.git',
        'subscription'   => 'git@code.go1.com.au:microservices/subscription.git',
        'support'        => 'git@code.go1.com.au:microservices/support.git',
        'user'           => 'git@code.go1.com.au:microservices/user.git',
        'vote'           => 'git@code.go1.com.au:microservices/vote.git',
    ],
    'php/libraries'  => [
        # 'stash'          => 'git@code.go1.com.au:microservices/stash.git',
        'app'            => $gh('git@github.com:go1com/app.git'),
        'command'        => $gh('git@github.com:go1com/command_bus.git'),
        'edge'           => $gh('git@github.com:go1com/edge.git'),
        'flood'          => $gh('git@github.com:go1com/flood.git'),
        'jwt_middleware' => $gh('git@github.com:go1com/JwtMiddleware.git'),
        'kv'             => $gh('git@github.com:go1com/kv.git'),
        'middleware'     => $gh('git@github.com:go1com/middlewares.git'),
        'neo4j_builder'  => $gh('git@github.com:go1com/neo4j_builder.git'),
        'report_helpers' => $gh('git@github.com:go1com/report_helpers.git'),
        'util'           => $gh('git@github.com:go1com/util.git'),
        'util_es'        => $gh('git@github.com:go1com/util_es.git'),
        'util_dataset'   => $gh('git@github.com:go1com/util_dataset.git'),
    ],
    'drupal'         => [
        'accounts' => 'git@code.go1.com.au:go1/accounts.git',
        'gc'       => 'git@code.go1.com.au:gc/gocatalyze.git',
    ],
    'golang'         => [
        # Please update build-go.php
        # The projects are managed by glide.
    ],
    'web'            => [
        'report-component' => 'git@code.go1.com.au:angularjs/report-component.git',
        'report-monitor'   => 'git@code.go1.com.au:web/report-monitor.git',
        'staff-reports'    => 'git@code.go1.com.au:angularjs/staff-reports.git',
        'ui'               => 'git@code.go1.com.au:apiom/apiom-ui.git',
        'website'          => 'git@code.go1.com.au:web/go1web.git',
    ],
    'infrastructure' => [
        'cron'          => 'git@code.go1.com.au:microservices/cron.git',
        'deploy_helper' => $gh('git@github.com:go1com/deploy_helper.git'),
        'docker-php'    => 'https://github.com/go1com/docker-php.git',
        'ecs'           => 'git@code.go1.com.au:go1/launch-configuration.git',
        'goweb'         => 'git@code.go1.com.au:microservices/goweb.git',
        'haproxy'       => 'git@code.go1.com.au:go1/haproxy.git',
        'wait-for-it'   => 'https://github.com/vishnubob/wait-for-it.git',
    ],
    'nodejs'         => [
        # @TODO: realtime
    ],
    'resources'      => [
        'documentation' => 'git@code.go1.com.au:go1/documentation.git',
    ],
];

# ---------------------
# Disabled
# ---------------------
# PHP
# ---
#   'algolia'  => 'git@code.go1.com.au:microservices/algolia.git',
#   'batch'    => 'git@code.go1.com.au:microservices/batch.git',
#   'console'  => 'git@code.go1.com.au:go1/console.git',
#   'endpoint' => 'git@code.go1.com.au:microservices/endpoint.git',
#   'lib'      => 'git@code.go1.com.au:microservices/lib.git',
#   'natero'   => 'git@code.go1.com.au:microservices/natero.git',
#   'queue'    => 'git@code.go1.com.au:microservices/queue.git',
#   'status'   => 'git@code.go1.com.au:microservices/status.git',
#   'uptime'   => 'git@code.go1.com.au:microservices/uptime.git',
#   'workshop' => 'git@code.go1.com.au:microservices/workshop.git',
#   'video'    => 'git@code.go1.com.au:microservices/video.git',
# NodeJS
# ---
#   'report' => 'git@code.go1.com.au:microservices/report-index.git',
#
# infrastructure
# ---
#   'memcached'     => 'git@code.go1.com.au:server/memcached.git',
