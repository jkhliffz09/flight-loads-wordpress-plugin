import { getCurrentUser } from "@/lib/auth/session"
import { getUserProfileById, getFlightRequestsByAirline } from "@/lib/mysql/database"
import { redirect } from "next/navigation"
import { Header } from "@/components/layout/header"
import { FlightRequestForm } from "@/components/requests/flight-request-form"
import { FlightRequestsList } from "@/components/requests/flight-requests-list"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"

export default async function RequestsPage() {
  const { user, profile } = await getCurrentUser()

  if (!user || !profile) {
    redirect("/auth/login")
  }

  if (!profile.is_approved) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Header />
        <div className="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h1 className="text-2xl font-bold text-gray-900 mb-4">Account Pending Approval</h1>
            <p className="text-gray-600">
              Your account is pending approval. Please wait for admin approval to access flight requests.
            </p>
          </div>
        </div>
      </div>
    )
  }

  try {
    // Get detailed profile with airline information
    const detailedProfile = await getUserProfileById(user.id)

    if (!detailedProfile) {
      redirect("/apply")
    }

    // Get flight requests visible to this user (their airline's requests)
    const requests = detailedProfile.airline_id ? await getFlightRequestsByAirline(detailedProfile.airline_id) : []

    return (
      <div className="min-h-screen bg-gray-50">
        <Header />
        <div className="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-gray-900">Flight Requests</h1>
            <p className="text-gray-600 mt-2">Request and provide flight load information</p>
          </div>

          <Tabs defaultValue="request" className="space-y-6">
            <TabsList className="grid w-full grid-cols-2">
              <TabsTrigger value="request">Request Flight Loads</TabsTrigger>
              <TabsTrigger value="browse">Browse Requests</TabsTrigger>
            </TabsList>

            <TabsContent value="request">
              <FlightRequestForm userProfile={detailedProfile} />
            </TabsContent>

            <TabsContent value="browse">
              <FlightRequestsList requests={requests || []} currentUser={detailedProfile} />
            </TabsContent>
          </Tabs>
        </div>
      </div>
    )
  } catch (error) {
    console.error("Error loading requests page:", error)
    return <div>Error loading page</div>
  }
}
