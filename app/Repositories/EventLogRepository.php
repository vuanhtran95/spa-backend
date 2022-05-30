<?php

namespace App\Repositories;

use App\EventLog;
use App\Constants\EventLog as EventLogType;

class EventLogRepository
{
    private $message = [
        EventLogType::CUSTOMER_POINT_DEDUCTED => "Khách sử dụng {cashPoints} điểm tích luỹ hiện tại để giảm giá",
        EventLogType::CUSTOMER_REWARD_REMAINING_POINT_DEDUCTED => "Khách sử dụng {remainingRewardPoints} điểm tích luỹ năm ngoái để giảm giá",
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
                    'message' => str_replace($data['placeholder'], $data['value'], $this->message[$eventType]),
                    'target_object_id' => $data['targetObjectId'],
                    'target_object_type' => $data['targetObjectType']
                ]);
            }
        }
    }
}
