<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Command;

use DirectoryIterator;
use Qore\Qore;
use Qore\SynapseManager\Artificer\Service\ServiceArtificer;
use Qore\SynapseManager\Structure\Entity\SynapseService;
use Qore\SynapseManager\Structure\Entity\SynapseServiceForm;
use Qore\SynapseManager\SynapseManager;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class: {command}
 * Documentation: https://symfony.com/doc/current/console.html
 * @see Symfony\Component\Console\Command\Command
 */
class CodeBuilder extends SymfonyCommand
{
    /**
     * defaultName
     *
     * @var string
     */
    protected static $defaultName = 'synapse:build';

    /**
     * input
     *
     * @var mixed
     */
    private $input = null;

    /**
     * output
     *
     * @var mixed
     */
    private $output = null;

    /**
     * tepmlateTypes
     *
     * @var mixed
     */
    private $templateTypes = [
        'service' => '{SynapseServiceNamespace}\\{SynapseServiceClass}',
        'entity' => '{SynapseNamespace}\\{SynapseClass}',
        'config' => '{SynapseServiceNamespace}\\ConfigProvider',
        'form.entity' => '{SynapseServiceNamespace}\\Forms\\{ServiceFormClass}',
        'form.hidden_selection' => '{SynapseServiceNamespace}\\Forms\\{ServiceFormClass}',
        'form.multiple_selection' =>  '{SynapseServiceNamespace}\\Forms\\{ServiceFormClass}',
    ];

    /**
     * templates
     *
     * @var mixed
     */
    private $templates = [];

    /**
     * configure
     *
     */
    protected function configure()
    {
        $this->setDescription('Генерация базовой инфраструктуры кода для заданного сервиса синапса')
            ->addArgument('synapse-service', InputArgument::OPTIONAL, 'Целевой сервис синапса, код которого необходимо сгенерировать')
            ->addOption('template', 't', InputOption::VALUE_OPTIONAL, 'Шаблон инфраструктуры сервиса. Чтобы просмотреть список доступных шаблонов используйте опцию `templates-list`', null)
            ->addOption('templates-list', 'tl', InputOption::VALUE_OPTIONAL, 'Список доступных шаблонов', null);
    }

    /**
     * execute
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $_input, OutputInterface $_output)
    {
        $this->input = $_input;
        $this->output = $_output;

        # - Get synapse service
        $synapseService = $this->getSynapseService();
        # - Get template for code building
        $serviceTemplate = $this->getTemplate();
        # - Get synpase namespace
        $namespace = $this->getSynapseNamespace();
        # - Breakline
        $this->output->writeln(['-------------------------------------------------']);

        $replacements = $this->prepareDataForTemplates($synapseService, $namespace);
        # - Generate files
        foreach ($serviceTemplate as $type => $template) {
            if ($type === 'service') {
                $classname = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $this->templateTypes[$type]
                );

                $result = ! file_exists($classfile = $this->getFileByClassname($classname)) && file_put_contents($classfile, str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $template
                ));

                $this->output->writeln(
                    $result ? [
                        "<info>Generated Synapse:Service class</info>\n" . $classname,
                        "<info>Path of class file</info>\n" . realpath($classfile),
                        "-------------------------------------------------",
                    ] : [
                        "<comment>Skipped Synapse:Service class because file exists</comment>\n" . $classname,
                        "<comment>Path of class file</comment>\n" . realpath($classfile),
                        "-------------------------------------------------",
                    ]
                );
            } elseif ($type === 'entity') {
                $classname = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $this->templateTypes[$type]
                );

                $result = ! file_exists($classfile = $this->getFileByClassname($classname)) && file_put_contents($classfile, str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    $template
                ));

                $this->output->writeln(
                    $result ? [
                        "<info>Generated SynapseEntity class</info>\n" . $classname,
                        "<info>Path of class file</info>\n" . realpath($classfile),
                        "-------------------------------------------------",
                    ] : [
                        "<comment>Skipped SynapseEntity class because file exists</comment>\n" . $classname,
                        "<comment>Path of class file</comment>\n" . realpath($classfile),
                        "-------------------------------------------------",
                    ]
                );
            } else {
                $types = array_combine([
                    'form.entity',
                    'form.hidden_selection',
                    'form.multiple_selection',
                ], [
                    SynapseServiceForm::FORM_ENTITY,
                    SynapseServiceForm::FORM_HIDDEN_SELECTION,
                    SynapseServiceForm::FORM_MULTIPLE_SELECTION
                ]);

                foreach ($synapseService->getEntity()->forms() as $form) {
                    if ((int)$form->type !== $types[$type]) {
                        continue;
                    }

                    $replacements = array_merge($replacements, $this->preapreDataForFormTemplate($form));

                    $classname = str_replace(
                        array_keys($replacements),
                        array_values($replacements),
                        $this->templateTypes[$type]
                    );

                    $result = ! file_exists($classfile = $this->getFileByClassname($classname)) && file_put_contents($classfile, str_replace(
                        array_keys($replacements),
                        array_values($replacements),
                        $template
                    ));

                    $this->output->writeln(
                        $result ? [
                            "<info>Generated Synapse:Service#Form class</info>\n" . $classname,
                            "<info>Path of class file</info>\n" . realpath($classfile),
                            "-------------------------------------------------",
                        ] : [
                            "<comment>Skipped Synapse:Service#Form class because file exists</comment>\n" . $classname,
                            "<comment>Path of class file</comment>\n" . realpath($classfile),
                            "-------------------------------------------------",
                        ]
                    );
                }
            }
        }

        return 0;
    }

    /**
     * getSynapseService
     *
     */
    private function getSynapseService() : ServiceArtificer
    {
        $sm = Qore::service(SynapseManager::class);
        if( ! is_null($synapseService = $this->input->getArgument('synapse-service'))
            && ! is_null($synapseService = $sm->getServicesRepository()->findByName($synapseService))) {
            return $synapseService;
        }

        $synapseServices = $sm->getServicesRepository()->getAll();
        $synapses = $synapseServices->map(function($_service) {
            return [
                'synapse' => $_service->getEntity()->synapse()->name,
                'service' => $_service,
                'service-name' => $_service->getEntity()->getSynapseServiceName(),
            ];
        })->groupBy(function($_service){
            return $_service['synapse'];
        })->toArray();

        ksort($synapses);

        $synapse = $this->getHelper('question')->ask($this->input, $this->output, new ChoiceQuestion(
            'Please choose synapse for code building',
            array_keys($synapses),
            null
        ));

        $services = Qore::collection($synapses[$synapse])->indexBy('service-name')->toArray();
        ksort($services);

        $service = $this->getHelper('question')->ask($this->input, $this->output, new ChoiceQuestion(
            'Please choose service of selected synapse services list',
            array_keys($services),
            null
        ));

        return $services[$service]['service'];
    }

    /**
     * getTemplate
     *
     */
    private function getTemplate() : array
    {
        $templates = $this->getTemplates();

        if (! is_null($template = $this->input->getOption('template')) && isset($templates[$template])) {
            return $templates[$template];
        }

        $question = new ChoiceQuestion(
            'Please choose template for code building (defaults to first item)',
            array_keys($templates),
            0
        );

        return $templates[$this->getHelper('question')->ask($this->input, $this->output, $question)];
    }

    /**
     * getTemplates
     *
     */
    private function getTemplates() : array
    {
        if ($this->templates) {
            return $this->templates;
        }

        if (is_null($templatePath = Qore::config($configOption = 'qore.synapse-configs.code-builder.templates-path', null))) {
            throw new Exception(sprintf('Undefined templates directory in configuration provider `%s`', $configOption));
        }

        $templatesPath = ! is_array($templatePath) ? [$templatePath] : $templatePath;

        $result = [];

        foreach ($templatesPath as $templatePath) {
            if (! is_dir($templatePath)) {
                throw new Exception(sprintf('Directory `%s` is not found!', $templatePath));
            }

            $directoryIterator = new DirectoryIterator($templatePath);
            foreach ($directoryIterator as $subject) {
                if ($subject->isDir() && ! $subject->isDot()) {
                    $result[mb_strtolower($subject->getFilename())] = $this->getTemplatesSamples($subject->getPathname());
                }
            }
        }

        asort($result);

        return $this->templates = $result;
    }

    /**
     * getTemplatesSamples
     *
     * @param string $_templateDirectory
     */
    private function getTemplatesSamples(string $_templateDirectory) : array
    {
        $result = [];
        foreach ($this->getTemplateTypesFiles() as $file => $index) {
            if (file_exists($filepath = sprintf('%s/%s', $_templateDirectory, $file))) {
                $result[$index] = file_get_contents($filepath);
            }
        }
        return $result;
    }

    /**
     * getTemplateTypesFiles
     *
     */
    private function getTemplateTypesFiles()
    {
        $result = [];
        foreach ($this->templateTypes as $type => $details) {
            $result[sprintf('artificer.%s.php', $type)] = $type;
        }

        return $result;
    }

    /**
     * getSynpaseNamespace
     *
     */
    private function getSynapseNamespace() : string
    {
        $namespaces = array_unique(Qore::config('qore.synapse-configs.namespaces', []));
        sort($namespaces);

        foreach ($namespaces as &$namespace) {
            $namespace = ltrim($namespace, '\\');
        }

        $question = new ChoiceQuestion(
            'Please choose synapse namespace (defaults to first item)',
            $namespaces,
            0
        );

        $question->setErrorMessage('Synapse namespace %s is invalid');

        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }

    /**
     * prepareDataForTemplates
     *
     * @param ServiceArtificer $_service
     * @param string $_namespace
     */
    private function prepareDataForTemplates(ServiceArtificer $_service, string $_namespace) : array
    {
        $synapseService = $_service->getEntity();
        $serviceForm = $synapseService->forms()->firstMatch(['name' => $synapseService->synapse()->name])
            ?? $synapseService->forms()->firstMatch(['type' => SynapseServiceForm::FORM_ENTITY]);

        return [
            '{SynapseNamespace}' => $_namespace . '\\' . $synapseService->synapse()->name,
            '{SynapseClass}' => $synapseService->synapse()->name,
            '{SynapseServiceNamespace}' => $_namespace . '\\' . implode('\\', [$synapseService->synapse()->name, $synapseService->name]),
            '{SynapseServiceClass}' => $synapseService->synapse()->name . 'Service',
            '{ServiceForm}' => $serviceForm ? $serviceForm->name : '',
            '{ServiceRoute}' => strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $synapseService->synapse()->name)),
            '{SynapseLabel}' => $synapseService->synapse()->label ?? $synapseService->synapse()->name,
            '{ServiceLabel}' => $synapseService->label,
            '{ServiceDescription}' => $synapseService->description,
        ];
    }

    /**
     * preapreDataForFormTemplate
     *
     * @param SynapseServiceForm $_form
     */
    private function preapreDataForFormTemplate(SynapseServiceForm $_form) : array
    {
        return [
            '{ServiceFormClass}' => $_form->name,
        ];
    }

    /**
     * getFileByClassname
     *
     * @param string $_classname
     */
    private function getFileByClassname(string $_classname) : ?string
    {
        $namespace = $this->getNamespace($_classname);
        if (strpos($_classname, '\\') !== false) {
            $_classname = mb_substr($_classname, mb_strlen($namespace) + 1);
        }

        $namespaces = $this->getNamespaces();
        $baseSegment = $segment = '';
        $sections = explode('\\', $namespace);
        foreach ($sections as $section) {
            $segment .= $section . '\\';
            if (isset($namespaces[$segment])) {
                $baseSegment = $segment;
            }
        }

        if (! $baseSegment) {
            return null;
        }

        if (! is_dir($directory = $namespaces[$baseSegment] . '/' . implode('/', explode('\\', mb_substr($namespace, mb_strlen($baseSegment)))))) {
            mkdir($directory, 0755, true);
        }

        return $directory . '/' . $_classname . '.php';
    }

    /**
     * getNamespace
     *
     * @param string $_namespace
     */
    private function getNamespace(string $_classname) : string
    {
        if (strpos($_classname, '\\') === false) {
            $_classname = static::class;
        }

        $sections = explode('\\', $_classname);
        array_pop($sections);
        return implode('\\', $sections);
    }

    /**
     * getNamespaces
     *
     */
    private function getNamespaces() : array
    {
        return Qore::collection(Qore::service('loader')->getPrefixesPsr4())->map(function($_el) {
            return array_shift($_el);
        })->toArray();
    }

}
