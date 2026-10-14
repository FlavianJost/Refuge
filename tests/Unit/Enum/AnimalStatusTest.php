<?php

namespace App\Tests\Unit\Enum;

use App\Enum\AnimalStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class AnimalStatusTest extends TestCase
{
    #[DataProvider('provideCases')]
    public function testLabel(AnimalStatus $status, string $label, string $badge): void
    {
        self::assertSame($label, $status->label());
    }

    #[DataProvider('provideCases')]
    public function testBadgeClass(AnimalStatus $status, string $label, string $badge): void
    {
        self::assertSame($badge, $status->badgeClass());
    }

    #[DataProvider('provideCases')]
    public function testTransDelegatesToTranslator(AnimalStatus $status, string $label, string $badge): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with($label, [], null, null)
            ->willReturn('translated');

        self::assertSame('translated', $status->trans($translator));
    }

    /**
     * @return iterable<string, array{AnimalStatus, string, string}>
     */
    public static function provideCases(): iterable
    {
        yield 'available' => [AnimalStatus::AVAILABLE, 'Disponible', 'success'];
        yield 'pending' => [AnimalStatus::PENDING, 'En cours d\'adoption', 'warning'];
        yield 'adopted' => [AnimalStatus::ADOPTED, 'Adopté', 'secondary'];
    }
}
