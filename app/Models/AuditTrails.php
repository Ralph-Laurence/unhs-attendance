<?php

namespace App\Models;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;

use Hashids\Hashids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

// This is a mask for the Audit model class. We don't want to 
// mess up the original Audit model.

class AuditTrails extends Model
{
    use HasFactory;

    public const f_Audit_ID     = 'id';
    public const f_User_Type    = 'user_type';
    public const f_User_FK      = 'user_id';
    public const f_Event        = 'event';
    public const f_Model_Type   = 'auditable_type'; // The class name of the model that was audited
    public const f_Model_Id     = 'auditable_id';   // The record id of the audited model
    public const f_Old_Values   = 'old_values';
    public const f_New_Values   = 'new_values';
    public const f_Url          = 'url';
    public const f_Ip_Address   = 'ip_address';
    public const f_User_Agent   = 'user_agent';

    public const HASH_SALT = 'BEEFC0DE'; // Just random string, nothing special
    public const MIN_HASH_LENGTH = 10;

    private function hashDecode(string $hash)
    {
        $hashids = new Hashids(self::HASH_SALT, self::MIN_HASH_LENGTH);
        return $hashids->decode($hash)[0];
    }

    public static function getTableName()
    {
        return Constants::TABLE_AUDIT_TRAILS;
    }

    private array $actionIcons = [
        Constants::AUDIT_EVENT_CREATE => 'action-create',
        Constants::AUDIT_EVENT_UPDATE => 'action-update',
        Constants::AUDIT_EVENT_DELETE => 'action-delete',
    ];

    public function viewAuditDetails(int $id)
    {
        try 
        {
            $row = $this->buildQueryViewDetails($id)->first();

            if (!$row)
                throw new ModelNotFoundException;

            // Change the auditable type to human-readable names
            $this->setAuditableModelFriendlyName($row);
            
            return json_encode([
                'code'    => Constants::XHR_STAT_OK,
                'dataset' => $row
            ]);
        } 
        catch (ModelNotFoundException $ex) 
        {
            error_log($ex->getMessage());
            // When no records of audit trails were found
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INEXISTENT);
        } 
        catch (\Exception $ex) 
        {
            error_log($ex->getMessage());
            // Common errors
            return Extensions::encodeFailMessage(Messages::READ_RECORD_FAIL);
        }
    }

    public function getBasic()
    {
        $dataset  = $this->buildQueryBasic()->get();
        $hashids  = new Hashids(self::HASH_SALT, self::MIN_HASH_LENGTH);
    
        foreach ($dataset as $row) 
        {
            $this->beautifyBasicAuditResults($row, $hashids);
        }
    
        return $dataset;
    }

    private function beautifyBasicAuditResults(&$row, $hashids)
    {
        $row->id = $hashids->encode($row->id);

        // Change the auditable type to human-readable names
        $this->setAuditableModelFriendlyName($row);

        $recordFromAffected = 'record from ' . $row->affected;

        // Describe the actions
        switch ($row->action) 
        {
            case Constants::AUDIT_EVENT_CREATE:
                $row->description = "Added a $recordFromAffected.";
                return;

            case Constants::AUDIT_EVENT_UPDATE:
                if (empty($row->new_values)) 
                {
                    $row->description = "Modified a $recordFromAffected.";
                    return;
                }

                $description = [];

                foreach (json_decode($row->new_values, true) as $field => $value) 
                {
                    $description[] = "$field into '$value'";
                }

                $row->description = 'Changed ' . implode(', ', $description);
                return;

            case Constants::AUDIT_EVENT_DELETE:
                $row->description = "Removed a $recordFromAffected.";
                return;
        }
    }

    private function setAuditableModelFriendlyName(&$row)
    {
        if (method_exists($row->affected, 'getFriendlyName'))
            $row->affected = $row->affected::getFriendlyName();
        else
            $row->affected = 'Unknown';
    }

    private function buildQueryBasic(): Builder
    {
        $fields = Extensions::prefixArray('a.', [
            'id',
            self::f_Event        . ' as action',
            self::f_Model_Type   . ' as affected',
            self::f_Old_Values   . ' as old_values',
            self::f_New_Values   . ' as new_values',
        ]);

        $query = DB::table(self::getTableName() . ' as a')
            ->select($fields)
            ->selectRaw(
                "CASE 
                       WHEN DATE(a.created_at) = CURDATE() THEN 'Today'
                       ELSE DATE_FORMAT(a.created_at, '%b %d, %Y')
                   END as `date`,
                   CONCAT(e.firstname,' ',e.lastname) AS adminname"
            )
            ->selectRaw(Extensions::mapCaseWhen($this->actionIcons, 'a.' . self::f_Event, 'action_icon'))
            ->selectRaw(Extensions::time_format_hip('a.created_at'))
            ->leftJoin('users as e', 'e.id', '=', 'a.' . self::f_User_FK)
            ->orderBy('a.created_at', 'DESC');

        return $query;
    }

    private function buildQueryViewDetails(int $id) : Builder
    {
        $select = array_merge(
            Extensions::prefixArray('a.', [
                self::f_Event      . ' as action',
                self::f_Model_Type . ' as affected',
                self::f_Old_Values . ' as old_values',
                self::f_New_Values . ' as new_values',
                self::f_Url        . ' as url',
                self::f_Ip_Address . ' as ip',
                self::f_User_Agent . ' as ua',
            ]), 
            [
                DB::raw("CONCAT_WS(' ', u.firstname, u.lastname) as user"),
                'u.username as username',
                DB::raw(Extensions::time_format_hip('a.created_at')),
                DB::raw(Extensions::date_format_bdY('a.created_at'))
            ]);

        $query = DB::table(self::getTableName(), 'a')
            ->leftJoin('users as u', 'u.id', '=', 'a.' . self::f_User_FK)
            ->where('a.id', '=', $id)
            ->select($select);

        return $query;
    }
}
