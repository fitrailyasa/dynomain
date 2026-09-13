<?php

namespace App\Http\Controllers\Admin;

use App\Models\Domain;
use App\Models\Subdomain;
use App\Models\Server;
use App\Models\User;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalDomains = Domain::count();
        $totalSubdomains = Subdomain::count();
        $totalServers = Server::count();
        $totalUsers = User::count();

        return view('dashboard', compact('totalDomains', 'totalSubdomains', 'totalServers', 'totalUsers'));
    }
}
