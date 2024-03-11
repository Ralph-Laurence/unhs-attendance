<?php
// Not a Model, but a class that holds constant values
namespace App\Models\Constants;

/* 
In this setup, any class that extends EmployeeBase will need to 
provide an implementation for the getRanks() method, as it's declared 
in the IEmployeeRanks interface. The describeRank() method is a member 
of the abstract class and can be used directly or overridden in child classes
*/
interface IEmployeeRanks
{
    public static function getRanks(bool $flip = false) : array;
}

abstract class EmployeeBase implements IEmployeeRanks
{
    public static function describeRank(int $rank): string
    {
        $ranks = self::getRanks();

        if (!array_key_exists($rank, $ranks))
            return 'Unknown';

        return $ranks[$rank];
    }
}
