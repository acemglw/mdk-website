<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PageAccessLogModel;

class PageAuth implements FilterInterface
{
    /**
     * This runs BEFORE the controller method executes.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Check if logged in
        if (!$session->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        // --- NEW: Log Page Access ---
        $this->logAccess($request);

        // 2. Check Role Requirements (if any arguments passed, e.g., ['admin'])
        if (!empty($arguments)) {
            $userRole = $session->get('role');
            
            // super_admin bypasses all role checks
            if ($userRole === 'super_admin') {
                return;
            }

            // Check if user has required role
            if (!in_array($userRole, $arguments)) {
                // If they are an 'admin' trying to access something that might require 'super_admin'
                // or if they are a 'user' trying to access 'admin' pages.
                return redirect()->to('/dashboard')->with('error', 'You do not have the required permissions for that section.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }

    private function logAccess(RequestInterface $request)
    {
        // Don't log AJAX requests if you don't want to clutter the logs
        if ($request->isAJAX()) {
            return;
        }

        // Determine if it's a GET request. We might only want to track page views (GET), not POST actions.
        if (strtolower($request->getMethod()) !== 'get') {
             return;
        }

        $session = session();
        $userId = $session->get('id');
        $url = (string) $request->getUri();
        
        // Exclude certain paths from being logged if they are too noisy, e.g. polling endpoints
        // if (strpos($url, 'some/noisy/path') !== false) return;

        $ipAddress = $request->getIPAddress();
        $userAgent = $request->getUserAgent()->getAgentString();

        $logModel = new PageAccessLogModel();
        
        try {
            $logModel->insert([
                'user_id'    => $userId,
                'url'        => $url,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ]);
        } catch (\Exception $e) {
            // Failsafe so logging failure doesn't break the app
            log_message('error', 'Failed to log page access: ' . $e->getMessage());
        }
    }
}
