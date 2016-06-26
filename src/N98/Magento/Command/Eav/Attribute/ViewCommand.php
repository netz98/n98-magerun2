<?php

namespace N98\Magento\Command\Eav\Attribute;

use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ViewCommand extends AbstractAttributeCommand
{
    /**
     * Setup
     */
    protected function configure()
    {
        $this
            ->setName('eav:attribute:view')
            ->addArgument('entityType', InputArgument::REQUIRED, 'Entity Type Code like catalog_product')
            ->addArgument('attributeCode', InputArgument::REQUIRED, 'Attribute Code')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('View information about an EAV attribute')
            ->setHelp('Enter an entity type code and an attribute code to see information about an EAV attribute.');
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     * @return void
     * @throws InvalidArgumentException If the attribute doesn't exist
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $entityType = $input->getArgument('entityType');
        $attributeCode = $input->getArgument('attributeCode');
        $attribute = $this->getAttribute($entityType, $attributeCode);
        if (!$attribute->getId()) {
            throw new \InvalidArgumentException('Attribute was not found.');
        }

        $table = $this->getTable($attribute);

        $this
            ->getHelper('table')
            ->setHeaders(array('Type', 'Value'))
            ->renderByFormat($output, $table, $input->getOption('format'));
    }

    /**
     * Define the contents for the table. The key is the magic method name e.g.
     * get[Name](), and the value is an array containing first the label to display, then optionally
     * a callback for how to process the attribute value for display
     * @param  bool $isFrontend
     * @return array
     */
    public function getTableInput($isFrontend = false)
    {
        $table = array(
            'Id' => array('ID'),
            'Name' => array('Code'),
            'AttributeSetId' => array('Attribute-Set-ID'),
            'VisibleOnFront' => array('Visible-On-Front', function ($value) {
                return $value ? 'yes' : 'no';
            }),
            'AttributeModel' => array('Attribute-Model'),
            'BackendModel' => array('Backend-Model'),
            'BackendTable' => array('Backend-Table'),
            'BackendType' => array('Backend-Type'),
            'SourceModel' => array('Source-Model'),
            'CacheIdTags' => array('Cache-ID-Tags', function ($values) {
                return implode(',', (array) $values);
            }),
            'CacheTags' => array('Cache-Tags', function ($values) {
                return implode(',', (array) $values);
            }),
            'DefaultValue' => array('Default-Value'),
            'FlatColumns' => array(
                'Flat-Columns',
                function ($values) {
                    return implode(',', array_keys((array) $values));
                }
            ),
            'FlatIndexes' => array(
                'Flat-Indexes',
                function ($values) {
                    return implode(',', array_keys((array) $values));
                }
            )
        );


        if ($isFrontend) {
            $table['Frontend/Label'] = array('Frontend-Label');
            $table['Frontend/Class'] = array('Frontend-Class');
            $table['Frontend/InputType'] = array('Frontend-Input-Type');
            $table['Frontend/InputRendererClass'] = array('Frontend-Input-Renderer-Class');
        }

        return $table;
    }

    /**
     * Given an attribute and an input data table, construct the output table and call
     * the formatting callbacks if necessary
     * @param  Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return array
     */
    private function getTable($attribute)
    {
        $table = array();

        foreach ($this->getTableInput($attribute->getFrontend()) as $code => $info) {
            $label = array_shift($info);
            $callback = is_array($info) ? array_shift($info) : null;

            // Support nested getters
            $levels = explode('/', $code);
            $value = $attribute;
            foreach ($levels as $level) {
                $value = $value->{'get' . $level}();
            }

            // Optional formatting callback
            $value = is_callable($callback) ? $callback($value) : $value;

            if ($value === array()) {
                $value = '';
            }

            $table[] = array($label, trim($value));
        }

        return $table;
    }
}
