<?php

require __DIR__ . '/../../php/vendor/go1.autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatasetCommand extends Command
{
    private $http;

    public function __construct()
    {
        parent::__construct('main');

        $this
            ->addOption('user', null, InputOption::VALUE_OPTIONAL)
            ->addOption('pass', null, InputOption::VALUE_OPTIONAL)
            ->addOption('output', null, InputOption::VALUE_OPTIONAL)
            ->addArgument('portal-id', InputArgument::REQUIRED);

        $this->http = new Client(['cookies' => true]);
    }

    private function getJwt(InputInterface $input): string
    {
        $user = $input->getOption('user');
        $pass = $input->getOption('pass');
        $res = $this->http->post('https://staff-dev.go1.co/user/login', [
            'allow_redirects' => false,
            'form_params'     => [
                'username' => $user,
                'password' => $pass,
            ],
        ]);

        $cookieValue = explode('jwt=', $res->getHeader('Set-Cookie')[0])[1];
        $cookieValue = explode('; ', $cookieValue);

        return $cookieValue[0];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jwt = $this->getJwt($input);
        $portalId = $input->getArgument('portal-id');
        $dir = $input->getOption('output') ?: '/tmp/go1-dataset-' . uniqid();
        !is_dir($dir) && exec('mkdir -p ' . $dir);

        file_put_contents($dir . '/portal.json', $this->dump(__DIR__ . '/dataset/portal.php', $jwt, $portalId));
        file_put_contents($dir . '/enrolments.json', $this->dump(__DIR__ . '/dataset/accounts.php', $jwt, $portalId));
        file_put_contents($dir . '/accounts.json', $this->dump(__DIR__ . '/dataset/enrolments.php', $jwt, $portalId));
        file_put_contents($dir . '/learning-objects.json', $this->dump(__DIR__ . '/dataset/learning-objects.php', $jwt, $portalId));

        $output->writeln("Data is dumped to: {$dir}");
    }

    private function dump($pathToCode, $jwt, $portalId): string
    {
        $res = $this->http->post('https://staff-dev.go1.co/devel/php', [
            'headers'     => ['Cookie' => 'jwt=' . $jwt],
            'form_params' => [
                'code' => str_replace('$portalId', $portalId, file_get_contents($pathToCode)),
            ],
        ]);

        return $res->getBody()->getContents();
    }

}

$app = new Application('GO1 dataset', '1.0');
$app->add($cmd = new DatasetCommand());
$app->setDefaultCommand($cmd->getName());
$app->run();
