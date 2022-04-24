<?php

namespace App\Repositories;

use App\EventLog;
use App\Constants\EventLog as EventLogType;

class EventLogRepository
{
    private array $message = [
        EventLogType::CUSTOMER_POINT_DEDUCTED => "Customer has issued {cashPoints} for service payment",
        EventLogType::CUSTOMER_REWARD_REMAINING_POINT_DEDUCTED => "Customer has issued {remainingRewardPoints} for service payment",
    ];

    /**
     * @param array $data
     * @return mixed
     */
    public function create($data) {
        return EventLog::create($data);
    }

    /**
     * @param array $logs
     */
    public function storeCustomerRewardPointsEventLog($logs) {
        if (is_array($logs) && !empty($logs)) {
            foreach ($logs as $eventType => $data) {
                $this->create([
                    'entity_id' => $data['entityId'],
                    'event_type' => $eventType,
                    'message' => str_replace($data['placeholder'], $data['value'], $this->message[$eventType])
                ]);
            }
        }
    }
}
