<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Flight Requests</h1>
    
    <?php if (!empty($flight_requests)): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Details</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Requester</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Travel Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Created</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($flight_requests as $request): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900"><?php echo esc_html($request->airline_code . $request->flight_number); ?></div>
                        <div class="text-xs text-gray-500"><?php echo esc_html($request->from_airport_id . ' → ' . $request->to_airport_id); ?></div>
                        <?php if ($request->is_return): ?>
                            <div class="text-xs text-blue-600 font-medium mt-1">Return flight requested</div>
                        <?php endif; ?>
                        <span class="inline-flex mt-2 items-center px-2.5 py-0.5 rounded-full text-xs font-medium block md:hidden 
                            <?php echo $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                     ($request->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                            <?php echo esc_html(ucfirst($request->status)); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 hidden sm:table-cell">
                        <div class="font-medium text-gray-900"><?php echo esc_html($request->requester_name); ?></div>
                        <div class="text-sm text-gray-500"><?php echo esc_html($request->user_email); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell"><?php echo esc_html(date('M j, Y', strtotime($request->travel_date))); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            <?php echo $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                     ($request->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                            <?php echo esc_html(ucfirst($request->status)); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo esc_html(date('M j, Y g:i A', strtotime($request->created_at))); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <!-- Modal toggle -->
                        <button data-modal-target="requestDetailsModal" data-modal-toggle="requestDetailsModal" class="ml-2 text-sm text-blue-600 hover:text-blue-800 pfl-view-request" data-request-id="<?php echo esc_attr($request->id); ?>" type="button">
                          View
                        </button>
                        <button type="button" class="text-red-600 hover:text-red-900 pfl-delete-request" 
                                data-request-id="<?php echo esc_attr($request->id); ?>">
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div id="pagination" class="flex justify-center mt-4 mb-4 space-x-1"></div>
    </div>
    <?php else: ?>
    <div class="bg-gray-50 rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <p class="text-gray-500 text-lg">No flight requests found.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Main modal -->
<div id="requestDetailsModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-10 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Request Details
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="requestDetailsModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-6 bg-white rounded-lg shadow border border-gray-200 space-y-6" id="requestDetailsContent">
                <p class="text-gray-500">Loading...</p>
            </div>
            <!-- Modal footer -->
            <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button data-modal-hide="requestDetailsModal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-red rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Close</button>
            </div>
        </div>
    </div>
</div>
