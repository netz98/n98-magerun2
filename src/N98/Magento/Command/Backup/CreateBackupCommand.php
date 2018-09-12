<?php
//requires tar, which everyone should have
namespace N98\Magento\Command\Backup;

use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use N98\Util\Exec;
use N98\Util\Filesystem;
use N98\Util\OperatingSystem;

class CreateBackupCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
      $this
          ->setName('backup:create')
          ->setDescription('Creates a full backup with db dump, presumes a lot about your server paths for tar.')
      ;
    }

   /**
    * @param \Symfony\Component\Console\Input\InputInterface $input
    * @param \Symfony\Component\Console\Output\OutputInterface $output
    * @return int|void
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {

		//first create db:dump
		$this->getApplication()->setAutoExit(false);
		$date = date('mdY_h', time());
		$input = new StringInput('db:dump '.$date);
        $this->getApplication()->run($input, $output);
		
		
		$timeStamp = date('Y-m-d_His');
		$backupException = null;
		$defaultName=$timeStamp . '_backup.tar.gz';
		$dialog = $this->getHelper('dialog');
			$fileName = $dialog->ask(
				$output,
				'<question>Filename for backup:</question> [<comment>' . $timeStamp . '_backup.tar.gz' . '</comment>]',$defaultName);

		
		
		$backupCommand= 'tar -zcvf ../' . $fileName . ' .';
        $backupOutput = null;
        $returnStatus = null;
		$output->writeln("Tar-balling Magento directory, check one directory up on completion.");
		$process = new Process($backupCommand);
		$process->setTimeout(36000);
		$process->run(function ($type, $buffer) {
			if (Process::ERR === $type) {
				echo 'ERR > '.$buffer;
			} else {
				echo $buffer;
			}
		});
		//delete sql file created earlier
		$output->writeln("Deleting sql from root, now in archive.");
		$process = new Process('rm '.$date.'.sql');
		$process->run();

		// executes after the command finishes
		if (!$process->isSuccessful()) {
			throw new ProcessFailedException($process);
		}

		echo $process->getOutput();
		//timestamp

		$output->writeln("Backup complete, check up a directory, try cd ../");
		$this->getApplication()->setAutoExit(true);
	
	
	

        }
    }
}
