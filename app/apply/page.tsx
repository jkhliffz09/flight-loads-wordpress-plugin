import { MultiStepApplicationForm } from "@/components/application/multi-step-form"

export default function ApplicationPage() {
  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-2xl mx-auto">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Join Passrider</h1>
          <p className="text-gray-600 mt-2">Apply for access to flight load information</p>
        </div>

        <MultiStepApplicationForm />
      </div>
    </div>
  )
}
