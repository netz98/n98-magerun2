<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Console\Helper;

use Exception;
use InvalidArgumentException;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use N98\Magento\Command\CommandAware;
use N98\Util\Validator\FakeMetadataFactory;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper as AbstractHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;

/**
 * Helper to init some parameters
 *
 * @package N98\Util\Console\Helper
 */
class ParameterHelper extends AbstractHelper implements CommandAware
{
    use CommandTrait;

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
        $storeManager = $this->getCommand()
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
            foreach ($storeManager->getStores($withDefaultStore) as $store) {
                $stores[$store->getId()] = sprintf(
                    '<comment>%s - %s</comment>',
                    $store->getCode(),
                    $store->getName()
                );
            }

            if (count($stores) > 1) {
                $storeId = select('Please select a store', $stores);
            } else {
                // only one store view available -> take it
                $storeId = array_key_first($stores);
            }

            $store = $storeManager->getStore((int) $storeId);
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
        $storeManager = $this->getCommand()
            ->getApplication()
            ->getObjectManager()
            ->get('Magento\Store\Model\StoreManagerInterface');

        try {
            if ($input->getArgument($argumentName) === null) {
                throw new RuntimeException('No website given');
            }
            $website = $storeManager->getWebsite($input->getArgument($argumentName));
        } catch (Exception $e) {
            $websites = [];
            foreach ($storeManager->getWebsites() as $website) {
                $websites[$website->getId()] = sprintf(
                    '<comment>%s - %s</comment>',
                    $website->getCode(),
                    $website->getName()
                );
            }

            if (count($websites) === 1) {
                return $storeManager->getWebsite(array_key_first($websites));
            }

            $websiteId = select('Please select a website', $websites);

            $website = $storeManager->getWebsite((int) $websiteId);
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

        return $this->_validateArgument($argumentName, $input->getArgument($argumentName), $constraints);
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

        return $this->_validateArgument($argumentName, $input->getArgument($argumentName), $constraints, true);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraints\Collection $constraints
     * @param bool $masked mask the input, e.g. for passwords
     * @return mixed
     */
    protected function _validateArgument($name, $value, $constraints, $masked = false)
    {
        $validator = $this->initValidator();

        $validate = function ($input) use ($validator, $constraints, $name): ?string {
            $errors = $validator->validate([$name => $input], $constraints);

            return count($errors) > 0 ? $errors[0]->getMessage() : null;
        };

        if (!empty($value) && $validate($value) === null) {
            return $value;
        }

        $label = sprintf('<question>%s:</question>', ucfirst($name));

        return $masked ? password($label, validate: $validate) : text($label, validate: $validate);
    }

    /**
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected function initValidator()
    {
        if (null === $this->validator) {
            $this->validator = Validation::createValidatorBuilder()
                ->setConstraintValidatorFactory(new ConstraintValidatorFactory())
                ->setMetadataFactory(new FakeMetadataFactory())
                ->getValidator();
        }

        return $this->validator;
    }
}
