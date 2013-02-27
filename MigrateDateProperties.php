<?php
use Symfony\Component\Console\Command\Command;
use PHPCR\PropertyType;
use Jackalope\Session;
use Jackalope\Node;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrate Date fields with format('r') to format('Y-m-d H:i:s')
 *
 * @author Willem-Jan Zijderveld <wjzijderveld@gmail.com>
 */
class MigrateDateProperties extends Command
{
    /** @var bool */
    protected $dryRun;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    protected $output;

    /** @var array */
    protected $updates = array();

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('jackalope:dbal:migrate-date-properties')
            ->setDescription('Migrates dates to the new format')
            ->setDefinition(array(
                new InputOption('dry-run', 't', InputOption::VALUE_NONE,
                    'Instead of updateing, show the paths that would be updated'
                )
            ))
            ->setHelp(<<<EOT
Converts all Date properties in format 'r' to format 'Y-m-d H:i:s'
EOT
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dryRun = $input->getOption('dry-run');
        $this->output = $output;

        /** @var $session \Jackalope\Session */
        $session = $this->getHelper('phpcr')->getSession();

        $this->fixNode($session->getRootNode());

        if (!$this->dryRun) {
            $session->save();
            $totalProperties = 0;

            foreach ($this->updates as $update) {
                $totalProperties += count($update);
            }

            $output->writeln(sprintf('<info>Updated %d nodes with %d Date properties</info>', count($this->updates), $totalProperties));
        } else {
            if (count($this->updates)) {
                $output->writeln('Would have updated the following nodes and properties');
                foreach ($this->updates as $path => $properties) {
                    $output->writeln('<info>' . $path . '</info>');
                    foreach ($properties as $property) {
                        $output->writeln("\t" . $property);
                    }
                }
            } else {
                $output->writeln('No nodes found with Date properties');
            }
        }
    }

    protected function fixNode(Node $node)
    {
        $this->fixNodeProperties($node);

        foreach ($node as $child) {
            $this->fixNode($child);
        }
    }

    protected function fixNodeProperties(Node $node)
    {
        $properties = $node->getProperties();

        /** @var $property \Jackalope\Property */
        foreach ($properties as $property) {
            if ($property->getType() === \PHPCR\PropertyType::DATE) {
                if (!$this->dryRun) {
                    // Just mark property as modified
                    // The data retrieved from DB is correct, it just needs to be saved again in the new format
                    $property->setModified();
                }

                $this->updates[$node->getPath()][] = $property->getName() . sprintf(' (<comment>%s</comment> => <comment>%s</comment>)', $property->getValue()->format('r'), PropertyType::convertType($property->getValue(), PropertyType::STRING));
            }
        }
    }

}
