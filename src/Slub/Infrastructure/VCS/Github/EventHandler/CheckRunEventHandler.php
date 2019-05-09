<?php

declare(strict_types=1);

namespace Slub\Infrastructure\VCS\Github\EventHandler;

use Slub\Application\CIStatusUpdate\CIStatusUpdate;
use Slub\Application\CIStatusUpdate\CIStatusUpdateHandler;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 */
class CheckRunEventHandler implements EventHandlerInterface
{
    private const CHECK_RUN_EVENT_TYPE = 'check_run';

    /** @var string[] */
    private $supportedCheckRunNames;

    /** @var CIStatusUpdateHandler */
    private $CIStatusUpdateHandler;

    public function __construct(CIStatusUpdateHandler $CIStatusUpdateHandler, string $supportedCheckRunNames)
    {
        $this->CIStatusUpdateHandler = $CIStatusUpdateHandler;
        $this->supportedCheckRunNames = explode(',', $supportedCheckRunNames);
    }

    public function supports(string $eventType): bool
    {
        return self::CHECK_RUN_EVENT_TYPE === $eventType;
    }

    public function handle(Request $request): void
    {
        $CIStatusUpdate = $this->getCIStatusUpdate($request);
        if ($this->isCICheckGreenButNotSupported($CIStatusUpdate)) {
            return;
        }

        $this->updateCIStatus($CIStatusUpdate);
    }

    private function isCICheckGreenButNotSupported(array $CIStatusUpdate): bool
    {
        $isGreen = 'GREEN' === $this->getStatus($CIStatusUpdate);
        $isSupported = in_array($CIStatusUpdate['check_run']['name'], $this->supportedCheckRunNames);

        return $isGreen && !$isSupported;
    }

    private function getCIStatusUpdate(Request $request): array
    {
        return json_decode((string) $request->getContent(), true);
    }

    private function updateCIStatus(array $CIStatusUpdate): void
    {
        $command = new CIStatusUpdate();
        $command->PRIdentifier = $this->getPRIdentifier($CIStatusUpdate);
        $command->repositoryIdentifier = $CIStatusUpdate['repository']['full_name'];
        $command->status = $this->getStatus($CIStatusUpdate);
        $this->CIStatusUpdateHandler->handle($command);
    }

    private function getPRIdentifier(array $CIStatusUpdate): string
    {
        return sprintf(
            '%s/%s',
            $CIStatusUpdate['repository']['full_name'],
            $CIStatusUpdate['check_run']['check_suite']['pull_requests'][0]['number']
        );
    }

    private function getStatus(array $CIStatusUpdate): string
    {
        if ('queued' === $CIStatusUpdate['check_run']['status']) {
            return 'PENDING';
        }

        $conclusion = $CIStatusUpdate['check_run']['conclusion'];
        if ('success' === $conclusion) {
            return 'GREEN';
        }
        if ('failure' === $conclusion) {
            return 'RED';
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Expected conclusion to be one of "success" or "failure", but "%s" found',
                $conclusion
            )
        );
    }
}
