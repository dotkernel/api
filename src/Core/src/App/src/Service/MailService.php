<?php

declare(strict_types=1);

namespace Core\App\Service;

use Core\App\Message;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Log\LoggerInterface;
use Dot\Mail\Exception\MailException;
use Mezzio\Template\TemplateRendererInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use function sprintf;

class MailService
{
    #[Inject(
        TemplateRendererInterface::class,
        'dot-mail.service.default',
        'dot-log.default_logger',
        'config',
    )]
    public function __construct(
        private readonly TemplateRendererInterface $templateRenderer,
        protected \Dot\Mail\Service\MailService $mailService,
        protected LoggerInterface $logger,
        private readonly array $config,
    ) {
    }

    /**
     * @throws MailException
     */
    public function sendActivationMail(User $user): bool
    {
        if ($user->isActive()) {
            return false;
        }

        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject('Welcome to ' . $this->config['application']['name']);
        $this->mailService->setBody(
            $this->templateRenderer->render('user::activate', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException | TransportExceptionInterface $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }

    /**
     * @throws MailException
     */
    public function sendResetPasswordRequestedMail(User $user): bool
    {
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject(
            'Reset password instructions for your ' . $this->config['application']['name'] . ' account'
        );
        $this->mailService->setBody(
            $this->templateRenderer->render('user::reset-password-requested', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException | TransportExceptionInterface $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }

    /**
     * @throws MailException
     */
    public function sendResetPasswordCompletedMail(User $user): bool
    {
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject(
            'You have successfully reset the password for your ' . $this->config['application']['name'] . ' account'
        );
        $this->mailService->setBody(
            $this->templateRenderer->render('user::reset-password-completed', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException | TransportExceptionInterface $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }

    /**
     * @throws MailException
     */
    public function sendWelcomeMail(User $user): bool
    {
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject('Welcome to ' . $this->config['application']['name']);
        $this->mailService->setBody(
            $this->templateRenderer->render('user::welcome', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException | TransportExceptionInterface $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }

    /**
     * @throws MailException
     */
    public function sendRecoverIdentityMail(User $user): bool
    {
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject(
            'Recover identity for your ' . $this->config['application']['name'] . ' account'
        );
        $this->mailService->setBody(
            $this->templateRenderer->render('user::recover-identity-requested', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException | TransportExceptionInterface $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }
}
