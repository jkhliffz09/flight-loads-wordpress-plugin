import { createClient } from "@/lib/supabase/server"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Users, FileText, Plane, Building } from "lucide-react"

export default async function AdminDashboard() {
  const supabase = createClient()

  // Get dashboard statistics
  const [{ count: usersCount }, { count: requestsCount }, { count: airlinesCount }, { count: pendingUsersCount }] =
    await Promise.all([
      supabase.from("user_profiles").select("*", { count: "exact", head: true }),
      supabase.from("flight_requests").select("*", { count: "exact", head: true }),
      supabase.from("airlines").select("*", { count: "exact", head: true }),
      supabase.from("user_profiles").select("*", { count: "exact", head: true }).eq("is_approved", false),
    ])

  const stats = [
    {
      title: "Total Users",
      value: usersCount || 0,
      icon: Users,
      color: "text-blue-600",
    },
    {
      title: "Flight Requests",
      value: requestsCount || 0,
      icon: FileText,
      color: "text-green-600",
    },
    {
      title: "Airlines",
      value: airlinesCount || 0,
      icon: Building,
      color: "text-purple-600",
    },
    {
      title: "Pending Approvals",
      value: pendingUsersCount || 0,
      icon: Plane,
      color: "text-orange-600",
    },
  ]

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p className="text-gray-600 mt-2">Manage your Passrider Flight Loads system</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {stats.map((stat) => (
          <Card key={stat.title}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium text-gray-600">{stat.title}</CardTitle>
              <stat.icon className={`h-5 w-5 ${stat.color}`} />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stat.value}</div>
            </CardContent>
          </Card>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600">Recent user registrations and flight requests will appear here.</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>System Status</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              <div className="flex justify-between">
                <span className="text-sm text-gray-600">Database</span>
                <span className="text-sm text-green-600 font-medium">Connected</span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-gray-600">Authentication</span>
                <span className="text-sm text-green-600 font-medium">Active</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
