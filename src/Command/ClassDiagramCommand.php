<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Command;

use Jhofm\PhPuml\Exception\PhPumlException;
use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Service\PhPuml;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClassDiagramCommand
 */
class ClassDiagramCommand extends Command
{
    private const ARG_INPUT_PATH_OR_PACKAGE = 'input';

    /** @var PhPuml */
    private $phpumlService;

    /**
     * PumlGenCommand constructor.
     *
     * @param PhPuml $phpumlService
     * @param string|null $name
     */
    public function __construct(
        PhPuml $phpumlService,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->phpumlService = $phpumlService;
    }

    /**
     * Command configuration
     */
    public function configure()
    {
        $this->setName('ph-puml');
        $this->setDescription('Generates PlantUML class diagrams from PHP code');
        $this->addArgument(
            self::ARG_INPUT_PATH_OR_PACKAGE,
            InputArgument::OPTIONAL,
            'directory path containing PHP code (absolute or relative)',
            '.'
        );
        $pumlOptions = Options::getDefaults();
        foreach ($pumlOptions as $option => $defaultValue) {
            $this->addOption($option, null, Options::getFlags($option), '', $defaultValue);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws PhPumlException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $options = Options::fromArray($input->getOptions());
        $puml = $this->phpumlService->generatePuml(
            $input->getArgument(self::ARG_INPUT_PATH_OR_PACKAGE),
            $options
        );
        $output->write($puml);
        return 0;
    }
}