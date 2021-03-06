<?php

namespace Mannion007\BestInvestments\Prospecting\CommandHandler;

use Mannion007\Interfaces\Command\CommandInterface;
use Mannion007\Interfaces\CommandHandler\CommandHandlerInterface;
use Mannion007\BestInvestments\Prospecting\Command\GiveUpOnProspectCommand;
use Mannion007\BestInvestments\Prospecting\Domain\ProspectId;
use Mannion007\BestInvestments\Prospecting\Domain\ProspectRepositoryInterface;

class GiveUpOnProspectHandler implements CommandHandlerInterface
{
    private $prospectRepository;

    public function __construct(ProspectRepositoryInterface $prospectRepository)
    {
        $this->prospectRepository = $prospectRepository;
    }

    public function handle(CommandInterface $command): void
    {
        $giveUpCommand = GiveUpOnProspectCommand::fromPayload($command->getPayload());
        $prospect = $this->prospectRepository->getByProspectId(
            ProspectId::fromExisting($giveUpCommand->getProspectId())
        );
        $prospect->giveUp();
        $this->prospectRepository->save($prospect);
    }
}
