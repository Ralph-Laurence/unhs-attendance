<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Text\Messages;
use App\Http\Utils\Extensions;
use App\Http\Utils\RouteNames;
use App\Models\AuditTrails;
use Hashids\Hashids;
use Illuminate\Http\Request;

class AuditTrailsController extends Controller
{
    protected $hashids;

    public function __construct() 
    {
        $this->hashids = new Hashids(AuditTrails::HASH_SALT, AuditTrails::MIN_HASH_LENGTH);
    }

    public function index()
    {
        $routes = [
            'getAll'    => route(RouteNames::AuditTrails['all']),
            'viewAudit' => route(RouteNames::AuditTrails['show'])
        ];

        return view('backoffice.audits.index')
               ->with('routes', $routes);
    }

    public function getAll()
    {
        $dataset = new AuditTrails;
        
        return json_encode([
            'data' => $dataset->getBasic()
        ]);
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
