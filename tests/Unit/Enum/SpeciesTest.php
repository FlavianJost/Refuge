<?php

namespace App\Tests\Unit\Enum;

use App\Enum\Species;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class SpeciesTest extends TestCase
{
    #[DataProvider('provideLabels')]
    public function testLabel(Species $species, string $expected): void
    {
        self::assertSame($expected, $species->label());
    }

    #[DataProvider('provideLabels')]
    public function testTransDelegatesToTranslator(Species $species, string $expected): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with($expected, [], null, null)
            ->willReturn('translated');

        self::assertSame('translated', $species->trans($translator));
    }

    public function testTransForwardsLocale(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with('Chien', [], null, 'en')
            ->willReturn('Dog');

        self::assertSame('Dog', Species::DOG->trans($translator, 'en'));
    }

    /**
     * @return iterable<string, array{Species, string}>
     */
    public static function provideLabels(): iterable
    {
        yield 'dog' => [Species::DOG, 'Chien'];
        yield 'cat' => [Species::CAT, 'Chat'];
        yield 'rabbit' => [Species::RABBIT, 'Lapin'];
        yield 'bird' => [Species::BIRD, 'Oiseau'];
        yield 'rodent' => [Species::RODENT, 'Rongeur'];
        yield 'other' => [Species::OTHER, 'Autre'];
    }
}
