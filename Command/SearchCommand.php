<?php
declare(strict_types=1);

namespace DMK\DuplicateCheckBundle\Command;

use DMK\DuplicateCheckBundle\Async\Topics;
use DMK\DuplicateCheckBundle\Facade;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends Command
{
    private $producer;
    private $helper;
    private $facade;

    public function __construct(MessageProducerInterface $producer, Facade $facade, DoctrineHelper $doctrineHelper)
    {
        $this->producer = $producer;
        $this->helper = $doctrineHelper;
        $this->facade = $facade;

        parent::__construct(null);
    }

    protected function configure()
    {
        $this->setName('dmk:duplicate-check:search');
        $this->addOption('scheduled', null, InputOption::VALUE_NONE, 'Performs the check asynchronous');
        $this->addArgument('class', InputArgument::REQUIRED, 'The class of the entity');
        $this->addArgument('id', InputArgument::REQUIRED, 'The id of the entity');
        $this->setHelp(<<<'HELP'
The <info>%command.name%</info> performs an duplicate check for the given
entity.
HELP
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('class');
        $id = $input->getArgument('id');

        $entity = $this->helper->getEntity($class, $id);

        if ($input->getOption('scheduled')) {
            $this->producer->send(Topics::TOPIC_CHECK_SINGLE, [
                'class' => $class,
                'id' => $id,
            ]);

            $output->writeln(sprintf('Scheduled duplicate check for entity "%s"', $class));
            return 0;
        }

        $output->writeln(sprintf(
            'Perform duplicate check for class "%s"',
            $class
        ));

        $duplicates = $this->facade->perform($entity);

        $output->writeln(sprintf(
            'Found <info>%d</info> duplicates for the given entity.',
            count($duplicates)
        ));

        return 0;
    }
}
