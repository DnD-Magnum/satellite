<?php declare(strict_types=1);

namespace Kiboko\Component\Satellite\Cloud\Console\Command\Organization;

use Gyroscops\Api;
use Kiboko\Component\Satellite;
use Kiboko\Component\Satellite\Cloud\AccessDeniedException;
use Symfony\Component\Console;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

final class ListCommand extends Console\Command\Command
{
    protected static $defaultName = 'organization:list';

    protected function configure(): void
    {
        $this->setDescription('Sends configuration to the Gyroscops API.');
        $this->addOption('url', 'u', mode: Console\Input\InputArgument::OPTIONAL, description: 'Base URL of the cloud instance', default: 'https://app.gyroscops.com');
        $this->addOption('beta', mode: Console\Input\InputOption::VALUE_NONE, description: 'Shortcut to set the cloud instance to https://beta.gyroscops.com');
        $this->addOption('ssl', mode: Console\Input\InputOption::VALUE_NEGATABLE, description: 'Enable or disable SSL');
    }

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): int
    {
        $style = new Console\Style\SymfonyStyle(
            $input,
            $output,
        );

        if ($input->getOption('beta')) {
            $url = 'https://beta.gyroscops.com';
            $ssl = $input->getOption('ssl') ?? true;
        } else if ($input->getOption('url')) {
            $url = $input->getOption('url');
            $ssl = $input->getOption('ssl') ?? true;
        } else {
            $url = 'https://gyroscops.com';
            $ssl = $input->getOption('ssl') ?? true;
        }

        $auth = new Satellite\Cloud\Auth();
        try {
            $token = $auth->token($url);
        } catch (AccessDeniedException) {
            $style->error('Your credentials were not found, please run <info>cloud login</>.');
            return self::FAILURE;
        }

        $httpClient = HttpClient::createForBaseUri(
            $url,
            [
                'verify_peer' => $ssl,
                'auth_bearer' => $token
            ]
        );

        $psr18Client = new Psr18Client($httpClient);
        $client = Api\Client::create($psr18Client);

        $organizations = $client->getOrganizationCollection();

        $context = new Satellite\Cloud\Context();

        try {
            $currentOrganization = $context->organization()->asString();
        } catch (Satellite\Cloud\NoOrganizationSelectedException) {
            $currentOrganization = null;
        }

        $style->table(
            ['id', 'name', 'current'],
            array_map(
                fn (Api\Model\Organization $organization) => [
                    $organization->getId(),
                    $organization->getName(),
                    ((string) $organization->getId()) === $currentOrganization ? '*' : ''
                ],
                $organizations
            )
        );

        return self::SUCCESS;
    }
}
