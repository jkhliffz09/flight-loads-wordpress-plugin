import { getFlightRequests } from "@/lib/mysql/database"
import { getCurrentUser } from "@/lib/auth/session"
import { redirect } from "next/navigation"
import { FlightRequestsTable } from "@/components/admin/flight-requests-table"

export default async function AdminRequestsPage() {
  const { user, profile } = await getCurrentUser()

  if (!user || !profile || !profile.is_admin) {
    redirect("/auth/login")
  }

  try {
    const requests = await getFlightRequests()

    return (
      <div>
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Flight Requests</h1>
          <p className="text-gray-600 mt-2">Manage all flight load requests</p>
        </div>

        <FlightRequestsTable requests={requests || []} />
      </div>
    )
  } catch (error) {
    console.error("Error fetching flight requests:", error)
    return <div>Error loading flight requests</div>
  }
}
