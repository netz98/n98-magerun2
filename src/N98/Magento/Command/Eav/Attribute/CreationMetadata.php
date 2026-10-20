<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Eav\Attribute;

/** Validated, deliberately bounded subset of Magento's setup metadata. */
class CreationMetadata
{
    public function build(string $code, array $options): array
    {
        if (!preg_match('/^[a-z][a-z0-9_]{0,29}$/D', $code)) {
            throw new \InvalidArgumentException('Attribute code must start with a lowercase letter and contain at most 30 lowercase letters, digits or underscores.');
        }
        $label = trim((string) $options['label']);
        if ($label === '' || strlen($label) > 255 || preg_match('/[\x00-\x1f\x7f]/', $label)) {
            throw new \InvalidArgumentException('Label must contain 1-255 bytes without control characters.');
        }
        $types = ['text' => 'varchar', 'textarea' => 'text', 'date' => 'datetime', 'boolean' => 'int', 'select' => 'int', 'multiselect' => 'varchar'];
        $input = $options['input'];
        if (!isset($types[$input])) {
            throw new \InvalidArgumentException('Input must be text, textarea, date, boolean, select or multiselect.');
        }
        $data = ['label' => $label, 'input' => $input, 'type' => $types[$input], 'user_defined' => true, 'system' => false];
        foreach (['required', 'unique', 'visible'] as $flag) {
            if (!in_array($options[$flag], ['0', '1'], true)) {
                throw new \InvalidArgumentException($flag . ' must be 0 or 1.');
            }
            $data[$flag] = (int) $options[$flag];
        }
        $scopes = ['store' => 0, 'global' => 1, 'website' => 2];
        if (!isset($scopes[$options['scope']])) {
            throw new \InvalidArgumentException('Scope must be global, website or store.');
        }
        $data['global'] = $scopes[$options['scope']];
        if (!preg_match('/^[0-9]+$/D', $options['sort-order']) || (float) $options['sort-order'] > 32767) {
            throw new \InvalidArgumentException('Sort order must be an integer between 0 and 32767.');
        }
        $data['sort_order'] = (int) $options['sort-order'];
        $labels = $options['option'];
        if (in_array($input, ['select', 'multiselect'], true)) {
            if (!$labels || count($labels) !== count(array_unique($labels))) {
                throw new \InvalidArgumentException('Select inputs require unique --option labels.');
            }
            foreach ($labels as $option) {
                if (trim($option) !== $option || $option === '' || strlen($option) > 255 || preg_match('/[\x00-\x1f\x7f]/', $option)) {
                    throw new \InvalidArgumentException('Option labels must be non-empty, trimmed and at most 255 bytes without control characters.');
                }
            }
            $data['option'] = ['values' => array_values($labels)];
            if ($input === 'multiselect') {
                $data['backend'] = \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class;
            }
        } elseif ($labels) {
            throw new \InvalidArgumentException('--option is only supported for select and multiselect.');
        }
        if ($input === 'boolean') {
            $data['source'] = \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class;
        }
        if ($options['default'] !== null) {
            $default = $options['default'];
            if (in_array($input, ['select', 'multiselect', 'date'], true)) {
                throw new \InvalidArgumentException('--default is supported only for text, textarea and boolean inputs.');
            }
            if (
                ($input === 'boolean' && !in_array($default, ['0', '1'], true))
                || strlen($default) > ($input === 'textarea' ? 65535 : 255)
            ) {
                throw new \InvalidArgumentException('Invalid default value for this input.');
            }
            $data['default'] = $default;
        }

        return $data;
    }
}
