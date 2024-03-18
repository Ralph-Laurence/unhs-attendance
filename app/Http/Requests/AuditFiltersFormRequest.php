<?php

namespace App\Http\Requests;

use App\Models\Constants\AuditableTypesProvider;
use Illuminate\Foundation\Http\FormRequest;

class AuditFiltersFormRequest extends FormRequest
{
    private $auditableTypesProvider;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // $this->auditableTypesProvider = new AuditableTypesProvider;
        // $types = array_keys( $this->auditableTypesProvider->getAuditableTypes() );

        // return [
        //     'timeFrom' : 05:51 pm
        //     'timeTo': 05:51 pm
        //     'date': March 18, 2024
        //     'user': 
        //     'action'    => 'in:' . implode(',', $types),
        //     'affected': f
        //     'searchTerm': '
        // ];
    }
}
