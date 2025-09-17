<?php //echo get_transient('pfl_verification_' . md5('active@khliffz.com'));?>
<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Manage Users</h1>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center gap-4">
                <div class="bulk-actions">
                    <select id="bulk-action-selector-top" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="-1">Bulk Actions</option>
                        <option value="approve">Approve</option>
                        <option value="deny">Deny</option>
                    </select>
                    <button type="button" id="bulk-apply" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 text-sm font-medium">Apply</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Pending Approvals (<?php echo count($pending_users); ?>)</h2>
        <?php if (!empty($pending_users)): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="w-12 px-6 py-3">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Airline</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Registration Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($pending_users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" value="<?php echo esc_attr($user->user_id); ?>" class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900"><?php echo esc_html($user->display_name); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html($user->user_email); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell"><?php echo esc_html($user->airline_name ?: 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($user->status === 'retired'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 hidden sm:inline">
                                    Retired
                                </span>
                                
                                <!-- Modal toggle -->
                                <button data-modal-target="userDetailsModal" data-modal-toggle="userDetailsModal" class="ml-2 text-xs text-blue-600 hover:text-blue-800 pfl-view-retired" data-user-id="<?php echo esc_attr($user->user_id); ?>" type="button">
                                  View Details
                                </button>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hidden sm:inline">
                                    <?php echo esc_html(ucfirst($user->status)); ?>
                                </span>
                                <button data-modal-target="userDetailsModal" data-modal-toggle="userDetailsModal" class="ml-2 text-xs text-blue-600 hover:text-blue-800 pfl-view-retired" data-user-id="<?php echo esc_attr($user->user_id); ?>" type="button">
                                  View Details
                                </button>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html(date('M j, Y', strtotime($user->user_registered))); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2 hidden sm:table-cell">
                            <button type="button" class="text-green-600 hover:text-green-900 pfl-approve-user" 
                                    data-user-id="<?php echo esc_attr($user->user_id); ?>">
                                Approve
                            </button>
                            <button type="button" class="text-red-600 hover:text-red-900 pfl-deny-user" 
                                    data-user-id="<?php echo esc_attr($user->user_id); ?>">
                                Deny
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <p class="text-gray-500">No pending user approvals.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Approved Users (<?php echo count($approved_users); ?>)</h2>
        <?php if (!empty($approved_users)): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Airline</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Registration Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Approved Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($approved_users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html($user->user_id); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900"><?php echo esc_html($user->display_name); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html($user->user_email); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell"><?php echo esc_html($user->airline_name ?: 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 hidden sm:inline">
                                Approved
                            </span>
                            <button data-modal-target="userDetailsModal" data-modal-toggle="userDetailsModal" class="ml-2 text-xs text-blue-600 hover:text-blue-800 pfl-view-retired" data-user-id="<?php echo esc_attr($user->user_id); ?>" type="button">
                                  View Details
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html(date('M j, Y', strtotime($user->updated_at))); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html(date('M j, Y', strtotime($user->user_registered))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <p class="text-gray-500">No approved users yet.</p>
        </div>
        <?php endif; ?>
    </div>


    <!-- DENIED USERS-->
    <div>
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Denied Users (<?php echo count($denied_users); ?>)</h2>
        <?php if (!empty($denied_users)): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Airline</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Registration Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Denied Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($denied_users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html($user->user_id); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900"><?php echo esc_html($user->display_name); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html($user->user_email); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell"><?php echo esc_html($user->airline_name ?: 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 hidden sm:inline">
                                Approved
                            </span>
                            <button data-modal-target="userDetailsModal" data-modal-toggle="userDetailsModal" class="ml-2 text-xs text-blue-600 hover:text-blue-800 pfl-view-retired" data-user-id="<?php echo esc_attr($user->user_id); ?>" type="button">
                                  View Details
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html(date('M j, Y', strtotime($user->updated_at))); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html(date('M j, Y', strtotime($user->user_registered))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <p class="text-gray-500">No denied users yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Retired User Details Modal -->
<div id="pfl-retired-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Retired User Details</h3>
            <button class="pfl-modal-close text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="pfl-retired-details" class="text-sm text-gray-600"></div>
    </div>
</div>

<!-- Main modal -->
<div id="userDetailsModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-10 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Flight Loads Applicant Details
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="userDetailsModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-6 bg-white rounded-lg shadow border border-gray-200 space-y-6" id="userDetailsContent">
                <p class="text-gray-500">Loading...</p>
            </div>
            <!-- Modal footer -->
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button id="modal-approve" data-modal-hide="userDetailsModal" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 pfl-approve-user hidden">Approve</button>
                <button id="modal-deny" data-modal-hide="userDetailsModal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-red rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 pfl-deny-user">Deny</button>
            </div>
        </div>
    </div>
</div>
