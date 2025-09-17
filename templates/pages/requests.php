<?php 

$current_user = wp_get_current_user();
$user_profile = PFL_Database_Utils::get_user_profile($current_user->ID);

// Only block non-admins who are not approved
if ((!$user_profile || $user_profile->approval_status !== 'approved') && !current_user_can('manage_options')) {
    return;
}

$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$current_user_id = get_current_user_id();

if ($edit_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'pfl_flight_requests';

    // Make sure this request belongs to the logged-in user
    $request = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d AND user_id = %d", $edit_id, $current_user_id)
    );

    if (!$request) {
        echo '<div class="bg-red-50 border border-red-200 rounded-md p-4"><div class="flex justify-center items-center"><div class="flex-shrink-0"></div><div class="ml-3"><p class="text-black-800 text-center">You do not have permission to edit this request.</p></div></div></div>';
        return;
    }

    // If we reach here, $request belongs to the current user
    $flight_number   = esc_html($request->flight_number);
    $airline_code    = esc_html($request->airline_code);
    $from_airport_id = esc_html($request->from_airport_id);
    $to_airport_id   = esc_html($request->to_airport_id);
    $travel_date     = esc_html($request->travel_date);
    $notes     		 = esc_html($request->notes);
    $aircraft 		 = esc_html($request->aircraft);
}


?>

<div class="max-w-6xl mx-auto p-6">
    <!-- Updated tabs with Tailwind styling -->
    <div class="bg-white rounded-lg shadow-lg">        
        <!-- Request Tab -->
        <div class="pfl-tab-content p-6" data-tab="request">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Request Flight Loads</h3>
            <div class="pfl-form-container hidden"></div>
            <form id="pfl-flight-request-form" method="post" class="space-y-6">
                <?php wp_nonce_field('pfl_flight_request_nonce', 'pfl_nonce'); ?>

                <div id="search_fields" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-start <?= $edit_id ? 'hidden': ''; ?>">
                    <div class="relative pfl-form-group">
                        <label for="from_airport_search" class="block text-sm font-medium text-gray-700 mb-1">From *</label>
                        <input type="text" id="from_airport_search" name="from_airport_search" placeholder="Search airport..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="hidden" id="from_airport_code_search" name="from_airport_code">
                        <div id="from_airport_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
                    </div>
                    
                    <div class="relative pfl-form-group">
                        <label for="to_airport_search" class="block text-sm font-medium text-gray-700 mb-1">To *</label>
                        <input type="text" id="to_airport_search" name="to_airport_search" placeholder="Search airport..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="hidden" id="to_airport_id_search" name="to_airport_id_search">
                        <div id="to_airport_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
                    </div>

                    <div class="pfl-form-group">
                        <label for="travel_date_search" class="block text-sm font-medium text-gray-700 mb-1">Date of Travel *</label>
                        <input type="date" id="travel_date_search" name="travel_date_search" 	
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                    	<button id="search_flights" type="button" class="bg-blue-600 text-white px-4 py-2 !rounded-md text-sm mt-4 hover:bg-blue-700">
		                    Search Flight
		                </button>
                    </div>
                </div>

                <div class="relative mt-6">
                	<button id="scrollLeft" class="absolute left-0 top-1/2 -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full shadow-xl hover:bg-gray-700 z-10 hidden">←</button>
                	<div id="flightScroller" class="overflow-x-auto scrollbar-hide">
                		<div id="flight-results" class="flex space-x-4 px-8"></div>
                	</div>
                	<button id="scrollRight" class="absolute right-0 top-1/2 -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full shadow-xl hover:bg-gray-700 z-10 hidden">→</button>
                </div>
                
                <div id="request-fields" class="<?= !$edit_id ? 'hidden' : ''; ?>">
	                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
	                    <div class="relative pfl-form-group">
	                        <label for="request_airline" class="block text-sm font-medium text-gray-700 mb-1">Airline Name *</label>
	                        <input type="text" id="request_airline_name" name="request_airline_name" placeholder="Search airline..."
	                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?= $edit_id ? $airline_code : ''; ?>">
	                        <input type="hidden" id="request_airline_code" name="request_airline_code" value="<?= $edit_id ? $airline_code : ''; ?>">
                    		<div id="request_airline_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
	                    </div>
	                    
	                    <div class="pfl-form-group">
	                        <label for="flight_number" class="block text-sm font-medium text-gray-700 mb-1">Flight Number *</label>
	                        <input type="text" id="flight_number" name="flight_number" maxlength="10" placeholder="e.g., 1234"
	                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?= $edit_id ? $flight_number : ''; ?>">
	                    </div>
	                </div>

	                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
	                    <div class="relative pfl-form-group">
	                        <label for="from_airport" class="block text-sm font-medium text-gray-700 mb-1">From *</label>
	                        <input type="text" id="from_airport" name="from_airport" placeholder="Search airport..."
	                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?= $edit_id ? $from_airport_id : ''; ?>">
	                        <input type="hidden" id="from_airport_id" name="from_airport_id" value="<?= $edit_id ? $from_airport_id : ''; ?>">
	                        <div id="from_airport_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
	                    </div>
	                    
	                    <div class="relative pfl-form-group">
	                        <label for="to_airport" class="block text-sm font-medium text-gray-700 mb-1">To *</label>
	                        <input type="text" id="to_airport" name="to_airport" placeholder="Search airport..." 
	                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?= $edit_id ? $to_airport_id : ''; ?>">
	                        <input type="hidden" id="to_airport_id" name="to_airport_id" value="<?= $edit_id ? $to_airport_id : ''; ?>">
	                        <div id="to_airport_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
	                    </div>
	                </div>
	                
	                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
	                    <div class="pfl-form-group">
	                        <label for="travel_date" class="block text-sm font-medium text-gray-700 mb-1">Date of Travel *</label>
	                        <input type="date" id="travel_date" name="travel_date" 
	                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="<?= $edit_id ? $travel_date : ''; ?>">
	                    </div>
	                    
	                </div>            
	                
	                <div class="pfl-form-group">
	                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
	                    <textarea id="notes" name="notes" maxlength="300" placeholder="Additional information..." rows="4"
	                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= $edit_id ? $notes : ''; ?></textarea>
	                    <p class="mt-1 text-sm text-gray-500"><span class="pfl-char-count">0</span>/300 characters</p>
	                </div>
	                <input type="hidden" name="aircraft" id="aircraft" value="<?= $edit_id ? $aircraft : ''; ?>">
	                <input type="hidden" name="request_id" id="request_id" value="<?= $edit_id ? $edit_id : ''; ?>">

	                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-md !hover:bg-blue-700 !focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium text-lg mt-5">
	                    <?= $edit_id ? 'Update Request' : 'Submit Request'; ?>
	                </button>
	                <button id="reset-btn" type="button" class="w-full p-2 focus:no-outline no-outline"><span class="underline">Reset Fields</span></button>
	            </div>
                
            </form>
        </div>
        
    </div>
</div>
