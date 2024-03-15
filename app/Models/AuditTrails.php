<?php

namespace App\Models;

use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Models\Constants\AuditableTypesMapping;
use App\Models\Constants\FacultyConstants;
use App\Models\Constants\StaffConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public static function getTableName() 
    {
        return Constants::TABLE_AUDIT_TRAILS;
    }

    private array $actionIcons = [
        Constants::AUDIT_EVENT_CREATE => 'action-create',
        Constants::AUDIT_EVENT_UPDATE => 'action-update',
        Constants::AUDIT_EVENT_DELETE => 'action-delete',
    ];

    private array $customEmployeeMap = [
        Employee::f_Rank
    ];

    public function getBasic()
    {
        $dataset  = $this->buildQueryBasic()->get();
        $fieldMap = new AuditableTypesMapping;
        
        $employeeRankMappingSource = [
            Faculty::getFriendlyName() => FacultyConstants::getRanks(),
            Staff::getFriendlyName()   => StaffConstants::getRanks()
        ];

        foreach ($dataset as $d)
        {
            $model = $d->affected;

            // Change the auditable type to human-readable names
            if (method_exists($model, 'getFriendlyName'))
                $d->affected = $model::getFriendlyName();
            else
                $d->affected = 'Unknown';

            $affected = strtolower($d->affected);

            if ($affected == strtolower(Employee::STR_COLLECTIVE_ROLE_FACULTY))
                $affected = strtolower(Employee::STR_ROLE_TEACHER);

            // Sentence connector
            $connector = (Extensions::getCTypeAlpha($affected) == Constants::CTYPE_VOWEL)
                ? 'an'
                : 'a';

            // Describe the actions
            switch ($d->action)
            {
                case Constants::AUDIT_EVENT_CREATE:
                    $d->description = "Added a new $affected.";
                    break;

                case Constants::AUDIT_EVENT_UPDATE:
                    
                    if (!empty($d->old_values) && !empty($d->new_values))
                    {
                        $oldValues = json_decode($d->old_values, true);
                        $newValues = json_decode($d->new_values, true);
                        $changed   = array_diff_assoc($oldValues, $newValues);

                        $description = [];
                        
                        foreach ($changed as $fieldName => $fieldValue)
                        {
                            $field = $fieldMap->mapField($model, $fieldName);
                            $value = '';
   
                            // Only for employees, because it uses a separate rank
                            // foreach employee type
                            if ($fieldName == Employee::f_Rank)
                            {
                                $value = $fieldMap->mapValue(
                                    $fieldName, 
                                    $fieldValue, 
                                    $employeeRankMappingSource[$d->affected], 
                                    true
                                );
                            }

                            // For others
                            else
                            {
                                $value = $fieldMap->mapValue($fieldName, $fieldValue);
                            }
                            
                            $description[] = "$field to $value";
                        }

                        $d->description = 'Changed ' . implode(', ', $description);
                    }
                    else
                    {
                        $d->description = "Modified $connector $affected record";
                    }
                    break;

                case Constants::AUDIT_EVENT_DELETE:
                    $d->description = "Removed $connector $affected.";
                    break;
            }
        }

        return $dataset;
    }

    private function buildQueryBasic() : Builder
    {
        $fields = Extensions::prefixArray('a.', [
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
}