<?php

namespace N98\Magento\Command\Developer\Console;

use Psy\Shell as PsyShell;

class Shell extends PsyShell
{
    /**
     * @var string
     */
    private $prompt = '';

    /**
     * @return string
     */
    protected function getPrompt()
    {
        if (!empty($this->prompt)) {
            return $this->prompt;
        }

        return parent::getPrompt();
    }

    /**
     * @param string $prompt
     */
    public function setPrompt($prompt)
    {
        $this->prompt =  $prompt;
    }

    /**
     * Resets prompt to default
     */
    public function resetPrompt()
    {
        $this->prompt = '';
    }

}