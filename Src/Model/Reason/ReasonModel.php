<?php
declare(strict_types=1);
namespace ApiVacations\Model\Reason;

use ApiVacations\Model\AbstractModel;
use ApiVacations\Exceptions\AppException;

class ReasonModel extends AbstractModel
{
    /**
     * Get all reasons
     *
     * @param string $authorize
     * @return array
     */
    public function getReasons(string $authorize): array
    {
        if ($authorize === 'admin' or $authorize === 'user') {
            return $this->getReasonsFromDB();
        }
        http_response_code(403);
        throw new AppException('Forbidden', 403);
    }

    private function getReasonsFromDB(): array
    {
        $result = [];
        $sql = "
            SELECT id, name 
            FROM Reasons  
            ORDER BY name ASC 
        ";
        $rows = $this->db->selectProcess($sql, [], 'fetchAll');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }
}