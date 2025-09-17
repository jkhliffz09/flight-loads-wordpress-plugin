<!-- Updated admin dashboard to use Tailwind CSS classes -->
<div class="wrap">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Passrider Flight Loads Dashboard</h1>
    
    <div class="flex gap-5 mb-5">
        <div class="bg-white border border-wp-border rounded p-5 flex-1 text-center">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Users</h3>
            <p class="text-3xl font-bold text-wp-blue my-2"><?php echo esc_html($stats['total_users']); ?></p>
        </div>
        <div class="bg-white border border-wp-border rounded p-5 flex-1 text-center">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Pending Approvals</h3>
            <p class="text-3xl font-bold text-wp-blue my-2"><?php echo esc_html($stats['pending_users']); ?></p>
        </div>
        <div class="bg-white border border-wp-border rounded p-5 flex-1 text-center">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Active Requests</h3>
            <p class="text-3xl font-bold text-wp-blue my-2"><?php echo esc_html($stats['active_requests']); ?></p>
        </div>
    </div>
    
    <div class="bg-white border border-wp-border rounded p-5 mt-5">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="flex gap-3">
            <a href="<?php echo admin_url('admin.php?page=pfl-users'); ?>" class="px-4 py-2 bg-wp-blue text-white rounded hover:bg-wp-blue-dark transition-colors duration-200 no-underline">
                Manage Users
            </a>
            <a href="<?php echo admin_url('admin.php?page=pfl-requests'); ?>" class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded hover:bg-gray-200 transition-colors duration-200 no-underline">
                View Requests
            </a>
            <a href="<?php echo admin_url('admin.php?page=pfl-settings'); ?>" class="px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded hover:bg-gray-200 transition-colors duration-200 no-underline">
                Settings
            </a>
        </div>
    </div>
</div>
