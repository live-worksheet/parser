<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\Variable;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use LiveWorksheet\Parser\Markdown\AbstractInputContext;
use LiveWorksheet\Parser\Markdown\InputContextInterface;
use LiveWorksheet\Parser\Markdown\Variable\Variable;
use LiveWorksheet\Parser\Markdown\Variable\VariableRenderer;
use LiveWorksheet\Parser\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class VariableRendererTest extends TestCase
{
    /**
     * @dataProvider provideVariables
     */
    public function testRendersVariables(Variable $variable, string $expectedOutput, InputContextInterface $input = null): void
    {
        $renderer = new VariableRenderer();

        if (null !== $input) {
            $renderer->setInput($input);
        }

        $output = $renderer->render(
            $variable,
            $this->createMock(ElementRendererInterface::class)
        );

        self::assertEquals($expectedOutput, $output);
    }

    public function provideVariables(): \Generator
    {
        yield 'swallow if no input is defined' => [
            new Variable('Bar'),
            '',
        ];

        $input = new class() extends AbstractInputContext {
            public function getVariablePlaceholder(string $name): string
            {
                $placeholder = parent::getVariablePlaceholder($name);

                // Make variable placeholders distinguishable in the output
                return '' !== $placeholder ? sprintf('?%s', $placeholder) : '';
            }
        };

        $input->setParameters([
            new Parameter('Foo', '1 + 2'),
        ]);

        yield 'mapped variable' => [
            new Variable('Foo'),
            'Foo',
            $input,
        ];

        yield 'mapped variable placeholder' => [
            new Variable('Foo', true),
            '?Foo',
            $input,
        ];

        yield 'unmapped variable' => [
            new Variable('Bar'),
            "!unknown variable 'Bar'!",
            $input,
        ];

        yield 'unmapped variable placeholder' => [
            new Variable('Bar', true),
            "!unknown variable 'Bar'!",
            $input,
        ];
    }

    public function testThrowsOnInvalidType(): void
    {
        $renderer = new VariableRenderer();

        $inline = $this->createMock(AbstractInline::class);
        $htmlRenderer = $this->createMock(ElementRendererInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/Incompatible inline type: '\\S+'\\./");

        $renderer->render($inline, $htmlRenderer);
    }
}
