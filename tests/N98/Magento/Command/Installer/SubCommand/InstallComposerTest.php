<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\ConfigBag;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class InstallComposerTest extends TestCase
{
    public function testDownloadComposerVerifiesHashSuccess()
    {
        $content = '<?php echo "installer"; ?>';
        $signature = hash('sha384', $content);

        $command = new class() extends InstallComposer {
            public $installerContent;
            public $signatureContent;

            protected function fetchComposerInstaller()
            {
                return $this->installerContent;
            }

            protected function fetchComposerSignature()
            {
                return $this->signatureContent;
            }
        };

        $command->installerContent = $content;
        $command->signatureContent = $signature;

        $command->setConfig(new ConfigBag(['initialFolder' => sys_get_temp_dir()]));
        $command->setOutput(new NullOutput());

        try {
            $reflection = new \ReflectionClass($command);
            $method = $reflection->getMethod('downloadComposer');
            if (\PHP_VERSION_ID < 80100) {
                $method->setAccessible(true);
            }
            $result = $method->invoke($command);

            // If it returns a path, it means it succeeded (execution passed, which might happen if exec works or fails gracefully without throw)
            // But usually exec failure throws Exception 'Installation failed.'
            // Let's catch that specific exception as a "success" for hash verification.

        } catch (\Exception $e) {
            if ($e->getMessage() === 'Composer installer corrupt') {
                $this->fail('Hash verification failed unexpectedly');
            }
            // Other exceptions are acceptable (e.g. installation failed because we didn't provide a real installer)
        }
    }

    public function testDownloadComposerVerifiesHashFailure()
    {
        $content = 'some content';
        $signature = 'invalid_hash';

        $command = new class() extends InstallComposer {
            public $installerContent;
            public $signatureContent;

            protected function fetchComposerInstaller()
            {
                return $this->installerContent;
            }

            protected function fetchComposerSignature()
            {
                return $this->signatureContent;
            }
        };

        $command->installerContent = $content;
        $command->signatureContent = $signature;

        $command->setConfig(new ConfigBag(['initialFolder' => sys_get_temp_dir()]));
        $command->setOutput(new NullOutput());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Composer installer corrupt');

        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('downloadComposer');
        if (\PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }
        $method->invoke($command);
    }
}
