<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(
        array_merge(
            explode(
                ',',
                'single_blank_line_before_namespace,no_blank_lines_after_class_opening,unused_use,ordered_use,' .
                'concat_with_spaces,spaces_cast,trailing_spaces,unalign_equals'
            ),
            array(
                'array_element_no_space_before_comma', 'array_element_white_space_after_comma',
                'multiline_array_trailing_comma',
                'join_function',
            )
        )
    )
    ->finder($finder)
    ->setUsingCache(true)
;
