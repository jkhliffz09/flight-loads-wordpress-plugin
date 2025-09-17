<div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-lg mt-10 mb-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Flight Loads Login</h2>
    <form action="<?php echo esc_url( wp_login_url() ); ?>" method="post" class="space-y-4">
        <label class="block">
            <span class="text-sm text-gray-700">Username or Email</span>
            <input type="text" name="log" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        </label>

        <label class="block">
            <span class="text-sm text-gray-700">Password</span>
            <input type="password" name="pwd" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        </label>

        <div class="flex items-center justify-between">
            <label class="flex items-center space-x-2 text-sm text-gray-600">
                <input type="checkbox" name="rememberme" class="h-4 !w-4 text-blue-600 border-gray-300 rounded">
                <span>Remember me</span>
            </label>
            <a href="<?php echo wp_lostpassword_url(); ?>" class="text-blue-600 hover:underline text-sm">Forgot password?</a>
        </div>

        <input type="hidden" name="redirect_to" value="<?php echo esc_url($atts['redirect_url']); ?>" />

        <button type="submit" name="wp-submit"
            class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 text-sm font-medium">
            Login
        </button>
    </form>
    <p class="text-center text-sm">No account yet? <a href="<?php echo site_url('/flight-loads-application/', 'https'); ?>">Signup here.</a></p>
</div>