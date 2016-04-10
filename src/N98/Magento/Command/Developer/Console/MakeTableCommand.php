<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\DB\Ddl\Table;
use N98\Magento\Command\Developer\Console\Renderer\PHPCode\TableRenderer;
use N98\Magento\Command\Developer\Console\Structure\DDLTable;
use N98\Magento\Command\Developer\Console\Structure\DDLTableColumn;
use N98\Util\Console\Helper\TwigHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class MakeTableCommand extends AbstractGeneratorCommand
{
    /**
     * @var array
     */
    private $columnTypes = [
        Table::TYPE_BOOLEAN,
        Table::TYPE_SMALLINT,
        Table::TYPE_INTEGER,
        Table::TYPE_BIGINT,
        Table::TYPE_FLOAT,
        Table::TYPE_NUMERIC,
        Table::TYPE_DECIMAL,
        Table::TYPE_DATE,
        Table::TYPE_TIMESTAMP,
        Table::TYPE_DATETIME,
        Table::TYPE_TEXT,
        Table::TYPE_BLOB,
        Table::TYPE_VARBINARY,
    ];

    /**
     * @var string
     */
    private $identityColumn = null;

    protected function configure()
    {
        $this
            ->setName('make:table')
            ->setDescription('Creates a new database table')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        $table = new DDLTable();
        
        $this->askForTableName($input, $output, $questionHelper, $table);
        $this->processColumns($input, $output, $questionHelper, $table);
        $this->askForTableComment($input, $output, $questionHelper, $table);

        $this->generateCode($input, $output, $table);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param DDLTable $table
     * @return void
     */
    private function processColumns(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, DDLTable $table
    ) {
        $columns = [];

        do {
            $columns[] = $this->processColumn($input, $output, $questionHelper);

            $question = new ConfirmationQuestion('<question>Add a new column? [y/n]</question>:');
        } while ($questionHelper->ask($input, $output, $question));

        $table->setColumnDefinitions($columns);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @return DDLTableColumn
     */
    private function processColumn(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper
    ) {
        $column = new DDLTableColumn();
        
        $this->askForColumnName($input, $output, $questionHelper, $column);
        $this->askForColumnType($input, $output, $questionHelper, $column);

        if (empty($this->identityColumn)) {
            $this->askForIdentityColumn($input, $output, $questionHelper, $column);
        }

        $this->askForColumnSize($input, $output, $questionHelper, $column);
        $this->askForColumnIsNullable($input, $output, $questionHelper, $column);
        $this->askForColumnIsUnsigned($input, $output, $questionHelper, $column);
        $this->askForColumnDefault($input, $output, $questionHelper, $column);
        $this->askForColumnComment($input, $output, $questionHelper, $column);

        $output->writeln('');

        return $column;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function askForColumnName(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $columnNameQuestion = new Question('<question>Column name:</question>');
        $columnNameQuestion->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Column name could not be empty');
            }

            return $answer;
        });

        return $questionHelper->ask($input, $output, $columnNameQuestion);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param DDLTableColumn $data
     * @TODO refactor
     * @return string
     */
    private function askForIdentityColumn(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, DDLTableColumn $data
    ) {
        $columnNameQuestion = new ConfirmationQuestion('<question>Is this your identity column? (y/n)</question>');

        if ($questionHelper->ask($input, $output, $columnNameQuestion)) {
            $this->identityColumn = $data['name'];
            $data['type'] = Table::TYPE_INTEGER;
            $data['unsigned'] = true;
            $data['default'] = null;
            $data['primary'] = true;
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param DDLTableColumn $data
     * @return void
     */
    private function askForColumnType(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, DDLTableColumn $data
    ) {
        $columnTypeQuestion = new ChoiceQuestion('<question>Column type:</question>', $this->columnTypes);
        $columnTypeQuestion->setErrorMessage('Type %s is invalid.');

        $data->setType($questionHelper->ask($input, $output, $columnTypeQuestion));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param DDLTableColumn $column
     * @return void
     */
    private function askForColumnIsUnsigned(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, DDLTableColumn $column
    ) {
        if (!$column->isIntType()) {
            return;
        }

        $columnTypeQuestion = new ConfirmationQuestion(
            '<question>Is column unsigned?</question><info>(default yes)</info>'
        );

        $column->setUnsigned($questionHelper->ask($input, $output, $columnTypeQuestion));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param DDLTableColumn $column
     * @return void
     */
    private function askForColumnSize(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        DDLTableColumn $column
    ) {
        if (!$column->isTypeWithSize()) {
            return;
        }

        $default = null;

        if ($column->getType() == Table::TYPE_TEXT) {
            $default = 255;
        }

        $columnNameQuestion = new Question(
            '<question>Column size:</question> <info>(default: ' . $default . ')</info>',
            $default
        );
        $columnNameQuestion->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Column size could not be empty');
            }

            if ($answer <= 0) {
                throw new \RuntimeException('Column size could be greater than zero');
            }

            return (int) $answer;
        });

        $column->setSize($questionHelper->ask($input, $output, $columnNameQuestion));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param DDLTableColumn $column
     * @return void
     */
    private function askForColumnIsNullable(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, DDLTableColumn $column
    ) {
        $columnTypeQuestion = new ConfirmationQuestion(
            '<question>Is column nullable:</question><info>(default yes)</info>'
        );

        $column->setNullable($questionHelper->ask($input, $output, $columnTypeQuestion));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param DDLTableColumn $column
     * @return void
     */
    private function askForColumnDefault(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, DDLTableColumn $column
    ) {
        $question = new Question('<question>Column default value:</question>');
        $question->setValidator(function ($answer) use ($column) {

            if ($column->isIntType() && !is_numeric($answer) && !empty($answer)) {
                throw new \InvalidArgumentException('Invalid default value');
            }

            return $answer;
        });

        $column->setDefault($questionHelper->ask($input, $output, $question));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param DDLTableColumn $column
     * @return void
     */
    private function askForColumnComment(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, DDLTableColumn $column
    ) {
        $columnNameQuestion = new Question('<question>Column comment:</question>');
        $columnNameQuestion->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Column comment could not be empty');
            }

            return $answer;
        });

        
        $column->setComment($questionHelper->ask($input, $output, $columnNameQuestion));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $questionHelper
     * @param DDLTable $table
     * @return void
     */
    private function askForTableName(InputInterface $input, OutputInterface $output, $questionHelper, DDLTable $table)
    {
        $question = new Question('<question>Table name:</question>');
        $question->setValidator(function ($answer) {

            if (empty($answer)) {
                throw new \RuntimeException('Table name could not be empty');
            }

            return $answer;
        });

        $table->setName($questionHelper->ask($input, $output, $question));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $questionHelper
     * @param DDLTable $column
     * @return void
     */
    private function askForTableComment(
        InputInterface $input, OutputInterface $output, $questionHelper, DDLTable $column
    ) {
        $question = new Question('<question>Table comment:</question>');
        $question->setValidator(function ($answer) {

            if (empty($answer)) {
                throw new \RuntimeException('Table comment could not be empty');
            }

            return $answer;
        });

        $column->setComment($questionHelper->ask($input, $output, $question));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param DDLTable $table
     * @return void
     */
    private function generateCode(
        InputInterface $input, OutputInterface $output, DDLTable $table
    ) {
        $renderer = new TableRenderer($table, $this->getHelper('twig'));

        echo $renderer->render();
    }
}