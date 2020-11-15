<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\VersionControl\GitMergeConflictSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\LanguageConstructSpacingSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\SuperfluousWhitespaceSniff;
use PhpCsFixer\Fixer\CastNotation\NoUnsetCastFixer;
use PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Comment\MultilineCommentOpeningClosingFixer;
use PhpCsFixer\Fixer\ControlStructure\NoAlternativeSyntaxFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\FunctionNotation\CombineNestedDirnameFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoUnreachableDefaultArgumentValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\StaticLambdaFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\NoUnsetOnPropertyFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\Operator\LogicalOperatorsFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarAnnotationCorrectOrderFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpTag\NoShortEchoTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertInternalTypeFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMockFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitNamespacedFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitNoExpectationAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitOrderedCoversFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitSetUpTearDownVisibilityFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestCaseStaticMethodCallsFixer;
use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\StringNotation\EscapeImplicitBackslashesFixer;
use PhpCsFixer\Fixer\StringNotation\HeredocToNowdocFixer;
use PhpCsFixer\Fixer\StringNotation\NoBinaryStringFixer;
use PhpCsFixer\Fixer\StringNotation\SimpleToComplexStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\StringLineEndingFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypehintFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use SlevomatCodingStandard\Sniffs\Classes\DisallowMultiConstantDefinitionSniff;
use SlevomatCodingStandard\Sniffs\Classes\ModernClassNameReferenceSniff;
use SlevomatCodingStandard\Sniffs\Classes\TraitUseDeclarationSniff;
use SlevomatCodingStandard\Sniffs\Classes\TraitUseSpacingSniff;
use SlevomatCodingStandard\Sniffs\Commenting\EmptyCommentSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireShortTernaryOperatorSniff;
use SlevomatCodingStandard\Sniffs\Functions\UnusedInheritedVariablePassedToClosureSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UnusedUsesSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UselessAliasSniff;
use SlevomatCodingStandard\Sniffs\Operators\RequireCombinedAssignmentOperatorSniff;
use SlevomatCodingStandard\Sniffs\PHP\DisallowDirectMagicInvokeCallSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessSemicolonSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullTypeHintOnLastPositionSniff;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff;
use SlevomatCodingStandard\Sniffs\Whitespaces\DuplicateSpacesSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\Strict\BlankLineAfterStrictTypesFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/vendor/symplify/easy-coding-standard/config/set/symfony.php');
    $containerConfigurator->import(__DIR__ . '/vendor/symplify/easy-coding-standard/config/set/symfony-risky.php');

    $services = $containerConfigurator->services();

    $services
        ->set(HeaderCommentFixer::class)
        ->call('configure', [[
            'header' => "(c) Moritz Vondano\n\n@license LGPL-3.0-or-later",
        ]]);

    $services
        ->set(BlankLineBeforeStatementFixer::class)
        ->call('configure', [[
            //  Enforce blank lines everywhere except before "break", "continue" and "yield"
            'statements' => ['case', 'declare', 'default', 'do', 'for', 'foreach', 'if', 'return', 'switch', 'throw', 'try', 'while'],
        ]])
    ;

    $services
        ->set(DuplicateSpacesSniff::class)
        ->property('ignoreSpacesInAnnotation', true)
    ;

    $services
        ->set(ListSyntaxFixer::class)
        ->call('configure', [[
            'syntax' => 'short',
        ]])
    ;

    $services
        ->set(MultilineWhitespaceBeforeSemicolonsFixer::class)
        ->call('configure', [[
            'strategy' => 'new_line_for_chained_calls',
        ]])
    ;

    $services
        ->set(PhpUnitTestCaseStaticMethodCallsFixer::class)
        ->call('configure', [[
            'call_type' => 'self',
        ]])
    ;

    $services
        ->set(SuperfluousWhitespaceSniff::class)
        ->property('ignoreBlankLines', false)
    ;

    $services
        ->set(TraitUseSpacingSniff::class)
        ->property('linesCountAfterLastUse', 1)
        ->property('linesCountAfterLastUseWhenLastInClass', 0)
        ->property('linesCountBeforeFirstUse', 0)
        ->property('linesCountBetweenUses', 0)
    ;

    $services->set(AlignMultilineCommentFixer::class);
    $services->set(ArrayIndentationFixer::class);
    $services->set(BlankLineAfterStrictTypesFixer::class);
    $services->set(CombineConsecutiveIssetsFixer::class);
    $services->set(CombineConsecutiveUnsetsFixer::class);
    $services->set(CombineNestedDirnameFixer::class);
    $services->set(CompactNullableTypehintFixer::class);
    $services->set(DeclareStrictTypesFixer::class);
    $services->set(DisallowDirectMagicInvokeCallSniff::class);
    $services->set(DisallowMultiConstantDefinitionSniff::class);
    $services->set(EmptyCommentSniff::class);
    $services->set(EscapeImplicitBackslashesFixer::class);
    $services->set(FullyQualifiedStrictTypesFixer::class);
    $services->set(GitMergeConflictSniff::class);
    $services->set(HeredocToNowdocFixer::class);
    $services->set(LanguageConstructSpacingSniff::class);
    $services->set(LinebreakAfterOpeningTagFixer::class);
    $services->set(LogicalOperatorsFixer::class);
    $services->set(MethodChainingIndentationFixer::class);
    $services->set(ModernClassNameReferenceSniff::class);
    $services->set(MultilineCommentOpeningClosingFixer::class);
    $services->set(NoAlternativeSyntaxFixer::class);
    $services->set(NoBinaryStringFixer::class);
    $services->set(NoNullPropertyInitializationFixer::class);
    $services->set(NoShortEchoTagFixer::class);
    $services->set(NoSuperfluousElseifFixer::class);
    $services->set(NoUnreachableDefaultArgumentValueFixer::class);
    $services->set(NoUnsetCastFixer::class);
    $services->set(NoUnsetOnPropertyFixer::class);
    $services->set(NoUselessElseFixer::class);
    $services->set(NoUselessReturnFixer::class);
    $services->set(NullTypeHintOnLastPositionSniff::class);
    $services->set(OrderedClassElementsFixer::class);
    $services->set(ParamReturnAndVarTagMalformsFixer::class);
    $services->set(PhpdocLineSpanFixer::class);
    $services->set(PhpdocNoEmptyReturnFixer::class);
    $services->set(PhpdocOrderFixer::class);
    $services->set(PhpdocVarAnnotationCorrectOrderFixer::class);
    $services->set(PhpUnitDedicateAssertInternalTypeFixer::class);
    $services->set(PhpUnitExpectationFixer::class);
    $services->set(PhpUnitMethodCasingFixer::class);
    $services->set(PhpUnitMockFixer::class);
    $services->set(PhpUnitNamespacedFixer::class);
    $services->set(PhpUnitNoExpectationAnnotationFixer::class);
    $services->set(PhpUnitOrderedCoversFixer::class);
    $services->set(PhpUnitSetUpTearDownVisibilityFixer::class);
    $services->set(PhpUnitTestAnnotationFixer::class);
    $services->set(ProtectedToPrivateFixer::class);
    $services->set(ReturnAssignmentFixer::class);
    $services->set(RequireCombinedAssignmentOperatorSniff::class);
    $services->set(RequireShortTernaryOperatorSniff::class);
    $services->set(SimpleToComplexStringVariableFixer::class);
    $services->set(StaticLambdaFixer::class);
    $services->set(StrictComparisonFixer::class);
    $services->set(StrictParamFixer::class);
    $services->set(StringLineEndingFixer::class);
    $services->set(TernaryToNullCoalescingFixer::class);
    $services->set(TraitUseDeclarationSniff::class);
    $services->set(UnusedInheritedVariablePassedToClosureSniff::class);
    $services->set(UnusedVariableSniff::class);
    $services->set(UselessAliasSniff::class);
    $services->set(UselessParenthesesSniff::class);
    $services->set(UselessSemicolonSniff::class);
    $services->set(UselessVariableSniff::class);
    $services->set(VoidReturnFixer::class);

    // Add sniffs from https://github.com/slevomat/coding-standard
    $services
        ->set(ReferenceUsedNamesOnlySniff::class)
        ->property('searchAnnotations', true)
        ->property('allowFullyQualifiedNameForCollidingClasses', true)
        ->property('allowFullyQualifiedGlobalClasses', true)
        ->property('allowFullyQualifiedGlobalFunctions', true)
        ->property('allowFullyQualifiedGlobalConstants', true)
        ->property('allowPartialUses', false)
    ;

    $services
        ->set(UnusedUsesSniff::class)
        ->property('searchAnnotations', true)
    ;

    $services->set(DisallowArrayTypeHintSyntaxSniff::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SKIP, [
        MethodChainingIndentationFixer::class => [
            'src/Parameter/Configuration/*Configuration.php',
        ],
    ]);

    $parameters->set(Option::LINE_ENDING, "\n");
    $parameters->set(Option::EXCLUDE_PATHS, ['*/Resources/*', '*/Fixtures/system/*']);
    $parameters->set(Option::CACHE_DIRECTORY, sys_get_temp_dir() . '/ecs_default_cache');
};
