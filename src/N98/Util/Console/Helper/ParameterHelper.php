<?php

namespace N98\Util\Console\Helper;

use Exception;
use InvalidArgumentException;
use N98\Util\Validator\FakeMetadataFactory;
use RuntimeException;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\Helper as AbstractHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidatorFactory;

/**
 * Helper to init some parameters
 */
class ParameterHelper extends AbstractHelper
{
    /**
     * @var Validator
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $argumentName
     * @param bool            $withDefaultStore [optional]
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
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
            $stores = array();
            $i = 0;

            foreach ($storeManager->getStores($withDefaultStore) as $store) {
                $stores[$i] = $store->getId();
                $question[] = sprintf(
                    '<comment>[%d]</comment> ' . $store->getCode() . ' - ' . $store->getName() . PHP_EOL,
                    ++$i
                );
            }

            if (count($stores) > 1) {
                $question[] = '<question>Please select a store: </question>';

                /** @var $dialog DialogHelper */
                $dialog = $this->getHelperSet()->get('dialog');
                $storeId = $dialog->askAndValidate(
                    $output,
                    $question,
                    function ($typeInput) use ($stores) {
                        if (!isset($stores[$typeInput - 1])) {
                            throw new InvalidArgumentException('Invalid store');
                        }

                        return $stores[$typeInput - 1];
                    }
                );
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
            $websites = array();
            foreach ($storeManager->getWebsites() as $website) {
                $websites[$i] = $website->getId();
                $question[] = sprintf(
                    '<comment>[%d]</comment> ' . $website->getCode() . ' - ' . $website->getName() . PHP_EOL,
                    ++$i
                );
            }
            if (count($websites) == 1) {
                return $storeManager->getWebsite($websites[0]);
            }
            $question[] = '<question>Please select a website: </question>';

            /** @var $dialog DialogHelper */
            $dialog = $this->getHelperSet()->get('dialog');
            $websiteId = $dialog->askAndValidate(
                $output,
                $question,
                function ($typeInput) use ($websites) {
                    if (!isset($websites[$typeInput - 1])) {
                        throw new InvalidArgumentException('Invalid store');
                    }

                    return $websites[$typeInput - 1];
                }
            );

            $website = $storeManager->getWebsite($websiteId);
        }

        return $website;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $argumentName
     *
     * @return string
     */
    public function askEmail(InputInterface $input, OutputInterface $output, $argumentName = 'email')
    {
        $constraints = new Constraints\Collection(
            array(
                'email' => array(
                    new Constraints\NotBlank(),
                    new Constraints\Email()
                )
            )
        );

        return $this->_validateArgument($output, $argumentName, $input->getArgument($argumentName), $constraints);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $argumentName
     *
*@return string
     */
    public function askPassword(
        InputInterface $input,
        OutputInterface $output,
        $argumentName = 'password',
        $needDigits = true
    ) {
        $validators = array();

        if ($needDigits) {
            $regex = array(
                'pattern' => '/^(?=.*\d)(?=.*[a-zA-Z])/',
                'message' => 'Password must contain letters and at least one digit'
            );
            $validators[] = new Constraints\Regex($regex);
        }

        $validators[] = new Constraints\Length(array('min' => 6));

        $constraints = new Constraints\Collection(
            array(
                'password' => $validators
            )
        );

        return $this->_validateArgument($output, $argumentName, $input->getArgument($argumentName), $constraints);
    }

    /**
     * @param OutputInterface                                $output
     * @param string                                         $name
     * @param string                                         $value
     * @param Constraints\Collection|Constraint|Constraint[] $constraints The constraint(s) to validate against.
     *
     * @return mixed
     */
    protected function _validateArgument(OutputInterface $output, $name, $value, $constraints)
    {
        $validator = $this->initValidator();
        $errors    = null;

        if (!empty($value)) {
            $errors = $validator->validateValue(array($name => $value), $constraints);
            if (count($errors) > 0) {
                $output->writeln('<error>' . $errors[0]->getMessage() . '</error>');
            }
        }

        if (count($errors) > 0 || empty($value)) {
            $question = '<question>' . ucfirst($name) . ': </question>';

            /** @var $dialog DialogHelper */
            $dialog = $this->getHelperSet()->get('dialog');
            $value = $dialog->askAndValidate(
                $output,
                $question,
                function ($typeInput) use ($validator, $constraints, $name) {
                    $errors = $validator->validateValue(array($name => $typeInput), $constraints);
                    if (count($errors) > 0) {
                        throw new InvalidArgumentException($errors[0]->getMessage());
                    }

                    return $typeInput;
                }
            );
            return $value;
        }
        return $value;
    }

    /**
     * @return Validator
     */
    protected function initValidator()
    {
        if ($this->validator == null) {
            $factory = new ConstraintValidatorFactory();
            $this->validator = new Validator(new FakeMetadataFactory(), $factory, new Translator('en'));
        }

        return $this->validator;
    }
}
