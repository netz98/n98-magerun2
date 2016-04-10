<?php
/**
 * Copyright © 2016 netz98 new media GmbH. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\DB\Ddl\Table;
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
     * @var array
     */
    private $columnTypesWithSize = [
        Table::TYPE_TEXT,
    ];

    /**
     * @var array
     */
    private $intTypes = [
        Table::TYPE_BIGINT,
        Table::TYPE_INTEGER,
        Table::TYPE_SMALLINT,
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

        $tableName = $this->askForTableName($input, $output, $questionHelper);
        $columns = $this->processColumns($input, $output, $questionHelper);
        $comment = $this->askForTableComment($input, $output, $questionHelper);

        $this->generateCode($input, $output, $tableName, $columns, $comment);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @return array
     */
    private function processColumns(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $columns = [];

        do {
            $columns[] = $this->processColumn($input, $output, $questionHelper);

            $question = new ConfirmationQuestion('<question>Add a new column? [y/n]</question>:');
        } while ($questionHelper->ask($input, $output, $question));

        return $columns;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @return array
     */
    private function processColumn(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $data = [];

        $data['name'] = $this->askForColumnName($input, $output, $questionHelper);
        $data['type'] = $this->askForColumnType($input, $output, $questionHelper);
        $data['size'] = $this->askForColumnSize($input, $output, $questionHelper, $data);
        $data['nullable'] = $this->askForColumnIsNullable($input, $output, $questionHelper, $data);
        $data['unsigned'] = $this->askForColumnIsUnsigned($input, $output, $questionHelper, $data);
        $data['default'] = $this->askForColumnDefault($input, $output, $questionHelper, $data);
        $data['comment'] = $this->askForColumnComment($input, $output, $questionHelper);
        $output->writeln('');

        return $data;
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
     * @return string
     */
    private function askForColumnType(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $columnTypeQuestion = new ChoiceQuestion('<question>Column type:</question>', $this->columnTypes);
        $columnTypeQuestion->setErrorMessage('Type %s is invalid.');

        return $questionHelper->ask($input, $output, $columnTypeQuestion);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $data
     * @return string
     */
    private function askForColumnIsUnsigned(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, array $data
    ) {
        if (!in_array($data['type'], $this->intTypes)) {
            return null;
        }

        $columnTypeQuestion = new ConfirmationQuestion(
            '<question>Is column unsigned?</question><info>(default yes)</info>'
        );

        return $questionHelper->ask($input, $output, $columnTypeQuestion);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $data
     * @return int
     */
    private function askForColumnSize(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        array $data
    ) {
        if (!in_array($data['type'], $this->columnTypesWithSize)) {
            return null;
        }

        $default = null;

        if ($data['type'] == Table::TYPE_TEXT) {
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

        return $questionHelper->ask($input, $output, $columnNameQuestion);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function askForColumnIsNullable(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $columnTypeQuestion = new ConfirmationQuestion(
            '<question>Is column nullable:</question><info>(default yes)</info>'
        );

        return $questionHelper->ask($input, $output, $columnTypeQuestion);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param array $data
     * @return string
     */
    private function askForColumnDefault(
        InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, array $data
    ) {
        $question = new Question('<question>Column default value:</question>');
        $question->setValidator(function ($answer) use ($data) {

            if (in_array($data['type'], $this->intTypes) && !is_numeric($answer)) {
                throw new \InvalidArgumentException('Invalid default value');
            }

            return $answer;
        });

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function askForColumnComment(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $columnNameQuestion = new Question('<question>Column comment:</question>');
        $columnNameQuestion->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Column comment could not be empty');
            }

            return $answer;
        });

        return $questionHelper->ask($input, $output, $columnNameQuestion);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $questionHelper
     * @return string
     */
    private function askForTableName(InputInterface $input, OutputInterface $output, $questionHelper)
    {
        $question = new Question('<question>Table name:</question>');
        $question->setValidator(function ($answer) {

            if (empty($answer)) {
                throw new \RuntimeException('Table name could not be empty');
            }

            return $answer;
        });

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $questionHelper
     * @return string
     */
    private function askForTableComment(InputInterface $input, OutputInterface $output, $questionHelper)
    {
        $question = new Question('<question>Table comment:</question>');
        $question->setValidator(function ($answer) {

            if (empty($answer)) {
                throw new \RuntimeException('Table comment could not be empty');
            }

            return $answer;
        });

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $columns
     * @param string $tableComment
     * @return string
     */
    private function generateCode(
        InputInterface $input, OutputInterface $output, $tableName, array $columns, $tableComment
    ) {
        /** @var TwigHelper $twigHelper */
        $twigHelper = $this->getHelper('twig');
        echo $twigHelper->render(
            'dev/console/make/table.twig',
            ['tableName' => $tableName, 'columns' => $columns, 'tableComment' => $tableComment]
        );
    }
}