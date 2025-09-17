<div id="mediavine-settings" data-blocklist-leaderboard="1" data-blocklist-sidebar-atf="1" data-blocklist-sidebar-btf="1" data-blocklist-content-desktop="1" data-blocklist-content-mobile="1" data-blocklist-adhesion-mobile="1" data-blocklist-adhesion-tablet="1" data-blocklist-adhesion-desktop="1" data-blocklist-recipe="1" data-blocklist-auto-insert-sticky="1" data-blocklist-in-image="1" data-blocklist-chicory="1" data-blocklist-zergnet="1" data-blocklist-interstitial-mobile="1" data-blocklist-interstitial-desktop="1" data-blocklist-universal-player-desktop="1" data-blocklist-universal-player-mobile="1" ></div>
<?php

$current_user = wp_get_current_user();
$user_profile = PFL_Database_Utils::get_user_profile($current_user->ID);

$current_path = trim( $_SERVER['REQUEST_URI'], '/' );

if (
    !$user_profile 
    || $user_profile->approval_status !== 'approved'
) {
    // Allow admins to bypass approval
    if (!current_user_can('manage_options')) {
        echo '<div class="bg-red-50 border border-red-200 rounded-md p-10">
                <div class="flex items-center justify-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-md text-red-800 m-0">
                            Your account must be approved to access flight requests.
                        </p>
                    </div>
                </div>
              </div>';
        return;
    }
}


?>

<div class="max-w-6xl mx-auto">
	<div class="bg-white rounded-lg shadow-lg">
	</div>
</div>

<!-- Include this script tag or install `@tailwindplus/elements` via npm: -->
<!-- <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script> -->
<nav class="relative bg-gray-800">
  <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
    <div class="relative flex h-16 items-center justify-between">
      <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
        <!-- Mobile menu button-->
        <button 
          id="menu-btn"
          type="button" 
          aria-expanded="false"
          aria-controls="mobile-menu"
          class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-white/5 hover:text-white outline-none focus:outline-none"
        >
          <span class="absolute -inset-0.5"></span>
          <span class="sr-only">Open main menu</span>

          <!-- Hamburger icon -->
          <svg id="icon-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
            aria-hidden="true" class="size-6 block">
            <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>

          <!-- X icon -->
          <svg id="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
            aria-hidden="true" class="size-6 hidden">
            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>
      <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
        <div class="flex shrink-0 items-center">
          <img src="<?php echo PFL_PLUGIN_URL . '/assets/img/plane.png' ?>" alt="Flight Loads" class="h-8 w-auto" />
        </div>
        <div class="sm:ml-6 sm:block hidden">
          <div class="flex space-x-4">
            <!-- Current: "bg-gray-900 text-white", Default: "text-gray-300 hover:bg-white/5 hover:text-white" -->
            <?php echo $this->nav_link(site_url('/flightloads'), 'Dashboard'); ?>
            <?php echo $this->nav_link(site_url('/flightloads/my-requests'), 'My Requests'); ?>
            <button onclick="window.location.href='<?php echo site_url('/flightloads/request'); ?>'"
      			  type="button"
      			  class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
      			  <!-- Heroicon: Plus -->
      			  <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      			    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      			  </svg>
      			  Request
      			</button>

            <?php echo $this->nav_link(site_url('/flightloads/about'), 'About'); ?>
          </div>
        </div>
      </div>
      <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
        <div class="relative">
          <!-- Notification button -->
          <button id="notification-btn" type="button"
            class="relative rounded-full p-1 text-gray-400 focus:outline-none">
            <span class="sr-only">View notifications</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="1.5" class="size-6">
              <path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967
                       8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967
                       8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085
                       5.455 1.31m5.714 0a24.255 24.255 0 0
                       1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <!-- Counter bubble -->
            <span id="notification-count"
              class="absolute -top-1 -right-1 inline-flex items-center justify-center
                     px-1.5 py-0.5 text-xs font-bold leading-none text-white
                     bg-red-600 rounded-full">0</span>
          </button>

          <!-- Dropdown -->
          <div id="notification-dropdown"
            class="hidden absolute right-0 mt-2 w-64 bg-white shadow-lg rounded-lg overflow-hidden z-10">
            <ul id="notifications-list"></ul>
            <button id="clear-all" class="text-sm w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg">Clear All</button>
          </div>
        </div>



        <!-- Profile dropdown -->
        <div class="relative ml-3">
        <!-- Button -->
        <button 
          id="userMenuButton"
          class="relative flex rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <span class="sr-only">Open user menu</span>
          <img 
            src="https://www.staging17.passrider.com/wp-content/uploads/2025/08/profile-pic-dummy.webp" 
            alt="User avatar" 
            class="size-8 rounded-full bg-gray-800 outline -outline-offset-1 outline-white/10"
          />
        </button>

        <!-- Dropdown menu -->
        <div 
          id="userMenu"
          class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 transition transform scale-95 opacity-0 !no-underline"
        >
          <a href="<?php echo site_url('/flightloads/account/', 'https');?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 !no-underline">Your profile</a>
          <a href="<?php echo esc_url( wp_logout_url()); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 !no-underline">Sign out</a>
        </div>
      </div>

      <script>
        document.addEventListener("DOMContentLoaded", function () {
          const button = document.getElementById("userMenuButton");
          const menu = document.getElementById("userMenu");

          button.addEventListener("click", function (e) {
            e.stopPropagation();
            menu.classList.toggle("hidden");

            // Toggle animation
            if (!menu.classList.contains("hidden")) {
              menu.classList.remove("scale-95", "opacity-0");
              menu.classList.add("scale-100", "opacity-100");
            } else {
              menu.classList.remove("scale-100", "opacity-100");
              menu.classList.add("scale-95", "opacity-0");
            }
          });

          // Close when clicking outside
          document.addEventListener("click", function (e) {
            if (!menu.contains(e.target) && !button.contains(e.target)) {
              menu.classList.add("hidden", "scale-95", "opacity-0");
              menu.classList.remove("scale-100", "opacity-100");
            }
          });
        });
      </script>

      </div>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="mobile-menu" class="hidden sm:hidden">
    <div class="space-y-1 px-2 pt-2 pb-3">
      <?php echo $this->nav_link(site_url('/flightloads'), 'Dashboard', true); ?>
      <?php echo $this->nav_link(site_url('/flightloads/my-requests'), 'My Requests', true); ?>
      <button onclick="window.location.href='<?php echo site_url('/flightloads/request', true); ?>'"
        type="button"
        class="inline-flex items-center rounded-lg bg-blue-600 mt-2 mb-2 px-4 py-2 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <!-- Heroicon: Plus -->
        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
          Request
      </button>

      <?php echo $this->nav_link(site_url('/flightloads/about'), 'About', true); ?>
    </div>
  </div>

  <script>
    const btn = document.getElementById("menu-btn");
    const menu = document.getElementById("mobile-menu");
    const iconOpen = document.getElementById("icon-open");
    const iconClose = document.getElementById("icon-close");

    btn.addEventListener("click", () => {
      const expanded = btn.getAttribute("aria-expanded") === "true";
      btn.setAttribute("aria-expanded", !expanded);

      menu.classList.toggle("hidden");
      iconOpen.classList.toggle("hidden");
      iconClose.classList.toggle("hidden");
    });
  </script>
</nav>