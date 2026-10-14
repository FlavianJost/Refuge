<?php

namespace App\Controller\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class TwoFactorController extends AbstractController
{
    #[Route('/profil/2fa/activer', name: 'app_profile_2fa_enable', methods: ['GET', 'POST'])]
    public function enable(
        Request $request,
        EntityManagerInterface $entityManager,
        TotpAuthenticatorInterface $totpAuthenticator,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($user->isTotpAuthenticationEnabled()) {
            $this->addFlash('info', 'L\'authentification à deux facteurs est déjà activée.');

            return $this->redirectToRoute('app_profile');
        }

        if (null === $user->getTotpSecret()) {
            $user->setTotpSecret($totpAuthenticator->generateSecret());
            $entityManager->flush();
        }

        $error = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('totp-enable', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            $code = trim((string) $request->request->get('code'));

            if ('' !== $code && $totpAuthenticator->checkCode($user, $code)) {
                $user->setTotpEnabled(true);
                $entityManager->flush();

                $this->addFlash('success', 'L\'authentification à deux facteurs est maintenant activée.');

                return $this->redirectToRoute('app_profile');
            }

            $error = 'Le code saisi est incorrect. Merci de réessayer.';
        }

        return $this->render('User/two_factor_enable.html.twig', [
            'secret' => $user->getTotpSecret(),
            'qrCodeDataUri' => $this->buildQrCode($totpAuthenticator, $user),
            'error' => $error,
        ]);
    }

    #[Route('/profil/2fa/desactiver', name: 'app_profile_2fa_disable', methods: ['POST'])]
    public function disable(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('totp-disable', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $user->setTotpEnabled(false);
        $user->setTotpSecret(null);
        $entityManager->flush();

        $this->addFlash('success', 'L\'authentification à deux facteurs a été désactivée.');

        return $this->redirectToRoute('app_profile');
    }

    private function buildQrCode(TotpAuthenticatorInterface $totpAuthenticator, User $user): string
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($totpAuthenticator->getQRContent($user))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(220)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        return $result->getDataUri();
    }
}
