<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\Http\Utils\RouteNames;
use App\Models\AuditTrails;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditTrailsController extends Controller
{
    public function index()
    {
        $routes = [
            'getAll' => route(RouteNames::AuditTrails['all'])
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
}
