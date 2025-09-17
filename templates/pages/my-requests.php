<?php 

$user_id = get_current_user_id();
$profile = PFL_Database_Utils::get_user_profile($user_id);

if (!$profile || $profile->approval_status !== 'approved') {
    return;
}

//var_dump($profile);
if (!$profile) {
    echo "<p>No profile found.</p>";
    return;
}

// Fetch all flight requests
$all_requests = PFL_Database_Utils::get_all_flight_requests();


//var_dump($all_requests);

// Filter by same airline_code
$requests = array_filter($all_requests, function($req) use ($profile) {

    return $req->user_id === $profile->user_id;
});



?>

<div class="max-w-2xl mx-auto space-y-4 p-10">
<?php 

if(count($requests) === 0){
    echo '<div class="bg-blue-50 border border-blue-200 rounded-md p-4"><div class="flex ju"><div class="flex-shrink-0"></div><div class="ml-3"><p class="text-sm text-black-800">You have no active request right now. Create a new request.</p></div></div></div>';

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

            
        </div>

        <!-- Inline Expander Form (Hidden by default) -->
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

<!-- Modal -->
<dialog id="deleteDialog" class="rounded-lg shadow-xl w-full max-w-md p-0">
  <div class="bg-white rounded-lg overflow-hidden shadow-xl">
    <!-- Content -->
    <div class="px-6 pt-6 pb-4">
      <div class="sm:flex sm:items-start">
        <!-- Icon -->
        <div class="mx-auto flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="h-6 w-6 text-red-600">
            <path d="M12 9v3.75M3 16.126c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L3 16.126ZM12 15.75h.007v.008H12v-.008Z" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>

        <!-- Text -->
        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
          <h2 class="font-semibold text-gray-900">
            Delete Request
          </h2>
          <div class="mt-2">
            <p class="text-gray-500">
              Are you sure you want to delete this request? This action cannot be undone.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
      <button 
        id="confirmDelete"
        class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-red-500">
        Delete
      </button>
      <button 
        onclick="document.getElementById('deleteDialog').close()" 
        class="bg-white border border-gray-300 px-4 py-2 rounded-md text-sm font-semibold text-gray-900 hover:bg-gray-50">
        Cancel
      </button>
    </div>
  </div>
</dialog>
<!-- Backdrop styling -->
<style>
  dialog::backdrop {
    background-color: rgba(0, 0, 0, 0.5); /* dark semi-transparent backdrop */
  }
</style>
