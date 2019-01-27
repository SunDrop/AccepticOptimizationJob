<?php

namespace Acceptic\Publisher;

use Psr\Log\LoggerInterface;

class PublisherNotifier
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function notify(string $notificationType, ...$publisherIds): void
    {
        foreach ($publisherIds as $publisherId) {
            $publisher = Publisher::getById($publisherId);
            $template = $this->getTemplate($notificationType);
            $this->send($publisher, $template);
            $this->logger->info('VK20180125_03: Notification "{type}" send to publisher ({id})', [
                'type' => $notificationType,
                'id' => $publisherId,
            ]);
        }
    }

    private function getTemplate(string $notificationType): string
    {
        return $notificationType . ' <!-- TODO: not implemented -->';
    }

    private function send(Publisher $publisher, string $template): void
    {
        /**
         * Stub (!)
         * 1) Fill template
         * 2) Send email/viber/etc
         */
        printf('Notification "%s" send to publisher (%d)', $template, $publisher->getId());
    }
}
