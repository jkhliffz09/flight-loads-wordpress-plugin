export default function ApplicationSubmittedPage() {
  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center px-4">
      <div className="max-w-md w-full text-center space-y-6">
        <div className="bg-white rounded-lg shadow-lg p-8">
          <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <h1 className="text-2xl font-bold text-gray-900 mb-4">Application Submitted</h1>

          <div className="space-y-3 text-gray-600">
            <p>Thank you for applying to join Passrider!</p>
            <p>Your application is now under review by our admin team.</p>
            <p>You will receive an email notification once your account is approved and ready to use.</p>
          </div>

          <div className="mt-6 pt-6 border-t border-gray-200">
            <p className="text-sm text-gray-500">Questions? Contact our support team for assistance.</p>
          </div>
        </div>
      </div>
    </div>
  )
}
