<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Constants;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\AuditTrails;
use App\Models\Constants\AuditableTypesProvider;
use App\Models\User;
use Hashids\Hashids;
use Illuminate\Http\Request;

class AuditTrailsController extends Controller
{
    protected $hashids;
    private $auditableTypesProvider;

    public function __construct() 
    {
        $this->hashids = new Hashids(AuditTrails::HASH_SALT, AuditTrails::MIN_HASH_LENGTH);
        $this->auditableTypesProvider = new AuditableTypesProvider;

        $this->auditableTypesProvider->registerTypes();
    }

    public function index()
    {
        $filters = [
            'actions' => [
                'Create' => Constants::AUDIT_EVENT_CREATE,
                'Update' => Constants::AUDIT_EVENT_UPDATE,
                'Delete' => Constants::AUDIT_EVENT_DELETE,
            ],
            'users'    => User::getUsersAssc(),
            'affected' => $this->auditableTypesProvider->getAuditableTypesOptions()
        ];

        $routes = [
            'getAll'        => route(RouteNames::AuditTrails['all']),
            'viewAudit'     => route(RouteNames::AuditTrails['show'])
        ];

        return view('backoffice.audits.index')
            ->with('routes',    $routes)
            ->with('filters',   $filters);
    }

    // public function filter(Request $request)
    // {
    //     $model = new AuditTrails;
        
    //     return $model->filterAudits($request);
    // }

    public function getAll(Request $request)
    {
        $dataset = new AuditTrails;
        
        return $dataset->getBasic($request);
    }

    public function show(Request $request)
    {
        $key = $request->input('rowKey');
        $id = $this->hashids->decode($key);

        if (empty($id))
            return Extensions::encodeFailMessage(Messages::READ_FAIL_INCOMPLETE);
        
        $dataset = new AuditTrails;
        
        return $dataset->viewAuditDetails($id[0]);
    }
}
