<?php

namespace App\Models;

use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Models\Constants\AuditableTypesProvider;
use Carbon\Carbon;
use Exception;
use Hashids\Hashids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
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

    private function inputExists($request, $input)
    {
        return $request->has($input) && $request->filled($input);
    }

    public function getBasic(Request $request)
    {
        $query = $this->buildQueryBasic();

        try
        {
            if ($this->inputExists($request, 'useFilter') && $request->input('useFilter') == 1)
            {
                if (
                    $this->inputExists($request, 'timeFrom') && 
                    $this->inputExists($request, 'timeTo')   && 
                    $this->inputExists($request, 'date')
                )
                {
                    if (
                        $this->inputExists($request, 'fullTime') && 
                        $request->input('fullTime') == Constants::CHECKBOX_ON
                    )
                    {
                        $date = Carbon::parse($request->input('date'));
                        
                        if (is_int($date) && $date == -1)
                            throw new \Carbon\Exceptions\InvalidFormatException;
    
                        $timeFrom = $date->startOfDay();
                        $timeTo   = $date->copy()->endOfDay();

                        $query->whereBetween('a.created_at', [$timeFrom, $timeTo]);
                    }
                    else
                    {
                        $timeFrom  = $request->input('timeFrom');
                        $timeTo    = $request->input('timeTo');
                        $rangeFrom = $this->parseDateTime($request->input('date'), $timeFrom);
                        $rangeTo   = $this->parseDateTime($request->input('date'), $timeTo);

                        if ((is_int($rangeFrom) && $rangeFrom == -1) ||
                            (is_int($rangeTo) && $rangeTo == -1)
                        )
                            throw new \Carbon\Exceptions\InvalidFormatException;

                        $query->whereBetween('a.created_at', [$rangeFrom, $rangeTo]);
                    }
                }
    
                if ($this->inputExists($request, 'user'))
                {
                    $userHash = new Hashids(User::HASH_SALT, User::MIN_HASH_LENGTH);
                    $userid  = $userHash->decode($request->input('user'));
    
                    if (empty($userid))
                    {
                        throw new Exception('invalid user filter');
                    }
    
                    $query->where(self::f_User_FK, '=', $userid);
                }
    
                if ($this->inputExists($request, 'action'))
                {
                    $action  = $request->input('action');
                    $actions = [ 
                        Constants::AUDIT_EVENT_CREATE, 
                        Constants::AUDIT_EVENT_UPDATE, 
                        Constants::AUDIT_EVENT_DELETE
                    ];
    
                    if (!in_array($action, $actions))
                    {
                        throw new Exception('invalid action filter');
                    }
    
                    $query->where(self::f_Event, '=', $action);
                }
    
                if ($this->inputExists($request, 'affected'))
                {
                    $affected = $request->input('affected');
    
                    $provider = new AuditableTypesProvider;
                    $models = $provider->getAuditableTypes();
    
                    if (!in_array($affected, array_keys($models)))
                    {
                        throw new Exception('invalid affected filter');
                    }
    
                    $query->where(self::f_Model_Type, '=', $models[$affected]);
                }

                if ($this->inputExists($request, 'searchTerm'))
                {
                    $searchTerm = $request->input('searchTerm');
                    
                    $query->whereRaw('CAST(old_values AS CHAR) LIKE ?',   ['%' . $searchTerm . '%'])
                          ->orWhereRaw('CAST(new_values AS CHAR) LIKE ?', ['%' . $searchTerm . '%']);
                }
            }

            $dataset  = $query->get();
            $hashids  = new Hashids(self::HASH_SALT, self::MIN_HASH_LENGTH);

            foreach ($dataset as $row) 
            {
                $this->beautifyBasicAuditResults($row, $hashids);
            }

            return json_encode([
                'code' => Constants::XHR_STAT_OK,
                'data' => $dataset
            ]);
        }
        catch (\Carbon\Exceptions\InvalidFormatException $cx)
        {
            return Extensions::encodeFailMessage('Invalid date and time filter combination.');
        }
        catch (Exception $ex)
        {
            error_log($ex->getMessage() . ' happend at -> ' . $ex->getLine());
            return Extensions::encodeFailMessage('The given filter combination contains invalid data.');
        }
    
        return $dataset;
    }

    // private function parseTime(string $timeString)
    // {
    //     try 
    //     {
    //         return Carbon::parse($timeString);
    //     }
    //     catch (Exception $ex)
    //     {
    //         return -1;
    //     }
    // }

    private function parseDate(string $dateString)
    {
        try 
        {
            return Carbon::parse($dateString);
        }
        catch (Exception $ex)
        {
            error_log($ex->getMessage() . ' happend at -> ' . $ex->getLine());
            return -1;
        }
    }


    private function parseDateTime($date, $time)
    {
        try 
        {
            return Carbon::createFromFormat('H:i a', $time)->setDateFrom(Carbon::parse($date)); //parse("$date $time");
        } 
        catch (Exception $e) 
        {
            error_log("Failed to parse date and time: " . $e->getMessage());
            return -1;
        }
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
