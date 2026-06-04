<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserStatModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function authenticate()
    {
        $session = session();
        $userModel = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Fetch user from the database
        $user = $userModel->where('username', $username)->first();

        if ($user && password_verify($password, $user['password'])) {
            
            // Check if the user is approved by the admin
            if ($user['status'] !== 'approved') {
                return redirect()->back()->with('error', 'Your account is pending approval from an administrator.');
            }

            // Credentials are valid and approved! Set up the session array
            $session->set([
                'id'             => $user['id'],
                'username'       => $user['username'],
                'player_name'    => $user['player_name'],
                'email'          => $user['email'],
                'alliance_level' => $user['alliance_level'],
                'role'           => $user['role'], // 'admin', 'editor', 'user'
                'is_logged_in'   => true,
            ]);

            return redirect()->to('/dashboard');
        }

        // Authentication failed
        return redirect()->back()->with('error', 'Invalid username or password.');
    }

    public function register()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }
        
        return view('auth/register');
    }

    public function store()
    {
        $userModel = new UserModel();
        $userStatModel = new UserStatModel();

        // Get form inputs
        $data = [
            'username'       => $this->request->getPost('username'),
            'player_name'    => $this->request->getPost('player_name'),
            'email'          => $this->request->getPost('email'),
            'alliance_level' => $this->request->getPost('alliance_level'),
            'password'       => $this->request->getPost('password'),
            'status'         => 'pending', // Default status until admin approves
            'role'           => 'user'     // Default role
        ];

        // Hash the password if provided
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Attempt to save the user
        $userId = $userModel->insert($data);
        if ($userId) {
            
            // Initialize empty squad powers for this new user
            $userStatModel->insert([
                'user_id'       => $userId,
                'power_tank'    => 0,
                'power_air'     => 0,
                'power_missile' => 0,
            ]);
            
            $displayName = !empty($data['player_name']) ? $data['player_name'] : $data['username'];
            
            // 1. Send Email to the new user
            try {
                $email = \Config\Services::email();
                $email->setTo($data['email']);
                $email->setSubject('Welcome to MDK Alliance! (Pending Approval)');
                
                $message = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                    <h2 style='color: #d97706;'>Welcome to MDK Alliance, {$displayName}!</h2>
                    <p>Your account has been successfully registered and is currently <strong>pending admin approval</strong>.</p>
                    <p>Once an administrator reviews and approves your account, you will be able to log in and access the alliance tools, tactical planners, and leaderboards.</p>
                    <br>
                    <p>Best regards,<br><strong>The MDK Command Team</strong></p>
                </body>
                </html>
                ";
                
                $email->setMessage($message);
                $email->send();
            } catch (\Exception $e) {
                log_message('error', 'Exception caught while trying to send welcome email: ' . $e->getMessage());
            }

            // 2. Send Notification Email to a specific admin address (rather than all super_admins)
            try {
                // To keep it simple and direct for the Super Admin
                $adminEmailAddress = 'acemglw@gmail.com';
                
                $adminEmail = \Config\Services::email();
                $adminEmail->setTo($adminEmailAddress);
                $adminEmail->setSubject('New User Registration - Action Required');
                
                $adminMessage = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                    <h2 style='color: #0284c7;'>New Member Application</h2>
                    <p>A new user has registered and is awaiting approval:</p>
                    <ul>
                        <li><strong>Username:</strong> {$data['username']}</li>
                        <li><strong>Player Name:</strong> {$displayName}</li>
                        <li><strong>Email:</strong> {$data['email']}</li>
                        <li><strong>Alliance Level:</strong> {$data['alliance_level']}</li>
                    </ul>
                    <p>Please log in to the admin panel to review and approve/reject their account.</p>
                    <br>
                    <p><a href='" . base_url('admin/approvals') . "' style='background: #0ea5e9; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Review Application</a></p>
                </body>
                </html>
                ";
                
                $adminEmail->setMessage($adminMessage);

                if (!$adminEmail->send()) {
                    log_message('error', 'Failed to send admin notification email. Error: ' . $adminEmail->printDebugger(['headers']));
                }
            } catch (\Exception $e) {
                log_message('error', 'Exception caught while trying to notify admin: ' . $e->getMessage());
            }
            
            return redirect()->to('/login')->with('success', 'Registration successful! Your account is now pending admin approval.');
        } else {
            // Validation failed
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }
    }

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        $userModel = new UserModel();
        $emailInput = $this->request->getPost('email');

        $user = $userModel->where('email', $emailInput)->first();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            // Set token to expire in 1 hour
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $userModel->update($user['id'], [
                'reset_token' => $token,
                'reset_expires_at' => $expiresAt
            ]);

            try {
                $email = \Config\Services::email();
                $email->setTo($user['email']);
                $email->setSubject('Password Reset Request - MDK Alliance');

                $resetLink = base_url('reset-password/' . $token);
                $displayName = !empty($user['player_name']) ? $user['player_name'] : $user['username'];

                $message = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                    <h2 style='color: #d97706;'>Password Reset</h2>
                    <p>Hello {$displayName},</p>
                    <p>We received a request to reset your password. If you made this request, please click the link below to set a new password:</p>
                    <p><a href='{$resetLink}' style='background: #0ea5e9; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Reset Password</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you did not request a password reset, please ignore this email.</p>
                    <br>
                    <p>Best regards,<br><strong>The MDK Command Team</strong></p>
                </body>
                </html>
                ";

                $email->setMessage($message);
                $email->send();
            } catch (\Exception $e) {
                log_message('error', 'Exception caught while trying to send password reset email: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to send the password reset email. Please try again later.');
            }
        }

        // Always show the same message whether the email exists or not for security reasons.
        return redirect()->back()->with('success', 'If an account with that email exists, a password reset link has been sent.');
    }

    public function resetPassword($token)
    {
        $userModel = new UserModel();
        $user = $userModel->where('reset_token', $token)
                          ->where('reset_expires_at >', date('Y-m-d H:i:s'))
                          ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Invalid or expired password reset link.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    public function updatePassword()
    {
        $userModel = new UserModel();
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');

        if ($password !== $passwordConfirm) {
            return redirect()->back()->with('error', 'Passwords do not match.');
        }

        $user = $userModel->where('reset_token', $token)
                          ->where('reset_expires_at >', date('Y-m-d H:i:s'))
                          ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Invalid or expired password reset link.');
        }

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters long.');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $userModel->update($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_expires_at' => null
        ]);

        return redirect()->to('/login')->with('success', 'Your password has been successfully reset. You can now log in.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    // Quick debug method to manually test SMTP!
    public function testEmail()
    {
        $email = \Config\Services::email();
        $email->setTo('acemglw@gmail.com'); // Test specific email
        $email->setSubject('MDK Alliance - SMTP Test');

        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
            <h2 style='color: #10b981;'>SMTP is working!</h2>
            <p>If you are reading this, your CodeIgniter 4 configuration for SMTP.PrivateName.net is perfectly set up and successfully delivered this test email.</p>
        </body>
        </html>
        ";

        $email->setMessage($message);

        if ($email->send()) {
            return "Test email sent successfully! Please check your inbox (and spam folder) at acemglw@gmail.com.";
        } else {
            $data = $email->printDebugger(['headers']);
            return "Failed to send email.<br><br><pre>" . print_r($data, true) . "</pre>";
        }
    }
}