<div id="mediavine-settings" data-blocklist-leaderboard="1" data-blocklist-sidebar-atf="1" data-blocklist-sidebar-btf="1" data-blocklist-content-desktop="1" data-blocklist-content-mobile="1" data-blocklist-adhesion-mobile="1" data-blocklist-adhesion-tablet="1" data-blocklist-adhesion-desktop="1" data-blocklist-recipe="1" data-blocklist-auto-insert-sticky="1" data-blocklist-in-image="1" data-blocklist-chicory="1" data-blocklist-zergnet="1" data-blocklist-interstitial-mobile="1" data-blocklist-interstitial-desktop="1" data-blocklist-universal-player-desktop="1" data-blocklist-universal-player-mobile="1" ></div>
<div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-lg mt-10 mb-8">
    <div class="block">
        <div class="flex items-center justify-center"><h2 class="text-3xl font-bold text-gray-900 mb-8">Flight Loads Setup</h2></div>
    </div>
    <!-- Updated progress bar to use Tailwind classes -->
    <div id="pfl-progress" class="flex items-center justify-center mb-8">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2 bg-blue-500 text-white px-3 py-2 rounded-full text-sm font-medium" data-step="1">
                <span class="w-6 h-6 bg-white text-blue-500 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                <span>Basic Info</span>
            </div>
            <div class="w-8 h-0.5 bg-gray-300"></div>
            <div class="flex items-center space-x-2 bg-gray-200 text-gray-600 px-3 py-2 rounded-full text-sm font-medium" data-step="2">
                <span class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                <span>Verification</span>
            </div>
            <div class="w-8 h-0.5 bg-gray-300"></div>
            <div class="flex items-center space-x-2 bg-gray-200 text-gray-600 px-3 py-2 rounded-full text-sm font-medium" data-step="3">
                <span class="w-6 h-6 bg-gray-400 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                <span>Account</span>
            </div>
        </div>
    </div>

    <form id="pfl-application-form" method="post" class="space-y-6">
        <?php wp_nonce_field('pfl_application_nonce', 'pfl_nonce'); ?>
        
        <!-- Step 1: Basic Information -->
        <div class="pfl-form-step block" data-step="1">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Basic Information</h2>
            
            <div class="space-y-4">
                <div class="pfl-form-group">
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red">*</span></label>
                    <input type="text" id="full_name" name="full_name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Airline Employment Status <span class="text-red">*</span></label>
                    <div class="flex space-x-6 pfl-form-group">
                        <label class="flex items-center mb-0 mt-1">
                            <input type="radio" name="status" value="active" class="h-4 !w-4 text-blue-600 focus:ring-blue-500 border-gray-300 w-full">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center mb-0 mt-1">
                            <input type="radio" name="status" value="retired" class="h-4 !w-4 text-blue-600 focus:ring-blue-500 border-gray-300 w-full">
                            <span class="ml-2 text-sm text-gray-700">Retired</span>
                        </label>
                    </div>
                </div>
                
                <!-- Updated retired fields styling -->
                <div id="additional_fields" class="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200 hidden">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="pfl-form-group">
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red">*</span></label>
                            <input type="tel" id="phone_number" name="phone_number"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="pfl-form-group">
                            <label id="date_label" for="employment_retirement_date" class="block text-sm font-medium text-gray-700 mb-1">Employment Start Date *</label>
                            <input type="date" id="employment_retirement_date" name="employment_retirement_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="pfl-form-group">
                            <label id="job_label" for="airline_job" class="block text-sm font-medium text-gray-700 mb-1">Airline Job *</label>
                            <input type="text" id="airline_job" name="airline_job"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div id="years_worked_toggle" class="pfl-form-group">
                            <label for="years_worked" class="block text-sm font-medium text-gray-700 mb-1">Years Worked *</label>
                            <input type="number" id="years_worked" name="years_worked" min="1" readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="pfl-form-group">
                        <label id="upload_label" for="upload_id" class="block text-sm font-medium text-gray-700 mb-1">Airline ID *</label>
                        <input type="file" id="upload_id" name="upload_id" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">Only accepts .jpg, .jpeg, .png and max file size is 5mb</p>
                    </div>
                </div>

                <div class="relative pfl-form-group">
                    <label for="airline_search" class="block text-sm font-medium text-gray-700 mb-1">Your Airline Name <span class="text-red">*</span></label>
                    <input type="text" id="airline_search" name="airline_search" placeholder="Start typing airline name..." required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="hidden" id="airline_id" name="airline_id">
                    <input type="hidden" id="airline_code" name="airline_code">
                    <div id="airline_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto hidden"></div>
                </div>

            </div>
            
            <button type="button" class="mt-6 w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium" id="step1_continue">
                Continue
            </button>
        </div>
        
        <!-- Step 2: Email Verification -->
        <div class="pfl-form-step hidden" data-step="2">
            <div class="pfl-form-container hidden"></div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Email Verification</h2>
            
            <div class="space-y-4">
                <div class="pfl-form-group">
                    <label for="airline_email" class="block text-sm font-medium text-gray-700 mb-1">Airline Affiliated Email *</label>
                    <input type="email" id="airline_email" name="airline_email"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Must match your selected airline's domain</p>
                </div>
                
                <button type="button" class="bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 font-medium hidden" id="send_verification">
                    Send Verification Code
                </button>
                
                <div id="verification_section" class="space-y-4 hidden">
                    <div class="pfl-form-group">
                        <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-1">Verification Code *</label>
                        <input type="text" id="verification_code" name="verification_code" maxlength="6"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <button type="button" class="bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 font-medium" id="verify_code">
                        Verify Code
                    </button>
                </div>
            </div>
            
            <button type="button" class="mt-6 w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium hidden" id="step2_continue">
                Continue
            </button>
        </div>
        
        <!-- Step 3: Create Account -->
        <div class="pfl-form-step hidden" data-step="3">
            <div class="pfl-form-container hidden"></div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Create Account</h2>
            
            <div class="space-y-4">
                <div class="pfl-form-group">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                    <input type="text" id="username" name="username"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="pfl-form-group">
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                  
                  <div class="flex">
                    <input type="email" id="email" name="email"
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="email@gmail.com">
                    
                    <button type="button" id="verifyEmail"
                      class="px-4 bg-blue-600 text-white rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 h-[34px]">
                      Send Code
                    </button>
                  </div>
                  
                  <p class="mt-1 text-sm text-gray-500">Email to use as your flight loads login</p>
                </div>

                <div id="verification_area"></div>
                
                <div class="security-area hidden">
                    <div class="pfl-form-group relative">
                      <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                      <input type="password" id="password" name="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10">
                      
                      <!-- Eye Icon -->
                      <button type="button" class="absolute right-3 top-9 transform text-gray-500 focus:outline-none" onclick="togglePassword('password', this)">
                        <!-- Eye Open -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Eye Closed -->
                        <svg class="h-5 w-5 eye-closed hidden" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M20.5303 4.53033C20.8232 4.23744 20.8232 3.76256 20.5303 3.46967C20.2374 3.17678 19.7626 3.17678 19.4697 3.46967L3.46967 19.4697C3.17678 19.7626 3.17678 20.2374 3.46967 20.5303C3.76256 20.8232 4.23744 20.8232 4.53033 20.5303L7.37723 17.6834C8.74353 18.3266 10.3172 18.75 12 18.75C14.684 18.75 17.0903 17.6729 18.8206 16.345C19.6874 15.6797 20.4032 14.9376 20.9089 14.2089C21.4006 13.5003 21.75 12.7227 21.75 12C21.75 11.2773 21.4006 10.4997 20.9089 9.79115C20.4032 9.06244 19.6874 8.32028 18.8206 7.65503C18.5585 7.45385 18.2808 7.25842 17.989 7.07163L20.5303 4.53033ZM16.8995 8.16113L15.1287 9.93196C15.5213 10.5248 15.75 11.2357 15.75 12C15.75 14.0711 14.0711 15.75 12 15.75C11.2357 15.75 10.5248 15.5213 9.93196 15.1287L8.51524 16.5454C9.58077 16.9795 10.7621 17.25 12 17.25C14.2865 17.25 16.3802 16.3271 17.9073 15.155C18.6692 14.5703 19.2714 13.9374 19.6766 13.3536C20.0957 12.7497 20.25 12.2773 20.25 12C20.25 11.7227 20.0957 11.2503 19.6766 10.6464C19.2714 10.0626 18.6692 9.42972 17.9073 8.84497C17.5941 8.60461 17.2571 8.37472 16.8995 8.16113ZM11.0299 14.0307C11.3237 14.1713 11.6526 14.25 12 14.25C13.2426 14.25 14.25 13.2426 14.25 12C14.25 11.6526 14.1713 11.3237 14.0307 11.0299L11.0299 14.0307Z" fill="black"/>
                            <path d="M12 5.25C13.0323 5.25 14.0236 5.40934 14.9511 5.68101C15.1296 5.73328 15.1827 5.95662 15.0513 6.0881L14.2267 6.91265C14.1648 6.97451 14.0752 6.99928 13.99 6.97967C13.3506 6.83257 12.6839 6.75 12 6.75C9.71345 6.75 7.61978 7.67292 6.09267 8.84497C5.33078 9.42972 4.72857 10.0626 4.32343 10.6464C3.90431 11.2503 3.75 11.7227 3.75 12C3.75 12.2773 3.90431 12.7497 4.32343 13.3536C4.67725 13.8635 5.18138 14.4107 5.81091 14.9307C5.92677 15.0264 5.93781 15.2015 5.83156 15.3078L5.12265 16.0167C5.03234 16.107 4.88823 16.1149 4.79037 16.0329C4.09739 15.4517 3.51902 14.8255 3.0911 14.2089C2.59937 13.5003 2.25 12.7227 2.25 12C2.25 11.2773 2.59937 10.4997 3.0911 9.79115C3.59681 9.06244 4.31262 8.32028 5.17941 7.65503C6.90965 6.32708 9.31598 5.25 12 5.25Z" fill="black"/>
                            <path d="M12 8.25C12.1185 8.25 12.2357 8.25549 12.3513 8.26624C12.5482 8.28453 12.6194 8.51991 12.4796 8.6597L11.2674 9.87196C10.6141 10.0968 10.0968 10.6141 9.87196 11.2674L8.6597 12.4796C8.51991 12.6194 8.28453 12.5482 8.26624 12.3513C8.25549 12.2357 8.25 12.1185 8.25 12C8.25 9.92893 9.92893 8.25 12 8.25Z" fill="black"/>
                        </svg>
                      </button>
                    </div>

                    <div class="pfl-form-group relative">
                      <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                      <input type="password" id="confirm_password" name="confirm_password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10">
                      
                      <!-- Eye Icon -->
                      <button type="button" class="absolute right-3 top-9 transform text-gray-500" onclick="togglePassword('confirm_password', this)">
                        <!-- Eye Open -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Eye Closed -->
                        <svg class="h-5 w-5 eye-closed hidden" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M20.5303 4.53033C20.8232 4.23744 20.8232 3.76256 20.5303 3.46967C20.2374 3.17678 19.7626 3.17678 19.4697 3.46967L3.46967 19.4697C3.17678 19.7626 3.17678 20.2374 3.46967 20.5303C3.76256 20.8232 4.23744 20.8232 4.53033 20.5303L7.37723 17.6834C8.74353 18.3266 10.3172 18.75 12 18.75C14.684 18.75 17.0903 17.6729 18.8206 16.345C19.6874 15.6797 20.4032 14.9376 20.9089 14.2089C21.4006 13.5003 21.75 12.7227 21.75 12C21.75 11.2773 21.4006 10.4997 20.9089 9.79115C20.4032 9.06244 19.6874 8.32028 18.8206 7.65503C18.5585 7.45385 18.2808 7.25842 17.989 7.07163L20.5303 4.53033ZM16.8995 8.16113L15.1287 9.93196C15.5213 10.5248 15.75 11.2357 15.75 12C15.75 14.0711 14.0711 15.75 12 15.75C11.2357 15.75 10.5248 15.5213 9.93196 15.1287L8.51524 16.5454C9.58077 16.9795 10.7621 17.25 12 17.25C14.2865 17.25 16.3802 16.3271 17.9073 15.155C18.6692 14.5703 19.2714 13.9374 19.6766 13.3536C20.0957 12.7497 20.25 12.2773 20.25 12C20.25 11.7227 20.0957 11.2503 19.6766 10.6464C19.2714 10.0626 18.6692 9.42972 17.9073 8.84497C17.5941 8.60461 17.2571 8.37472 16.8995 8.16113ZM11.0299 14.0307C11.3237 14.1713 11.6526 14.25 12 14.25C13.2426 14.25 14.25 13.2426 14.25 12C14.25 11.6526 14.1713 11.3237 14.0307 11.0299L11.0299 14.0307Z" fill="black"/>
                            <path d="M12 5.25C13.0323 5.25 14.0236 5.40934 14.9511 5.68101C15.1296 5.73328 15.1827 5.95662 15.0513 6.0881L14.2267 6.91265C14.1648 6.97451 14.0752 6.99928 13.99 6.97967C13.3506 6.83257 12.6839 6.75 12 6.75C9.71345 6.75 7.61978 7.67292 6.09267 8.84497C5.33078 9.42972 4.72857 10.0626 4.32343 10.6464C3.90431 11.2503 3.75 11.7227 3.75 12C3.75 12.2773 3.90431 12.7497 4.32343 13.3536C4.67725 13.8635 5.18138 14.4107 5.81091 14.9307C5.92677 15.0264 5.93781 15.2015 5.83156 15.3078L5.12265 16.0167C5.03234 16.107 4.88823 16.1149 4.79037 16.0329C4.09739 15.4517 3.51902 14.8255 3.0911 14.2089C2.59937 13.5003 2.25 12.7227 2.25 12C2.25 11.2773 2.59937 10.4997 3.0911 9.79115C3.59681 9.06244 4.31262 8.32028 5.17941 7.65503C6.90965 6.32708 9.31598 5.25 12 5.25Z" fill="black"/>
                            <path d="M12 8.25C12.1185 8.25 12.2357 8.25549 12.3513 8.26624C12.5482 8.28453 12.6194 8.51991 12.4796 8.6597L11.2674 9.87196C10.6141 10.0968 10.0968 10.6141 9.87196 11.2674L8.6597 12.4796C8.51991 12.6194 8.28453 12.5482 8.26624 12.3513C8.25549 12.2357 8.25 12.1185 8.25 12C8.25 9.92893 9.92893 8.25 12 8.25Z" fill="black"/>
                        </svg>
                      </button>
                    </div>

                    <script>
                    function togglePassword(inputId, btn) {
                      const input = document.getElementById(inputId);
                      const eyeOpen = btn.querySelector(".eye-open");
                      const eyeClosed = btn.querySelector(".eye-closed");

                      if (input.type === "password") {
                        input.type = "text";
                        eyeOpen.classList.add("hidden");
                        eyeClosed.classList.remove("hidden");
                      } else {
                        input.type = "password";
                        eyeOpen.classList.remove("hidden");
                        eyeClosed.classList.add("hidden");
                      }
                    }
                    </script>

                </div>
            </div>
            
            <button type="submit" class="mt-6 w-full !bg-green-600 text-white py-2 px-4 !rounded-md !hover:bg-green-700 !focus:outline-none !focus:ring-2 !focus:ring-green-500 !focus:ring-offset-2 font-medium">
                Submit Application
            </button>
        </div>
    </form>
    <p class="text-center text-sm">Already have an account? <a href="<?php echo site_url('/flight-loads-login/', 'https'); ?>">Login here.</a></p>
    <span class="text-sm">* Required Fields</span>
</div>
