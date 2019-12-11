<?php

declare(strict_types=1);

namespace Akeneo\Apps\Audit\Application\Query;

use Akeneo\Apps\Audit\Domain\Persistence\Query\SelectAppsEventCountByDayQuery;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByAppHandler
{
    /** @var SelectAppsEventCountByDayQuery */
    private $selectAppsEventCountByDateQuery;

    public function __construct(SelectAppsEventCountByDayQuery $selectAppsEventCountByDateQuery)
    {
        $this->selectAppsEventCountByDateQuery = $selectAppsEventCountByDateQuery;
    }

    public function handle(CountDailyEventsByAppQuery $query): array
    {
        $eventCountByApps = $this
            ->selectAppsEventCountByDateQuery
            ->execute($query->eventType(), $query->startDate(), $query->endDate());

        return $eventCountByApps;
    }
}
