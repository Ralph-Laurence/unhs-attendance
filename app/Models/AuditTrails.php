<?php

namespace App\Models;

use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function getBasic()
    {
        $fields = array_merge(Extensions::prefixArray('a.', [
            self::f_Event        . ' as action',
            self::f_Model_Type   . ' as target',
            self::f_Old_Values   . ' as old_vals',
            self::f_New_Values   . ' as new_vals',
        ]), [
            Extensions::date_format_bdY('a.created_at'),
            Extensions::time_format_hip('a.created_at'),
            DB::raw("CONCAT(e.firstname,' ',e.lastname) AS adminname")
            // Employee::getConcatNameDbRaw('e')
        ]);

        $dataset = DB::table(self::getTableName() . ' as a')
                 ->select($fields)
                 ->leftJoin('users as e', 'e.id', '=', 'a.'. self::f_User_FK)
                 ->orderBy('a.created_at', 'DESC')
                 ->get();
                
        foreach ($dataset as $d)
        {
            $model = $d->target;

            if (method_exists($model, 'getFriendlyName'))
                $d->target = $model::getFriendlyName();
            else
                $d->target = 'Unknown Target Name';
        }

        return $dataset;
    }
}
