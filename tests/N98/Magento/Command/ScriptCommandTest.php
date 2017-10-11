<?php

namespace N98\Magento\Command;

/**
 * Class ScriptCommandTest
 * @package N98\Magento\Command
 */
class ScriptCommandTest extends TestCase
{
    public function testExecute()
    {
        $input = array(
            'command'  => 'script',
            'filename' => __DIR__ . '/_files/test.mr',
        );

        // Check pre defined vars
        $this->assertDisplayRegExp($input, '~^\Qmagento.root: \E/.+\R$~m');
        $this->assertDisplayRegExp($input, '~^\Qmagento.edition: \E(Community|Enterprise)\R$~m');
        $this->assertDisplayRegExp($input, '~^\Qmagento.version: \E\d\.\d+\.\d+.*\R$~m');

        $this->assertDisplayContains($input, 'magerun.version: ' . $this->getApplication()->getVersion());

        $this->assertDisplayContains($input, 'code');
        $this->assertDisplayContains($input, 'foo.sql');
        $this->assertDisplayContains($input, 'BAR: foo.sql.gz');
        $this->assertDisplayContains($input, 'Magento Websites');
        $this->assertDisplayContains($input, 'web/secure/base_url');
        $this->assertDisplayContains($input, 'web/seo/use_rewrites => 1');
    }
}
