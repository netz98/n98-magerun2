<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Application\Console\Descriptor;

use N98\Util\Console\Glyph;
use N98\Util\Console\Theme;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Descriptor\ApplicationDescription;
use Symfony\Component\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Renders `list` and `help` in magerun's dense visual language.
 *
 * Only the surrounding structure changes - section headings become upper-case rules, namespaces
 * are indented groups, and descriptions are de-emphasised. Every piece of text Symfony generates
 * (command synopses, option descriptions, argument defaults) is passed through untouched, because
 * users and the functional test suite both match on those strings.
 *
 * Individual argument and option lines are delegated to the parent so their layout, default-value
 * rendering and escaping stay in sync with whatever Symfony version is installed.
 */
final class MagerunTextDescriptor extends TextDescriptor
{
    /**
     * Extra gap between a name column and its description.
     */
    private const COLUMN_GAP = 2;

    /**
     * @var bool whether the target output can render the redesigned layout
     */
    private $styled = false;

    public function describe(OutputInterface $output, object $object, array $options = []): void
    {
        // Styles are registered in Application::run(), but a descriptor is also reachable from
        // callers that built their own output - the command tester, embedded shells. Symfony emits
        // unregistered tags verbatim, so without this the banner's <emphasis>/<accent> would be
        // printed as literal text instead of being coloured or stripped.
        Theme::apply($output);

        $this->styled = $output->isDecorated() && !Theme::colorDisabledByEnvironment();

        parent::describe($output, $object, $options);
    }

    protected function describeApplication(Application $application, array $options = []): void
    {
        if (!$this->styled) {
            // Piped `list` output is a documented interface - shell completion, scripts and the
            // functional suite all parse it - so it stays exactly as Symfony renders it.
            parent::describeApplication($application, $options);

            return;
        }

        $describedNamespace = $options['namespace'] ?? null;
        $description = new ApplicationDescription($application, $describedNamespace);

        if ($options['raw_text'] ?? false) {
            $this->describeApplicationRaw($description, $options);

            return;
        }

        if ('' !== $help = $application->getHelp()) {
            $this->writeRaw($help, $options);
        }

        $this->heading('Usage', null, $options);
        $this->writeRaw(sprintf(
            "  %s %s [options] [arguments]\n",
            $application->getName(),
            OutputFormatter::escape('<command>')
        ), $options);
        $this->writeRaw(sprintf(
            "  <hint>Run %s help %s for details on a single command.</hint>\n",
            $application->getName(),
            OutputFormatter::escape('<command>')
        ), $options);

        $globalOptions = new InputDefinition($application->getDefinition()->getOptions());
        if ($globalOptions->getOptions()) {
            $this->heading('Global options', null, $options);
            $this->describeOptionList($globalOptions, $options);
        }

        $this->describeCommandList($description, $describedNamespace, $options);
    }

    protected function describeCommand(Command $command, array $options = []): void
    {
        if (!$this->styled) {
            parent::describeCommand($command, $options);

            return;
        }

        $command->mergeApplicationDefinition(false);

        $description = $command->getDescription();
        if ($description) {
            $this->heading('Description', null, $options);
            $this->writeRaw('  ' . $description . "\n", $options);
        }

        $this->heading('Usage', null, $options);
        foreach (array_merge([$command->getSynopsis(true)], $command->getAliases(), $command->getUsages()) as $usage) {
            $this->writeRaw('  ' . OutputFormatter::escape($usage) . "\n", $options);
        }

        $definition = $command->getDefinition();

        if ($definition->getArguments()) {
            $this->heading('Arguments', null, $options);
            $this->describeArgumentList($definition, $options);
        }

        if ($definition->getOptions()) {
            $this->heading('Options', null, $options);
            $this->describeOptionList($definition, $options);
        }

        $help = $command->getProcessedHelp();
        if ($help && $help !== $description) {
            $this->heading('Help', null, $options);
            $this->writeRaw('  ' . str_replace("\n", "\n  ", $help) . "\n", $options);
        }
    }

    /**
     * `list --raw`: one `name description` line per command and nothing else. Consumed by scripts
     * and shell completion, so it must stay exactly as Symfony produces it.
     */
    private function describeApplicationRaw(ApplicationDescription $description, array $options): void
    {
        $commands = $description->getCommands();
        $width = $this->columnWidth(array_keys($commands));

        foreach ($commands as $command) {
            $this->writeRaw(
                sprintf("%-{$width}s %s\n", $command->getName(), $command->getDescription()),
                $options
            );
        }
    }

    private function describeCommandList(
        ApplicationDescription $description,
        ?string $describedNamespace,
        array $options
    ): void {
        $commands = $description->getCommands();
        $namespaces = $description->getNamespaces();

        if ($describedNamespace && $namespaces) {
            // Alias-only entries are not in getCommands() but belong to the described namespace.
            $describedNamespaceInfo = reset($namespaces);
            foreach ($describedNamespaceInfo['commands'] as $name) {
                $commands[$name] = $description->getCommand($name);
            }
        }

        $listedNames = [];
        foreach ($namespaces as $namespace) {
            $listedNames[] = array_intersect($namespace['commands'], array_keys($commands));
        }
        $listedNames = $listedNames === [] ? [] : array_merge(...$listedNames);

        // The name column is indented one level deeper inside a namespace group, so budget for it.
        $width = $this->columnWidth($listedNames) + 2;

        $heading = $describedNamespace === null
            ? 'Commands'
            : sprintf('Commands in "%s"', $describedNamespace);

        $this->heading($heading, count($listedNames), $options);

        foreach ($namespaces as $namespace) {
            $namespace['commands'] = array_filter(
                $namespace['commands'],
                static fn ($name) => isset($commands[$name])
            );

            if (!$namespace['commands']) {
                continue;
            }

            $isGrouped = !$describedNamespace && ApplicationDescription::GLOBAL_NAMESPACE !== $namespace['id'];

            if ($isGrouped) {
                $this->writeRaw(sprintf(
                    "\n  <subheading>%s</subheading>\n",
                    OutputFormatter::escape($namespace['id'])
                ), $options);
            }

            foreach ($namespace['commands'] as $name) {
                $command = $commands[$name];
                $aliases = $name === $command->getName()
                    ? OutputFormatter::escape($this->aliasesText($command))
                    : '';
                $indent = $isGrouped ? '    ' : '  ';
                $padding = max(1, $width - Helper::width($name) - ($isGrouped ? 2 : 0));

                $this->writeRaw(sprintf(
                    "%s<accent>%s</accent>%s<muted>%s</muted>\n",
                    $indent,
                    OutputFormatter::escape($name),
                    str_repeat(' ', $padding),
                    $aliases . OutputFormatter::escape($command->getDescription())
                ), $options);
            }
        }
    }

    private function describeArgumentList(InputDefinition $definition, array $options): void
    {
        $totalWidth = $this->totalWidth($definition);

        foreach ($definition->getArguments() as $argument) {
            $this->describeInputArgument($argument, array_merge($options, ['total_width' => $totalWidth]));
            $this->writeRaw("\n", $options);
        }
    }

    private function describeOptionList(InputDefinition $definition, array $options): void
    {
        $totalWidth = $this->totalWidth($definition);

        // Symfony lists options with a multi-character shortcut last; keep that ordering.
        $laterOptions = [];

        foreach ($definition->getOptions() as $option) {
            if (strlen($option->getShortcut() ?? '') > 1) {
                $laterOptions[] = $option;
                continue;
            }

            $this->describeInputOption($option, array_merge($options, ['total_width' => $totalWidth]));
            $this->writeRaw("\n", $options);
        }

        foreach ($laterOptions as $option) {
            $this->describeInputOption($option, array_merge($options, ['total_width' => $totalWidth]));
            $this->writeRaw("\n", $options);
        }
    }

    /**
     * Unlike a command's own headings this one keeps an underlining rule: the help page is a wall of
     * full-width text with no table borders to separate its sections.
     */
    private function heading(string $text, ?int $count, array $options): void
    {
        $this->writeRaw(sprintf(
            "\n%s\n<border>%s</border>\n",
            Theme::headingLine($text, $count),
            Glyph::repeat(Glyph::LINE, Theme::width())
        ), $options);
    }

    /**
     * @param array<int, string> $names
     */
    private function columnWidth(array $names): int
    {
        $width = 0;

        foreach ($names as $name) {
            $width = max($width, Helper::width((string) $name));
        }

        return $width + self::COLUMN_GAP;
    }

    /**
     * Width of the widest argument/option synopsis, which the parent's line renderers pad to.
     */
    private function totalWidth(InputDefinition $definition): int
    {
        $width = 0;

        foreach ($definition->getArguments() as $argument) {
            $width = max($width, Helper::width($argument->getName()));
        }

        foreach ($definition->getOptions() as $option) {
            $shortcut = $option->getShortcut() ? sprintf('-%s, ', $option->getShortcut()) : '    ';
            $value = '';

            if ($option->acceptValue()) {
                $valueName = strtoupper($option->getName());
                $value = $option->isValueOptional() ? '[=' . $valueName . ']' : '=' . $valueName;
            }

            $width = max($width, Helper::width($shortcut . '--' . $option->getName() . $value));
        }

        return $width;
    }

    private function aliasesText(Command $command): string
    {
        $aliases = $command->getAliases();

        return $aliases ? '[' . implode('|', $aliases) . '] ' : '';
    }

    /**
     * Honours the same `raw_text` / `raw_output` semantics as the parent descriptor.
     */
    private function writeRaw(string $content, array $options = []): void
    {
        $this->write(
            ($options['raw_text'] ?? false) ? strip_tags($content) : $content,
            isset($options['raw_output']) ? !$options['raw_output'] : true
        );
    }
}
