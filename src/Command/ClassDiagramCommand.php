<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Command;

use Jhofm\PhPuml\PhPumlException;
use Jhofm\PhPuml\Formatter\Formatter;
use Jhofm\PhPuml\Options\OptionInterface;
use Jhofm\PhPuml\Options\Options;
use Jhofm\PhPuml\Service\PhPumlService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class ClassDiagramCommand extends Command
{
    private const ARG_INPUT_PATH = 'input';
    private const ARG_OUTPUT_PATH = 'output';

    public function __construct(
        private readonly PhPumlService $phpumlService,
        private readonly Options $options,
        private readonly Formatter $formatter,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    public function configure(): void
    {
        $this->setName('ph-puml');
        $this->setDescription('Generates PlantUML class diagrams from PHP code');
        $this->addArgument(
            self::ARG_INPUT_PATH,
            InputArgument::OPTIONAL,
            'Directory path containing PHP code (absolute or relative)',
            '/src'
        );
        $this->addArgument(
            self::ARG_OUTPUT_PATH,
            InputArgument::OPTIONAL,
            'Output path (absolute or relative)',
            'php://stdout'
        );
        foreach ($this->options as $name => $option) {
            $mode = InputOption::VALUE_OPTIONAL;
            if ($option->isArray()) {
                $mode |= InputOption::VALUE_IS_ARRAY;
            }
            $this->addOption($name, $option->getShortName(), $mode, $option->getDescription(),  $option->getValue());
        }
    }

    /**
     * @throws PhPumlException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->options->setValues($input->getOptions());
        $puml = $this->phpumlService->generatePuml($input->getArgument(self::ARG_INPUT_PATH));
        $puml = $this->formatter->format($puml);
        $outPath = $input->getArgument(self::ARG_OUTPUT_PATH);
        if ($outPath === 'php://stdout') {
            $output->write($puml, false, Output::OUTPUT_RAW);
        } else {
            if (file_put_contents($outPath, $puml) === false) {
                throw new PhPumlException(sprintf('Output file "%s" is not writable.', $outPath));
            }
        }
        return 0;
    }
}
