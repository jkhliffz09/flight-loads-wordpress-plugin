import { createClient } from "@/lib/supabase/server"
import { AirlinesManagement } from "@/components/admin/airlines-management"

export default async function AdminSettingsPage() {
  const supabase = createClient()

  const { data: airlines, error } = await supabase.from("airlines").select("*").order("name")

  if (error) {
    console.error("Error fetching airlines:", error)
    return <div>Error loading settings</div>
  }

  // Get user counts per airline domain
  const { data: userCounts } = await supabase.from("user_profiles").select("airline_id").not("airline_id", "is", null)

  const airlineUserCounts =
    userCounts?.reduce((acc: Record<number, number>, user) => {
      acc[user.airline_id] = (acc[user.airline_id] || 0) + 1
      return acc
    }, {}) || {}

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">General Settings</h1>
        <p className="text-gray-600 mt-2">Manage airline domains and system settings</p>
      </div>

      <AirlinesManagement airlines={airlines || []} userCounts={airlineUserCounts} />
    </div>
  )
}
