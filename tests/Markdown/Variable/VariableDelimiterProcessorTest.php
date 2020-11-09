<?php

declare(strict_types=1);

/*
 * (c) Moritz Vondano
 *
 * @license LGPL-3.0-or-later
 */

namespace LiveWorksheet\Parser\Tests\Markdown\Variable;

use League\CommonMark\Delimiter\DelimiterInterface;
use League\CommonMark\Inline\Element\AbstractStringContainer;
use League\CommonMark\Inline\Element\Text;
use LiveWorksheet\Parser\Markdown\Variable\Variable;
use LiveWorksheet\Parser\Markdown\Variable\VariableDelimiterProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VariableDelimiterProcessorTest extends TestCase
{
    public function testNonDynamicProperties(): void
    {
        $processor = new VariableDelimiterProcessor();

        self::assertEquals('{', $processor->getOpeningCharacter());
        self::assertEquals('}', $processor->getClosingCharacter());

        self::assertEquals('2', $processor->getMinLength());
    }

    /**
     * @dataProvider provideDelimiterUseSamples
     */
    public function testGetDelimiterUse(int $openerLength, int $closerLength, int $expectedUse): void
    {
        /** @var DelimiterInterface&MockObject $opener */
        $opener = $this->createMock(DelimiterInterface::class);
        $opener
            ->method('getLength')
            ->willReturn($openerLength)
        ;

        /** @var DelimiterInterface&MockObject $closer */
        $closer = $this->createMock(DelimiterInterface::class);
        $closer
            ->method('getLength')
            ->willReturn($closerLength)
        ;

        $processor = new VariableDelimiterProcessor();

        self::assertEquals(
            $expectedUse,
            $processor->getDelimiterUse($opener, $closer)
        );
    }

    public function provideDelimiterUseSamples(): \Generator
    {
        // openerLength, closerLength, expectedUse

        yield '{{foo}}' => [
            2, 2, 2,
        ];

        yield '{{{{foo}}}' => [
            4, 3, 3,
        ];

        yield '{{foo}}}}' => [
            2, 4, 2,
        ];

        yield '{{foo}' => [
            2, 1, 0,
        ];

        yield '{foo}}}' => [
            1, 3, 0,
        ];
    }

    public function testProcessSkipsNonTextNodes(): void
    {
        /** @var AbstractStringContainer&MockObject $element */
        $element = $this->createMock(AbstractStringContainer::class);
        $element
            ->expects(self::never())
            ->method('getContent')
        ;

        /** @var AbstractStringContainer&MockObject $opener */
        $opener = $this->createMock(AbstractStringContainer::class);
        $opener
            ->expects(self::once())
            ->method('next')
            ->willReturn($element)
        ;

        /** @var AbstractStringContainer&MockObject $closer */
        $closer = $this->createMock(AbstractStringContainer::class);

        $processor = new VariableDelimiterProcessor();

        $processor->process($opener, $closer, 2);
    }

    /**
     * @dataProvider provideVariables
     */
    public function testProcessMatchesVariables(string $content, string $variableName, bool $isInput): void
    {
        /** @var Text&MockObject $text */
        $text = $this->createMock(Text::class);

        $text
            ->method('getContent')
            ->willReturn($content)
        ;

        $text
            ->expects(self::once())
            ->method('detach')
        ;

        /** @var AbstractStringContainer&MockObject $opener */
        $opener = $this->createMock(AbstractStringContainer::class);
        $opener
            ->method('next')
            ->willReturn($text)
        ;

        $matchVariable = static fn (Variable $v): bool => $variableName === $v->getName() && $isInput === $v->isInput();

        $opener
            ->expects(self::once())
            ->method('insertAfter')
            ->with(self::callback($matchVariable))
        ;

        /** @var AbstractStringContainer&MockObject $closer */
        $closer = $this->createMock(AbstractStringContainer::class);

        $processor = new VariableDelimiterProcessor();

        $processor->process($opener, $closer, 2);
    }

    public function provideVariables(): \Generator
    {
        yield 'regular' => [
            'Foo', 'Foo', false,
        ];

        yield 'input' => [
            'Foo?', 'Foo', true,
        ];

        yield 'additional characters' => [
            'Foo?Bar', 'Foo', true,
        ];

        yield 'special characters' => [
            'F_o o+Bar', 'F_o o+Bar', false,
        ];
    }
}
