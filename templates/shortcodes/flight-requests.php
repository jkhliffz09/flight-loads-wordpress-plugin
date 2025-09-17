<?php
$current_user = wp_get_current_user();
$user_profile = PFL_Database_Utils::get_user_profile($current_user->ID);

if (!$user_profile || $user_profile->approval_status !== 'approved') {
    echo '<div class="bg-red-50 border border-red-200 rounded-md p-4"><div class="flex"><div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></div><div class="ml-3"><p class="text-sm text-red-800">Your account must be approved to access flight requests.</p></div></div></div>';
    return;
}
?>

<div class="max-w-6xl mx-auto p-6">
    <!-- Updated tabs with Tailwind styling -->
    <div class="bg-white rounded-lg shadow-lg">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button class="pfl-tab-button border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600" data-tab="request">
                    Make Request
                </button>
                <button class="pfl-tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="browse">
                    My Requests
                </button>
                <button class="pfl-tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="loads">
                    Browse Requests
                </button>
            </nav>
        </div>
        
        <!-- Request Tab -->
        <div class="pfl-tab-content p-6" data-tab="request">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Request Flight Loads</h3>
            
            <form id="pfl-flight-request-form" method="post" class="space-y-6">
                <?php wp_nonce_field('pfl_flight_request_nonce', 'pfl_nonce'); ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="relative">
                        <label for="request_airline" class="block text-sm font-medium text-gray-700 mb-1">Airline Name *</label>
                        <input type="text" id="request_airline" name="request_airline" placeholder="Search airline..." required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="hidden" id="request_airline_code" name="request_airline_code">
                        <div id="request_airline_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
                    </div>
                    
                    <div>
                        <label for="flight_number" class="block text-sm font-medium text-gray-700 mb-1">Flight Number *</label>
                        <input type="text" id="flight_number" name="flight_number" maxlength="10" placeholder="e.g., 1234" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="relative">
                        <label for="from_airport" class="block text-sm font-medium text-gray-700 mb-1">From *</label>
                        <input type="text" id="from_airport" name="from_airport" placeholder="Search airport..." required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="hidden" id="from_airport_id" name="from_airport_id">
                        <div id="from_airport_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
                    </div>
                    
                    <div class="relative">
                        <label for="to_airport" class="block text-sm font-medium text-gray-700 mb-1">To *</label>
                        <input type="text" id="to_airport" name="to_airport" placeholder="Search airport..." required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="hidden" id="to_airport_id" name="to_airport_id">
                        <div id="to_airport_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="travel_date" class="block text-sm font-medium text-gray-700 mb-1">Date of Travel *</label>
                        <input type="date" id="travel_date" name="travel_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="flex items-center">
                        <label class="flex items-center">
                            <input type="checkbox" id="is_return" name="is_return" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" style="width:15%;">
                            <span class="ml-2 text-sm text-gray-700">Return Flight</span>
                        </label>
                    </div>
                </div>
                
                <!-- Updated return fields styling -->
                <div id="return_fields" class="space-y-4 p-4 bg-blue-50 rounded-lg border border-gray-200 hidden">
                    <h4 class="text-lg font-medium text-gray-900">Return Flight Details</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <label for="return_airline" class="block text-sm font-medium text-gray-700 mb-1">Return Airline *</label>
                            <input type="text" id="return_airline" name="return_airline" placeholder="Search airline..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <input type="hidden" id="return_airline_code" name="return_airline_code">
                            <div id="return_airline_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
                        </div>
                        
                        <div>
                            <label for="return_flight_number" class="block text-sm font-medium text-gray-700 mb-1">Return Flight Number *</label>
                            <input type="text" id="return_flight_number" name="return_flight_number" maxlength="10"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label for="return_travel_date" class="block text-sm font-medium text-gray-700 mb-1">Return Date *</label>
                        <input type="date" id="return_travel_date" name="return_travel_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            
                
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" maxlength="300" placeholder="Additional information..." rows="4"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <p class="mt-1 text-sm text-gray-500"><span class="pfl-char-count">0</span>/300 characters</p>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium text-lg">
                    Submit Request
                </button>
            </form>
        </div>
        
        <!-- Browse Tab -->
        <div class="pfl-tab-content p-6 hidden" data-tab="browse">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">My Requests</h3>
            
            <!-- Added filter options for better request browsing -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                <div class="flex flex-wrap gap-4 items-center">
                    <div>                        
                        <select id="filter_status" class="px-5 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Requests</option>
                            <option value="pending">Pending</option>
                            <option value="answered">Answered</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button id="refresh_requests" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="pfl-requests-list" class="space-y-6">
                <!-- Enhanced request cards with like/comment functionality will be loaded via AJAX -->
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-gray-500">Loading requests...</p>
                </div>
            </div>
        </div>

        <!-- Loads Tab -->
        <div class="pfl-tab-content p-6 hidden" data-tab="loads">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Browse Requests</h3>
            
            <!-- Added filter options for better request browsing -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                <div class="flex flex-wrap gap-4 items-center">
                    <div>                        
                        <select id="filter_status-loads" class="px-5 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Requests</option>
                            <option value="pending">Pending</option>
                            <option value="answered">Answered</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button id="refresh_requests-loads" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="pfl-requests-list-loads" class="space-y-6">
                <!-- Enhanced request cards with like/comment functionality will be loaded via AJAX -->
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2 text-gray-500">Loading requests...</p>
                </div>
            </div>
        </div>

        <div class="z-9999999999"></div>
        
    </div>
</div>
