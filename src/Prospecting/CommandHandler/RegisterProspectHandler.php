<?php

namespace Mannion007\BestInvestments\Prospecting\CommandHandler;

use Mannion007\BestInvestments\Prospecting\Domain\HourlyRate;
use Mannion007\Interfaces\Command\CommandInterface;
use Mannion007\Interfaces\CommandHandler\CommandHandlerInterface;
use Mannion007\BestInvestments\Prospecting\Command\RegisterProspectCommand;
use Mannion007\BestInvestments\Prospecting\Domain\ProspectId;
use Mannion007\BestInvestments\Prospecting\Domain\ProspectRepositoryInterface;
use Mannion007\ValueObjects\Currency;

class RegisterProspectHandler implements CommandHandlerInterface
{
    private $prospectRepository;

    public function __construct(ProspectRepositoryInterface $prospectRepository)
    {
        $this->prospectRepository = $prospectRepository;
    }

    public function handle(CommandInterface $command): void
    {
        $registerCommand = RegisterProspectCommand::fromPayload($command->getPayload());
        $prospect = $this->prospectRepository->getByProspectId(
            ProspectId::fromExisting($registerCommand->getProspectId())
        );
        $prospect->register(new HourlyRate($registerCommand->getHourlyRate(), Currency::gbp()));
        $this->prospectRepository->save($prospect);
    }
}
