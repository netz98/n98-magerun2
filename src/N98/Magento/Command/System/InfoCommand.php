<?php

namespace N98\Magento\Command\System;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;

class InfoCommand extends AbstractMagentoCommand
{
    /**
     * @var array
     */
    protected $infos;

    protected function configure()
    {
        $this
            ->setName('sys:info')
            ->setDescription('Prints infos about the current magento system.')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);

        if ($input->getOption('format') == null) {
            $this->writeSection($output, 'Magento System Information');
        }

        $this->initMagento();

        $this->infos['Version'] = \Magento\Framework\AppInterface::VERSION;
        $this->infos['Edition'] = 'Community'; // @TODO Where can i obtain this info?

        $sessionConfig = $this->getObjectManager()->get('Magento\Framework\Session\Config');
        $this->infos['Session'] = $sessionConfig->getSaveHandler();

/*        $constInterpreter = new \Magento\Framework\Data\Argument\Interpreter\Constant();
        $interpreter = new \Magento\Framework\App\Arguments\ArgumentInterpreter($constInterpreter);
        var_dump($interpreter->evaluate(array('value' => 'Magento\Framework\Encryption\Encryptor::PARAM_CRYPT_KEY')));
*/
        $this->infos['Crypt Key'] = '<NOT IMPLEMENTED NOW>'; // @TODO Implement
        $this->infos['Install Date'] = '<NOT IMPLEMENTED NOW>'; // @TODO Implement

        $this->_addCacheInfos();

        $table = array();
        foreach ($this->infos as $key => $value) {
            $table[] = array($key, $value);
        }

        $this->getHelper('table')
            ->setHeaders(array('name', 'value'))
            ->renderByFormat($output, $table, $input->getOption('format'));
    }

    protected function _addCacheInfos()
    {
        $cachePool = $this->getObjectManager()->get('Magento\Framework\App\Cache\Type\FrontendPool');

        $this->infos['Cache Backend'] = get_class($cachePool->get('config')->getBackend());

        switch (get_class($cachePool->get('config')->getBackend())) {
            case 'Zend_Cache_Backend_File':
            case 'Cm_Cache_Backend_File':
                // @TODO Where are the cache options?
                //$cacheDir = $cachePool->get('config')->getBackend()->getOptions()->getCacheDir();
                //$this->infos['Cache Directory'] = $cacheDir;
                break;

            default:
        }
    }
}