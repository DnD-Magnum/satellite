<?php declare(strict_types=1);

namespace Kiboko\Component\Satellite\Cloud\Console\Command\Project;

use Gyroscops\Api;
use Kiboko\Component\Satellite;
use Kiboko\Component\Satellite\Cloud\AccessDeniedException;
use Symfony\Component\Console;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

final class CreateCommand extends Console\Command\Command
{
    protected static $defaultName = 'project:create';

    protected function configure(): void
    {
        $this->setDescription('Sends configuration to the Gyroscops API.');
        $this->addOption('url', 'u', mode: Console\Input\InputArgument::OPTIONAL, description: 'Base URL of the cloud instance', default: 'https://app.gyroscops.com');
        $this->addOption('beta', mode: Console\Input\InputOption::VALUE_NONE, description: 'Shortcut to set the cloud instance to https://beta.gyroscops.com');
        $this->addOption('ssl', mode: Console\Input\InputOption::VALUE_NEGATABLE, description: 'Enable or disable SSL');

        $this->addArgument('name', mode: Console\Input\InputArgument::REQUIRED, description: 'Project name');
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

        $context = new Satellite\Cloud\Context();

        $project = new Api\Model\Project();
        $project->setName($input->getArgument('name'));
        $project->setOrganization($context->organization()->asString());
        $project->setAuthorizations([]);
        $project->setUsers([]);

        try {
            $client->postProjectCollection($project);
        } catch (Api\Exception\PostProjectCollectionBadRequestException) {
            $style->error('Something went wrong while creating the project.');

            return self::FAILURE;
        }

        $style->success('The project has been successfully created.');

        return self::SUCCESS;
    }
}
