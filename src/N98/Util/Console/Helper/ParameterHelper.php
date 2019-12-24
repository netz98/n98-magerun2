<?php

namespace N98\Util\Console\Helper;

use Exception;
use InvalidArgumentException;
use N98\Util\Validator\FakeMetadataFactory;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper as AbstractHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidatorFactory;

/**
 * Helper to init some parameters
 *
 * @package N98\Util\Console\Helper
 */
class ParameterHelper extends AbstractHelper
{
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'parameter';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argumentName
     * @param bool $withDefaultStore [optional]
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function askStore(
        InputInterface $input,
        OutputInterface $output,
        $argumentName = 'store',
        $withDefaultStore = false
    ) {
        /* @var $storeManager \Magento\Store\Model\StoreManagerInterface */
        $storeManager = $this->getHelperSet()
            ->getCommand()
            ->getApplication()
            ->getObjectManager()
            ->get('Magento\Store\Model\StoreManagerInterface');

        try {
            if ($input->getArgument($argumentName) === null) {
                throw new RuntimeException('No store given');
            }
            $store = $storeManager->getStore($input->getArgument($argumentName));
        } catch (Exception $e) {
            $stores = [];
            $questionHelper = [];
            $i = 0;
            foreach ($storeManager->getStores($withDefaultStore) as $store) {
                $stores[$i] = $store->getId();
                $choices[$i + 1] = sprintf(
                    '<comment>' . $store->getCode() . ' - ' . $store->getName() . '</comment>'
                );
                $i++;
            }

            if (count($stores) > 1) {
                $questionHelper[] = '';

                $question = new ChoiceQuestion('Please select a store', $choices);
                $question->setValidator(
                    function ($typeInput) use ($stores) {
                        if (!isset($stores[$typeInput - 1])) {
                            throw new InvalidArgumentException('Invalid store');
                        }

                        return $stores[$typeInput - 1];
                    }
                );

                /** @var QuestionHelper $questionHelper */
                $questionHelper = $this->getHelperSet()->get('question');
                $storeId = $questionHelper->ask($input, $output, $question);
            } else {
                // only one store view available -> take it
                $storeId = $stores[0];
            }

            $store = $storeManager->getStore($storeId);
        }

        return $store;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argumentName
     *
     * @return mixed
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function askWebsite(InputInterface $input, OutputInterface $output, $argumentName = 'website')
    {
        /* @var $storeManager \Magento\Store\Model\StoreManagerInterface */
        $storeManager = $this->getHelperSet()
            ->getCommand()
            ->getApplication()
            ->getObjectManager()
            ->get('Magento\Store\Model\StoreManagerInterface');

        try {
            if ($input->getArgument($argumentName) === null) {
                throw new RuntimeException('No website given');
            }
            $website = $storeManager->getWebsite($input->getArgument($argumentName));
        } catch (Exception $e) {
            $i = 0;
            $websites = [];
            foreach ($storeManager->getWebsites() as $website) {
                $websites[$i] = $website->getId();
                $choices[$i + 1] = '<comment>' . $website->getCode() . ' - ' . $website->getName() . '</comment>';
                $i++;
            }

            if (count($websites) === 1) {
                return $storeManager->getWebsite($websites[0]);
            }

            $question = new ChoiceQuestion('Please select a website', $choices);
            $question->setValidator(function ($typeInput) use ($websites) {
                if (!isset($websites[$typeInput - 1])) {
                    throw new InvalidArgumentException('Invalid store');
                }

                return $websites[$typeInput - 1];
            });

            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelperSet()->get('question');
            $websiteId = $questionHelper->ask($input, $output, $question);

            $website = $storeManager->getWebsite($websiteId);
        }

        return $website;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argumentName
     *
     * @return string
     * @throws \Exception
     */
    public function askEmail(InputInterface $input, OutputInterface $output, $argumentName = 'email')
    {
        $constraints = new Constraints\Collection(
            [
                'email' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ],
            ]
        );

        return $this->_validateArgument($input, $output, $argumentName, $input->getArgument($argumentName), $constraints);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argumentName
     * @return string
     * @throws \Exception
     */
    public function askPassword(
        InputInterface $input,
        OutputInterface $output,
        $argumentName = 'password',
        $needDigits = true
    ) {
        $validators = [];

        if ($needDigits) {
            $regex = [
                'pattern' => '/^(?=.*\d)(?=.*[a-zA-Z])/',
                'message' => 'Password must contain letters and at least one digit',
            ];
            $validators[] = new Constraints\Regex($regex);
        }

        $validators[] = new Constraints\Length(['min' => 6]);

        $constraints = new Constraints\Collection(
            [
                'password' => $validators,
            ]
        );

        return $this->_validateArgument($input, $output, $argumentName, $input->getArgument($argumentName), $constraints);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $name
     * @param $value
     * @param $constraints
     * @return mixed
     */
    protected function _validateArgument(InputInterface $input, OutputInterface $output, $name, $value, $constraints)
    {
        $validator = $this->initValidator();
        $errors = [];

        if (!empty($value)) {
            $errors = $validator->validate([$name => $value], $constraints);
            if (count($errors) > 0) {
                $output->writeln('<error>' . $errors[0]->getMessage() . '</error>');
            }
        }

        if (count($errors) > 0 || empty($value)) {
            $question = new Question('<question>' . ucfirst($name) . ': </question>');
            $question->setValidator(function ($typeInput) use ($validator, $constraints, $name) {
                $errors = $validator->validate([$name => $typeInput], $constraints);
                if (count($errors) > 0) {
                    throw new InvalidArgumentException($errors[0]->getMessage());
                }

                return $typeInput;
            });

            /** @var $questionHelper QuestionHelper */
            $questionHelper = $this->getHelperSet()->get('question');
            $value = $questionHelper->ask($input, $output, $question);

            return $value;
        }
        return $value;
    }

    /**
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected function initValidator()
    {
        if (null === $this->validator) {
            $this->validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
                ->setConstraintValidatorFactory(new ConstraintValidatorFactory())
                ->setMetadataFactory(new FakeMetadataFactory())
                ->getValidator();
        }

        return $this->validator;
    }
}
