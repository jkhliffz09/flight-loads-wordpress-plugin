<?php
$current_user = wp_get_current_user();
$user_profile = PFL_Database_Utils::get_user_profile($current_user->ID);
?>

<div class="max-w-4xl mx-auto p-6">
    <!-- Updated header with Tailwind classes -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">My Account</h2>
        
        <?php if ($user_profile && $user_profile->approval_status === 'pending'): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-800">Your application is pending approval. You will receive an email once approved.</p>
                    </div>
                </div>
            </div>
        <?php elseif ($user_profile && $user_profile->approval_status === 'denied'): ?>
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800">Your application was denied. Please contact support for more information.</p>
                    </div>
                </div>
            </div>
        <?php elseif ($user_profile && $user_profile->approval_status === 'approved'): ?>
            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800">Your account is approved and active!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Updated profile section with Tailwind grid layout -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">Profile Information</h3>
            
            <div class="space-y-4">
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Full Name</span>
                    <span class="text-sm text-gray-900"><?php echo esc_html($current_user->display_name); ?></span>
                </div>
                
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Username</span>
                    <span class="text-sm text-gray-900"><?php echo esc_html($current_user->user_login); ?></span>
                </div>
                
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Email</span>
                    <span class="text-sm text-gray-900"><?php echo esc_html($current_user->user_email); ?></span>
                </div>
                
                <?php if ($user_profile): ?>
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Airline</span>
                    <span class="text-sm text-gray-900"><?php echo esc_html($user_profile->airline_name ?: 'N/A'); ?></span>
                </div>
                
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Status</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $user_profile->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                        <?php echo esc_html(ucfirst($user_profile->status)); ?>
                    </span>
                </div>
                
                
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Phone Number</span>
                    <span class="text-sm text-gray-900"><?php echo esc_html($user_profile->phone_number); ?></span>
                </div>
                
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Retirement Date</span>
                    <span class="text-sm text-gray-900"><?php echo esc_html(date('F j, Y', strtotime($user_profile->employment_retirement_date))); ?></span>
                </div>
                
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Ex-Airline Job</span>
                    <span class="text-sm text-gray-900"><?php echo esc_html($user_profile->airline_job); ?></span>
                </div>
                
                <div class="flex justify-between py-3 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Years Worked</span>
                    <span class="text-sm text-gray-900"><?php echo esc_html($user_profile->years_worked); ?> years</span>
                </div>
                
                
                <div class="flex justify-between py-3">
                    <span class="text-sm font-medium text-gray-500">Member Since</span>
                    <span class="text-sm text-gray-900"><?php echo esc_html(date('F j, Y', strtotime($current_user->user_registered))); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Updated password section with Tailwind styling -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-6">Change Password</h3>
            
            <form id="pfl-password-form" method="post" class="space-y-4">
                <?php wp_nonce_field('pfl_password_nonce', 'pfl_nonce'); ?>
                
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" id="new_password" name="new_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="confirm_new_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <button type="submit" class="w-full bg-blue-600 mb-3 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium">
                    Update Password
                </button>

                <button type="button" onclick="window.location.href='https://www.staging17.passrider.com/flight-loads-request/'"  class="w-full bg-green-600 text-white py-2 mt-3 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 font-medium">
                    Go To Flight Load Request Page
                </button>
            </form>
        </div>
    </div>
</div>
