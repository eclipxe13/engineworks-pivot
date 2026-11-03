<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 * @see https://cs.symfony.com/doc/ruleSets/
 * @see https://cs.symfony.com/doc/rules/
 */

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/build/php-cs-fixer.cache')
    ->setRules([
        '@PSR2' => true,
        'psr_autoloading' => false, // do not enforce psr-0, it rewrites 'namespace Test\' to 'namespace tests\'
        // symfony
        'whitespace_after_comma_in_array' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'type_declaration_spaces' => true,
        'no_alias_functions' => true,
        'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['arrays']], // different from PHP73Migration
        'new_with_braces' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'object_operator_without_whitespace' => true,
        'binary_operator_spaces' => true,
        'phpdoc_scalar' => true,
        'self_accessor' => true,
        'no_trailing_comma_in_singleline' => true,
        'single_quote' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_unused_imports' => true,
        'no_whitespace_in_blank_line' => true,
        // contrib
        'concat_space' => ['spacing' => 'one'],
        'not_operator_with_successor_space' => true,
        'no_blank_lines_before_namespace' => true,
        'linebreak_after_opening_tag' => true,
        'ordered_imports' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->append([__FILE__])
            ->exclude(['vendor', 'tools', 'build']),
    )
;
