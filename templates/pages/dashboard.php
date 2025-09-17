<?php 

$user_id = get_current_user_id();
$profile = PFL_Database_Utils::get_user_profile($user_id);

// Only block non-admins who are not approved
if ((!$profile || $profile->approval_status !== 'approved') && !current_user_can('manage_options')) {
    return;
}

//var_dump($profile);
if (!$profile && !current_user_can('manage_options')) {
    echo "<p>No profile found.</p>";
    return;
}

/// Fetch all flight requests
$all_requests = PFL_Database_Utils::get_all_flight_requests();

if (current_user_can('manage_options')) {
    // Admin sees everything (no airline filter, only remove expired)
    $requests = $all_requests;
} else {
    // Regular users only see their airline’s requests
    $requests = array_filter($all_requests, function($req) use ($profile) {
        return (
            ($req->airline_code === $profile->airline_code && $req->status !== 'expired') ||
            ($req->return_airline_code === $profile->airline_code && $req->status !== 'expired')
        );
    });
} ?>

<div class="max-w-2xl mx-auto space-y-4 p-10">
<?php 

if(count($requests) === 0){
    echo '<div class="bg-blue-50 border border-blue-200 rounded-md p-4"><div class="flex ju"><div class="flex-shrink-0"></div><div class="ml-3"><p class="text-sm text-black-800">There are no active load requests for your airline right now. Please check back later or create a new request.</p></div></div></div>';

}

foreach ($requests as $request): 
    //var_dump($request);
    $likes = PFL_Database_Utils::get_request_like_count($request->id);
    $liked = PFL_Database_Utils::is_request_liked($user_id, $request->id);
    $comments = PFL_Database_Utils::get_request_comments($request->id);
    $loads = PFL_Database_Utils::get_loads($request->id);
    // Unix timestamp in UTC
    $utc_now = time();

    // Or, as MySQL datetime string in UTC
    $utc_mysql = gmdate('Y-m-d H:i:s');

    // Example: time ago using UTC
    $time_ago = human_time_diff( strtotime( $request->created_at ), $utc_now ) . ' ago';
    $status = $request->status;
    $fill = ($status === 'answered') 
        ? 'bg-green-100 text-green-800' 
        : (($status === 'expired') 
            ? 'bg-red-100 text-red-800' 
            : 'bg-yellow-100 text-yellow-800');

    $isOwner = ((int) $user_id === (int) $request->user_id);
    

?>
    <div class="bg-white shadow-xl rounded-2xl p-4 border">
        <!-- Header -->
        <?= pfl_render_request_card($request, $time_ago, $fill, $isOwner, $loads); ?>

        <!-- Actions -->
        <div class="flex justify-between mt-3 text-gray-600 border-t border-b py-2 text-sm">
            <!-- Like -->
            <button class="pfl-like-btn like-btn flex items-center space-x-1 hover:text-blue-600 focus:outline-none" data-request-id="<?= $request->id; ?>" data-request="<?= $request->id; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 <?= $liked ? 'fill-blue-600 text-blue-600' : 'text-gray-500'; ?>" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2 10c0-1.1.9-2 2-2h3l1-4a2 2 0 014 0v2h3a2 2 0 012 2l-1 5a4 4 0 01-4 4H6a4 4 0 01-4-4v-3z"/>
                </svg>
                <span class="pfl-like-count"><?= $likes; ?></span>
            </button>

            <!-- Comment -->
            <button class="comment-toggle flex items-center space-x-1 hover:text-blue-600 focus:outline-none" data-request="<?= $request->id; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.95 9.95 0 01-4-.832L3 20l1.832-4A7.963 7.963 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span><?php echo count($comments); ?> Comment</span>
            </button>

            <?php if($status === 'answered' || $status === 'expired' || $isOwner): ?>
            <!-- Flight Load -->
            <?php else: ?> 
                <button class="flightload-show flex items-center space-x-1 hover:text-blue-600 focus:outline-none" data-request="<?= $request->id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500 focus:outline-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 3l7.5 7.5-7.5 7.5M3 12h18"/>
                    </svg>
                    <span>Give Flight Load</span>
                </button>
            <?php endif; ?>
        </div>

        <?= pfl_flightload_form($request->id); ?>

        <!-- Comments -->
        <div id="comments-<?= $request->id; ?>" class="mt-3 hidden">
            <div class="pfl-comments-list" data-request-id="<?= $request->id; ?>" data-special="1">
                <?php foreach ($comments as $c): ?>
                    <div class="flex items-start space-x-2 mb-2">
                        <div class="w-8 h-8 rounded-full bg-gray-200"></div>
                        <div class="bg-gray-100 rounded-lg px-3 py-2">
                            <p class="text-sm"><span class="font-semibold"><?= esc_html($c->author_name); ?>:</span> <?= esc_html($c->comment); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Add new comment -->
            <div class="flex items-center space-x-2 mt-2">
                <div class="w-8 h-8 rounded-full bg-gray-200"></div>
                <div class="flex-1">
                    <textarea class="pfl-comment-input new-comment w-full border rounded-lg p-2 text-sm" data-request-id="<?= $request->id; ?>" rows="1" placeholder="Write a comment..."></textarea>
                </div>
                <button class="pfl-comment-submit submit-comment text-blue-600 font-semibold text-sm" data-request-id="<?= $request->id; ?>">Post</button>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
