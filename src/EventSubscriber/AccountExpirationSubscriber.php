<?php

namespace App\EventSubscriber;

use App\Service\SubscriptionChecker;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AccountExpirationSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private SubscriptionChecker $subscriptionChecker;

    public function __construct(Security $security, SubscriptionChecker $subscriptionChecker)
    {
        $this->security = $security;
        $this->subscriptionChecker = $subscriptionChecker;
    }

    public static function getSubscribedEvents(): array
    {
        // On exécute après l'authentification du firewall (priorité < 8, on choisit 0)
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user || !method_exists($user, 'getPersonne')) {
            return;
        }

        $personne = $user->getPersonne();
        
        if ($personne) {
            $this->subscriptionChecker->checkExpiration($personne);
        }
    }
}
