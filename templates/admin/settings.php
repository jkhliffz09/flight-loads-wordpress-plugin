<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Settings</h1>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6">
                <a href="#airlines" class="nav-tab border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
                    Airlines
                </a>
                <a href="#integrations" class="nav-tab border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Integrations
                </a>
            </nav>
        </div>
        
        <div id="airlines" class="tab-content p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Airline Management</h2>
            
            <form method="post" action="" class="mb-8">
                <?php wp_nonce_field('pfl_admin_nonce', 'pfl_nonce'); ?>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <label for="airline_name" class="block text-sm font-medium text-gray-700 mb-2">Airline Name</label>
                        <input type="text" name="airline_name" id="airline_name" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                    </div>
                    <div>
                        <label for="iata_code" class="block text-sm font-medium text-gray-700 mb-2">IATA Code</label>
                        <input type="text" name="iata_code" id="iata_code" maxlength="3" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                    </div>
                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
                        <input type="text" name="domain" id="domain" placeholder="example.com" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               required>
                    </div>
                </div>
                <input type="hidden" name="airline_id" id="airline_id">
                <div class="mt-6">
                    <button type="submit" name="submit_airline" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 font-medium">
                        Add Airline
                    </button>
                </div>
            </form>
            
            <h3 class="text-lg font-medium text-gray-900 mb-4">Existing Airlines</h3>
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between p-3">
                  <!-- Search -->
                  <input id="table-search" type="text" placeholder="Search airlines..."
                    class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">

                  <!-- Rows per page -->
                  <select id="rows-per-page" class="px-2 py-1 border border-gray-300 rounded-md text-sm">
                    <option value="5">5 rows</option>
                    <option value="10" selected>10 rows</option>
                    <option value="25">25 rows</option>
                    <option value="50">50 rows</option>
                  </select>
                </div>
                <table id="airlines-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Airline Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IATA Code</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($airlines as $airline): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo esc_html($airline->name); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo esc_html($airline->iata_code); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo esc_html($airline->domain); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                global $wpdb;
                                $user_count = $wpdb->get_var($wpdb->prepare(
                                    "SELECT COUNT(*) FROM {$wpdb->prefix}pfl_user_profiles WHERE airline_id = %d",
                                    $airline->id
                                ));
                                echo esc_html($user_count);
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button type="button" class="text-blue-600 hover:text-blue-900 pfl-edit-airline" 
                                        data-id="<?php echo esc_attr($airline->id); ?>"
                                        data-name="<?php echo esc_attr($airline->name); ?>"
                                        data-iata="<?php echo esc_attr($airline->iata_code); ?>"
                                        data-domain="<?php echo esc_attr($airline->domain); ?>">
                                    Edit
                                </button>
                                <button type="button" class="text-red-600 hover:text-red-900 pfl-delete-airline" 
                                        data-id="<?php echo esc_attr($airline->id); ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="pagination" class="flex justify-center mt-4 mb-4 space-x-1"></div>
            </div>
        </div>
        
        <div id="integrations" class="tab-content p-6" style="display: none;">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Integrations</h2>
            
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Wishlist Member</h3>
                        <p class="text-sm text-gray-600">Connect with Wishlist Member for advanced membership management.</p>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Not Connected
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-4">
                    <div>
                        <label for="wlm_api_url" class="block text-sm font-medium text-gray-700 mb-2">API URL</label>
                        <input type="url" name="wlm_api_url" id="wlm_api_url" placeholder="https://yoursite.com/api" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="wlm_api_key" class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                        <input type="text" name="wlm_api_key" id="wlm_api_key" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 font-medium">
                        Connect
                    </button>
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 font-medium">
                        Test Connection
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('border-blue-500 text-blue-600').addClass('border-transparent text-gray-500');
        $(this).removeClass('border-transparent text-gray-500').addClass('border-blue-500 text-blue-600');
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });
    
    // Edit airline
    $('.pfl-edit-airline').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var iata = $(this).data('iata');
        var domain = $(this).data('domain');
        
        $('input[name="airline_name"]').val(name);
        $('input[name="iata_code"]').val(iata);
        $('input[name="domain"]').val(domain);
        $('#airline_id').val(id);
        $('input[name="submit_airline"]').val('Update Airline');
    });
});
</script>
