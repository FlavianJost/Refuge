<?php

namespace App\Tests\Unit\Enum;

use App\Enum\AdoptionRequestStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdoptionRequestStatusTest extends TestCase
{
    #[DataProvider('provideCases')]
    public function testLabel(AdoptionRequestStatus $status, string $label, string $badge): void
    {
        self::assertSame($label, $status->label());
    }

    #[DataProvider('provideCases')]
    public function testBadgeClass(AdoptionRequestStatus $status, string $label, string $badge): void
    {
        self::assertSame($badge, $status->badgeClass());
    }

    #[DataProvider('provideCases')]
    public function testTransDelegatesToTranslator(AdoptionRequestStatus $status, string $label, string $badge): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with($label, [], null, null)
            ->willReturn('translated');

        self::assertSame('translated', $status->trans($translator));
    }

    /**
     * @return iterable<string, array{AdoptionRequestStatus, string, string}>
     */
    public static function provideCases(): iterable
    {
        yield 'pending' => [AdoptionRequestStatus::PENDING, 'Nouvelle', 'info'];
        yield 'in_progress' => [AdoptionRequestStatus::IN_PROGRESS, 'En cours de traitement', 'warning'];
        yield 'accepted' => [AdoptionRequestStatus::ACCEPTED, 'Acceptée', 'success'];
        yield 'rejected' => [AdoptionRequestStatus::REJECTED, 'Refusée', 'danger'];
    }
}
