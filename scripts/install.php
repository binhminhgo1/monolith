<?php

namespace go1\monolith;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeTypes;
use go1\util\portal\PortalHelper;
use go1\util\user\Roles;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Ramsey\Uuid\Uuid;

require_once __DIR__ . '/../php/vendor/go1.autoload.php';
require_once __DIR__ . '/../php/user/domain/password.php';

/** @var Connection $con */
/** @var Connection $db */
$accountsName = 'accounts-dev.gocatalyze.com';
$client = new Client;
$pwd = dirname(__DIR__);
$custom = $pwd . '/build.json';
$custom = is_file($custom) ? json_decode(file_get_contents($custom), true) : [];
$instance = $custom['features']['domain'] ?? 'default.go1.local';
$mail = $custom['features']['admin']['mail'] ?? 'staff@go1.co';
$withScorm = !empty($custom['scorm']);

$con = call_user_func(require $pwd . '/scripts/wait.php', $withScorm);

$databases = $con->getSchemaManager()->listDatabases();
!in_array('go1_dev', $databases) && $con->getSchemaManager()->createDatabase('go1_dev');
!in_array('quiz_dev', $databases) && $con->getSchemaManager()->createDatabase('quiz_dev');
!in_array('scorm_dev', $databases) && $con->getSchemaManager()->createDatabase('scorm_dev');
!in_array('realtime_dev', $databases) && $con->getSchemaManager()->createDatabase('realtime_dev');

call_user_func(require $pwd . '/scripts/install/realtime/install.php', $c['dbs']['realtime']);

# ---------------------
# POST $service/install
# ---------------------
function serviceInstall(Client $client, $name)
{
    $retries = 0;
    $MAX_TRY = 5;

    while (1) {
        try {
            $client->get("http://localhost/GO1/{$name}");
            $client->get($url = "http://localhost/GO1/{$name}/install", ['http_errors' => false]);
            $client->post($url, ['http_errors' => false]);

            return;
        }
        catch (\Exception $e) {
            if ($e instanceof ConnectException || false !== strpos($e->getMessage(), 'Connection reset')) {
                if ($retries > $MAX_TRY) {
                    echo "[$name] give up.";

                    return;
                }

                $retries += 1;
                $t = pow(2, $retries);

                echo " + Waiting {$t}s\n";
                sleep($t);
            }
            else {
                echo " + [{$name}] Unexpected exception: " . $e->getMessage() . ". skipped\n";

                return;
            }
        }
    }
}

$projects = require __DIR__ . '/_projects.php'; # Make sure we have database for all services
$installExcluded = ['console', 'lti-consumer', 'mbosi-export'];
foreach (array_keys($projects['php']) as $name) {
    if (in_array($name, $installExcluded)) {
        continue;
    }

    echo "[install] GET|POST http://localhost/GO1/{$name}/install\n";
    serviceInstall($client, $name);
}

echo "[install] POST http://localhost/GO1/staff/api/install\n";
$client->post("http://localhost/GO1/staff/api/install", ['http_errors' => false]);

# ---------------------
# Create portals
# ---------------------
$db = $c['dbs']['core'];
createPortal($db, $instance);
createPortal($db, $accountsName);

# ---------------------
# Create user for #staff.
#
# TODO: Publish message to rabbitMQ.
# ---------------------
if (!$db->fetchColumn("SELECT 1 FROM gc_user WHERE mail = ?", [$mail])) {
    $password = $custom['features']['admin']['password'] ?? 'root';
    $userRow = [
        'uuid'         => Uuid::uuid4()->toString(),
        'instance'     => $accountsName,
        'profile_id'   => 1,
        'mail'         => $mail,
        'password'     => _password_crypt('sha512', $password, _password_generate_salt(10)),
        'created'      => $now = time(),
        'access'       => $now,
        'login'        => $now,
        'status'       => 1,
        'first_name'   => $custom['features']['admin']['first_name'] ?? 'Staff',
        'last_name'    => $custom['features']['admin']['last_name'] ?? 'Local',
        'data'         => json_encode(['roles' => [Roles::ROOT, Roles::DEVELOPER]]),
        'timestamp'    => $now,
        'allow_public' => 0,
    ];

    $accountRow = ['instance' => $instance, 'uuid' => Uuid::uuid4()->toString(), 'data' => json_encode(['roles' => [Roles::ADMIN]])] + $userRow;
    $db->insert('gc_user', $userRow);
    $userId = $db->lastInsertId('gc_user');
    $db->insert('gc_user', $accountRow);
    $accountId = $db->lastInsertId('gc_user');

    # EdgeHelper::link($db, EdgeTypes::HAS_ACCOUNT);
    $db->insert('gc_ro', [
        'type'      => EdgeTypes::HAS_ACCOUNT,
        'source_id' => $userId,
        'target_id' => $accountId,
        'weight'    => 0,
        'data'      => json_encode(['source' => 'monolith']),
    ]);
}

installThroughContainers($withScorm);

function createPortal(Connection $db, string $name)
{
    if (!$db->fetchColumn('SELECT 1 FROM gc_instance WHERE title = ?', [$name])) {
        echo "[install] Creating portal {$name}\n";
        $db->insert('gc_instance', [
            'title'      => $name,
            'status'     => 1,
            'is_primary' => 1,
            'version'    => PortalHelper::STABLE_VERSION,
            'timestamp'  => $now = time(),
            'created'    => $now,
            'data'       => json_encode([
                'author'        => 'admin@' . $name,
                'configuration' => [
                    'is_virtual'                             => 1,
                    'user_invite'                            => 1,
                    PortalHelper::FEATURE_SEND_WELCOME_EMAIL => 1,
                ],
                'features'      => [
                    'marketplace' => true,
                    'user_invite' => true,
                    'auth0'       => false,
                ],
                'user_plan'     => [
                    'license' => 10,
                    'price'   => 3620,
                    'product' => 'marketplace',
                ],
            ]),
        ]);

        echo "[install] Creating user.0 and user.1 for portal {$name}\n";
        $db->transactional(
            function () use ($db, $name) {
                $db->insert('gc_user', $row = [
                    'uuid'         => $privateKey = Uuid::uuid4()->toString(),
                    'instance'     => $name,
                    'profile_id'   => null,
                    'mail'         => "user.1@{$name}",
                    'password'     => md5($privateKey),
                    'created'      => time(),
                    'login'        => time(),
                    'access'       => time(),
                    'status'       => 1,
                    'first_name'   => 'Key',
                    'last_name'    => 'Private',
                    'allow_public' => 0,
                    'data'         => '[]',
                    'timestamp'    => time(),
                ]);

                $db->insert('gc_user', $row = [
                    'uuid'         => $publicKey = Uuid::uuid4()->toString(),
                    'instance'     => $name,
                    'profile_id'   => null,
                    'mail'         => "user.0@{$name}",
                    'password'     => md5($publicKey),
                    'created'      => time(),
                    'login'        => time(),
                    'access'       => time(),
                    'status'       => 1,
                    'first_name'   => 'Key',
                    'last_name'    => 'Public',
                    'allow_public' => 0,
                    'data'         => '[]',
                    'timestamp'    => time(),
                ]);
            }
        );
    }
}

function installThroughContainers($withScorm = false)
{
    echo "[install] Install quiz database\n";
    passthru('docker exec monolith_web_1 /app/quiz/bin/console migrations:migrate --no-interaction -e monolith');
    if ($withScorm) {
        echo "[install] Install scormengine database\n";
        passthru('docker exec monolith_scormengine_1 /install.sh');
    }
}
